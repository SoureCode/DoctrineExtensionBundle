<?php

namespace SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

final class UTCDateTimeType extends DateTimeType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return parent::convertToDatabaseValue(DateTimeHelper::convertToDatabaseValue($value), $platform);
    }

    /**
     * @param T $value
     *
     * @template T
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTime
    {
        return DateTimeHelper::convertToPHPValue($value, self::class, $platform->getDateTimeFormatString());
    }
}
