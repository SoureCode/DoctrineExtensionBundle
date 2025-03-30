<?php

namespace SoureCode\Bundle\DoctrineExtension;

use SoureCode\Bundle\DoctrineExtension\EventListener\BlameableListener;
use SoureCode\Bundle\DoctrineExtension\EventListener\TimestampableListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class SoureCodeDoctrineExtensionBundle extends AbstractBundle
{
    private static string $PREFIX = 'soure_code.doctrine_extension.';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services
            ->set(self::$PREFIX.'listener.timestampable', TimestampableListener::class)
            ->args([
                service('clock'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'prePersist',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'preUpdate',
            ]);

        $services
            ->set(self::$PREFIX.'listener.blameable', BlameableListener::class)
            ->args([
                service('security.helper'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'prePersist',
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'preUpdate',
            ]);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/packages/doctrine.php');
    }
}
