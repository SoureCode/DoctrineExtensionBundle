<?php

namespace SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateType;

final class UTCDateType extends DateType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return parent::convertToDatabaseValue(DateTimeHelper::convertToDatabaseValue($value), $platform);
    }

    /**
     * @psalm-param T $value
     *
     * @template T
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTime
    {
        return DateTimeHelper::convertToPHPValue($value, self::class, $platform->getDateFormatString());
    }
}
