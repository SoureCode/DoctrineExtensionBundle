<?php

use Doctrine\DBAL\Types\Types;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCDateImmutableType;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCDateTimeImmutableType;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCDateTimeType;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCDateTimeTzImmutableType;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCDateTimeTzType;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCDateType;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCTimeImmutableType;
use SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types\UTCTimeType;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return static function (ContainerBuilder $containerBuilder) {
    $containerBuilder->prependExtensionConfig('doctrine', [
        'dbal' => [
            'types' => [
                Types::DATE_MUTABLE => UTCDateType::class,
                Types::DATE_IMMUTABLE => UTCDateImmutableType::class,
                Types::DATETIME_MUTABLE => UTCDateTimeType::class,
                Types::DATETIME_IMMUTABLE => UTCDateTimeImmutableType::class,
                Types::DATETIMETZ_MUTABLE => UTCDateTimeTzType::class,
                Types::DATETIMETZ_IMMUTABLE => UTCDateTimeTzImmutableType::class,
                Types::TIME_MUTABLE => UTCTimeType::class,
                Types::TIME_IMMUTABLE => UTCTimeImmutableType::class,
            ],
        ],
    ]);
};
