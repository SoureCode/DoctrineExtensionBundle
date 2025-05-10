<?php

namespace SoureCode\Bundle\DoctrineExtension\Provider;

use DateTimeInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @implements ValueProviderInterface<DateTimeInterface>
 */
final class DateTimeValueProvider implements ValueProviderInterface, ResetInterface
{
    /**
     * @var array<string, \DateTimeImmutable|\DateTime|null>
     */
    private array $cache;

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function provide(string $type): ?\DateTimeInterface
    {
        return $this->cache[$type] ??= $this->create($type);
    }

    public function supports(string $type): bool
    {
        return match ($type) {
            \DateTimeImmutable::class, \DateTime::class => true,
            default => false,
        };
    }

    private function create(string $type): \DateTimeImmutable|\DateTime|null
    {
        if (\DateTimeImmutable::class === $type) {
            return \DateTimeImmutable::createFromInterface($this->clock->now());
        }

        if (\DateTime::class === $type) {
            return \DateTime::createFromImmutable($this->clock->now());
        }

        return null;
    }

    public function reset(): void
    {
        $this->cache = [];
    }
}
