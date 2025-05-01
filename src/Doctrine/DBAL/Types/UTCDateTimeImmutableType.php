<?php

namespace SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;

final class UTCDateTimeImmutableType extends DateTimeImmutableType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return parent::convertToDatabaseValue(DateTimeHelper::convertToDatabaseValue($value), $platform);
    }

    /**
     * @param T $value
     *
     * @return (T is null ? null : \DateTimeImmutable)
     *
     * @template T
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        return DateTimeHelper::convertToImmutablePHPValue($value, self::class, $platform->getDateTimeFormatString());
    }
}
