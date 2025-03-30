<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Clock\ClockInterface;
use SoureCode\Bundle\DoctrineExtension\Contracts\TimestampableInterface;
use Symfony\Contracts\Service\ResetInterface;

final class TimestampableListener implements ResetInterface
{
    private ?\DateTimeInterface $now = null;

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $object = $event->getObject();
        $this->now ??= \DateTimeImmutable::createFromInterface($this->clock->now());

        if ($object instanceof TimestampableInterface) {
            $object->setCreatedAt($this->now);
        }
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $object = $event->getObject();
        $this->now ??= \DateTimeImmutable::createFromInterface($this->clock->now());

        if ($object instanceof TimestampableInterface) {
            $object->setUpdatedAt($this->now);
        }
    }

    public function reset(): void
    {
        $this->now = null;
    }
}
