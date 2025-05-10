<?php

namespace SoureCode\Bundle\DoctrineExtension;

use SoureCode\Bundle\DoctrineExtension\Cache\ClassMetadataCacheWarmer;
use SoureCode\Bundle\DoctrineExtension\EventListener\PropertyListener;
use SoureCode\Bundle\DoctrineExtension\EventListener\TranslatableListener;
use SoureCode\Bundle\DoctrineExtension\EventListener\TranslationListener;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataFactory;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataGenerator;
use SoureCode\Bundle\DoctrineExtension\Provider\DateTimeValueProvider;
use SoureCode\Bundle\DoctrineExtension\Provider\UserValueProvider;
use SoureCode\Bundle\DoctrineExtension\Translation\EntityTranslator;
use SoureCode\Bundle\DoctrineExtension\Translation\EntityTranslatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Security\Core\User\UserInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

/**
 * @phpstan-type ConfigType array{
 *     user_class: class-string<UserInterface>,
 *     persist_date_time_class: string,
 *     update_date_time_class: string,
 * }
 */
final class SoureCodeDoctrineExtensionBundle extends AbstractBundle
{
    private static string $PREFIX = 'soure_code.doctrine_extension.';

    public function configure(DefinitionConfigurator $definition): void
    {
        /**
         * @var ArrayNodeDefinition $rootNode
         */
        $rootNode = $definition->rootNode();

        // @formatter:off
        $rootNode
            ->fixXmlConfig('doctrine_extension')
            ->addDefaultsIfNotSet()
        ;

        $children = $rootNode->children();
        $children
            ->scalarNode('user_class')
                ->defaultValue('App\Entity\User')
                ->validate()
                    ->ifTrue(fn (string $v) => !class_exists($v))
                    ->thenInvalid('The class "%s" does not exist.')
                    ->ifTrue(fn (string $v) => !is_subclass_of($v, UserInterface::class))
                    ->thenInvalid('The class "%s" must implement the "'.UserInterface::class.'" interface.');

        $children
            ->scalarNode('persist_date_time_class')
                ->defaultValue(\DateTimeImmutable::class)
                ->validate()
                    ->ifTrue(fn (string $v) => !class_exists($v) || !is_subclass_of($v, \DateTimeInterface::class))
                    ->thenInvalid('The class "%s" must be a subclass of "'.\DateTimeInterface::class.'".');

        $children
            ->scalarNode('update_date_time_class')
            ->defaultValue(\DateTimeImmutable::class)
                ->validate()
                    ->ifTrue(fn (string $v) => !class_exists($v) || !is_subclass_of($v, \DateTimeInterface::class))
                    ->thenInvalid('The class "%s" must be a subclass of "'.\DateTimeInterface::class.'".');
        // @formatter:on
    }

    /**
     * @param ConfigType $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $parameters = $container->parameters();

        $parameters->set(self::$PREFIX.'user_class', $config['user_class']);
        $parameters->set(self::$PREFIX.'persist_date_time_class', $config['persist_date_time_class']);
        $parameters->set(self::$PREFIX.'update_date_time_class', $config['update_date_time_class']);

        $services = $container->services();

        $services
            ->set(self::$PREFIX.'cache')
            ->parent('cache.system')
            ->tag('cache.pool');

        $services
            ->set(self::$PREFIX.'class_metadata.generator', ClassMetadataGenerator::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service(self::$PREFIX.'cache'),
                param(self::$PREFIX.'persist_date_time_class'),
                param(self::$PREFIX.'update_date_time_class'),
                param(self::$PREFIX.'user_class'),
            ]);

        $services
            ->set(self::$PREFIX.'class_metadata.cache_warmer', ClassMetadataCacheWarmer::class)
            ->args([
                service(self::$PREFIX.'class_metadata.generator'),
            ])
            ->tag('kernel.cache_warmer')
        ;

        $services
            ->set(self::$PREFIX.'class_metadata.factory', ClassMetadataFactory::class)
            ->args([
                service(self::$PREFIX.'class_metadata.generator'),
            ])
            ->tag('kernel.reset', [
                'method' => 'reset',
            ])
        ;

        $services
            ->set(self::$PREFIX.'value_provider.datetime', DateTimeValueProvider::class)
            ->args([
                service('clock'),
            ])
            ->tag(self::$PREFIX.'value_provider', [
                'priority' => 100,
            ])
            ->tag('kernel.reset', [
                'method' => 'reset',
            ])
        ;

        $services
            ->set(self::$PREFIX.'value_provider.user', UserValueProvider::class)
            ->args([
                service('security.token_storage'),
            ])
            ->tag(self::$PREFIX.'value_provider', [
                'priority' => 100,
            ])
            ->tag('kernel.reset', [
                'method' => 'reset',
            ])
        ;

        $services
            ->set(self::$PREFIX.'listener.property', PropertyListener::class)
            ->args([
                tagged_iterator(self::$PREFIX.'value_provider'),
                service(self::$PREFIX.'class_metadata.factory'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'prePersist',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'preUpdate',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ])
            ->tag('kernel.reset', [
                'method' => 'reset',
            ])
        ;

        $services
            ->set(self::$PREFIX.'listener.translatable', TranslatableListener::class)
            ->args([
                service(self::$PREFIX.'class_metadata.factory'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ]);

        $services
            ->set(self::$PREFIX.'listener.translation', TranslationListener::class)
            ->args([
                service(self::$PREFIX.'class_metadata.factory'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ]);

        $services
            ->set(self::$PREFIX.'translator.entity', EntityTranslator::class)
            ->args([
                service('request_stack'),
                service('doctrine.orm.default_entity_manager'),
                service(self::$PREFIX.'class_metadata.factory'),
                param('kernel.default_locale'),
            ]);

        $services
            ->alias(EntityTranslatorInterface::class, self::$PREFIX.'translator.entity')
            ->public();
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/packages/doctrine.php');
    }
}
