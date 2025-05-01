<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SoureCode\Bundle\DoctrineExtension\Contracts\TimestampableInterface;
use SoureCode\Bundle\DoctrineExtension\Traits\TimestampableTrait;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Contracts\Service\ResetInterface;

final class TimestampableListener implements ResetInterface
{
    private ?\DateTimeInterface $now = null;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly string $createdAtType,
        private readonly string $updatedAtType,
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

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $reflectionClass = $classMetadata->getReflectionClass();

        if (
            $reflectionClass->implementsInterface(TimestampableInterface::class)
            && \in_array(TimestampableTrait::class, $reflectionClass->getTraitNames(), true)
        ) {
            $classMetadataBuilder = new ClassMetadataBuilder($classMetadata);

            $classMetadataBuilder->createField('createdAt', $this->createdAtType)
                ->build();

            $classMetadataBuilder->createField('updatedAt', $this->updatedAtType)
                ->nullable()
                ->build();
        }
    }

    public function reset(): void
    {
        $this->now = null;
    }
}
