<?php

namespace SoureCode\Bundle\DoctrineExtension\Doctrine\DBAL\Types;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use SoureCode\Bundle\Timezone\Manager\TimezoneManager;

final class DateTimeHelper
{
    private static ?\DateTimeZone $utc = null;

    public static function convertToDatabaseValue(\DateTime|\DateTimeImmutable|null $value): \DateTime|\DateTimeImmutable|null
    {
        if ($value instanceof \DateTimeImmutable) {
            $value = \DateTimeImmutable::createFromInterface($value)
                ->setTimezone(self::getUtc());
        } elseif ($value instanceof \DateTime) {
            $value = \DateTime::createFromInterface($value)
                ->setTimezone(self::getUtc());
        }

        return $value;
    }

    private static function getUtc(): \DateTimeZone
    {
        return self::$utc ??= new \DateTimeZone('Etc/UTC');
    }

    /**
     * @param class-string $type
     *
     * @psalm-param T $value
     *
     * @template T
     *
     * @throws ConversionException
     */
    public static function convertToPHPValue($value, string $type, string $format): ?\DateTime
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $converted = \DateTime::createFromFormat($format, $value, self::getUtc());

        if (!$converted) {
            throw InvalidFormat::new($value, $type, $format);
        }

        return $converted->setTimezone(self::getTimeZone());
    }

    public static function getTimeZone(): \DateTimeZone
    {
        return TimezoneManager::getInstance()->getTimezone();
    }

    /**
     * @param class-string $type
     *
     * @psalm-param T $value
     *
     * @template T
     *
     * @throws ConversionException
     */
    public static function convertToImmutablePHPValue($value, string $type, string $format): ?\DateTimeImmutable
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $converted = \DateTimeImmutable::createFromFormat($format, $value, self::getUtc());

        if (!$converted) {
            throw InvalidFormat::new($value, $type, $format);
        }

        return $converted->setTimezone(self::getTimeZone());
    }
}
