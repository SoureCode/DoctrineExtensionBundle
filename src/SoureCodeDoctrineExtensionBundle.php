<?php

namespace SoureCode\Bundle\DoctrineExtension;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\EventListener\BlameableListener;
use SoureCode\Bundle\DoctrineExtension\EventListener\TimestampableListener;
use SoureCode\Bundle\DoctrineExtension\EventListener\TranslatableListener;
use SoureCode\Bundle\DoctrineExtension\EventListener\TranslationListener;
use SoureCode\Bundle\DoctrineExtension\Translation\EntityTranslator;
use SoureCode\Bundle\DoctrineExtension\Translation\EntityTranslatorInterface;
use SoureCode\Bundle\DoctrineExtension\Translation\TranslationMapping;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
            ->scalarNode('created_at_type')
                ->defaultValue(Types::DATETIME_IMMUTABLE)
                ->validate()
                    ->ifNotInArray([
                        Types::DATETIMETZ_IMMUTABLE,
                        Types::DATETIME_IMMUTABLE,
                        Types::DATETIMETZ_MUTABLE,
                        Types::DATETIME_MUTABLE,
                    ])
                    ->thenInvalid('The type "%s" is not supported. Supported types are: "'.Types::DATETIMETZ_IMMUTABLE.'", "'.Types::DATETIME_IMMUTABLE.'", "'.Types::DATETIMETZ_MUTABLE.'", "'.Types::DATETIME_MUTABLE.'".');

        $children
            ->scalarNode('updated_at_type')
                ->defaultValue(Types::DATETIME_IMMUTABLE)
                ->validate()
                    ->ifNotInArray([
                        Types::DATETIMETZ_IMMUTABLE,
                        Types::DATETIME_IMMUTABLE,
                        Types::DATETIMETZ_MUTABLE,
                        Types::DATETIME_MUTABLE,
                    ])
                    ->thenInvalid('The type "%s" is not supported. Supported types are: "'.Types::DATETIMETZ_IMMUTABLE.'", "'.Types::DATETIME_IMMUTABLE.'", "'.Types::DATETIMETZ_MUTABLE.'", "'.Types::DATETIME_MUTABLE.'".')
        ;
        // @formatter:on
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $parameters = $container->parameters();

        $parameters->set(self::$PREFIX.'user_class', $config['user_class']);
        $parameters->set(self::$PREFIX.'created_at_type', $config['created_at_type']);
        $parameters->set(self::$PREFIX.'updated_at_type', $config['updated_at_type']);

        $services = $container->services();

        $services
            ->set(self::$PREFIX.'listener.timestampable', TimestampableListener::class)
            ->args([
                service('clock'),
                param(self::$PREFIX.'created_at_type'),
                param(self::$PREFIX.'updated_at_type'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'prePersist',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'preUpdate',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ]);

        $services
            ->set(self::$PREFIX.'listener.blameable', BlameableListener::class)
            ->args([
                service('security.helper'),
                param(self::$PREFIX.'user_class'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'prePersist',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'preUpdate',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ]);

        $services
            ->set(self::$PREFIX.'mapping.translation', TranslationMapping::class)
            ->args([
                service(EntityManagerInterface::class),
                service(CacheInterface::class),
            ]);

        $services
            ->set(self::$PREFIX.'listener.translatable', TranslatableListener::class)
            ->args([
                service(self::$PREFIX.'mapping.translation'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ]);

        $services
            ->set(self::$PREFIX.'listener.translation', TranslationListener::class)
            ->args([
                service(EntityManagerInterface::class),
                service(self::$PREFIX.'mapping.translation'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'loadClassMetadata',
            ]);

        $services
            ->set(self::$PREFIX.'translator.entity', EntityTranslator::class)
            ->args([
                service('request_stack'),
                service(EntityManagerInterface::class),
                service(self::$PREFIX.'mapping.translation'),
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
