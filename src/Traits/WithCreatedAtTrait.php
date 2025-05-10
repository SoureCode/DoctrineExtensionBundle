<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnPersist;

/**
 * The createdAt is not nullable as it is set when the entity is created, and we expect it to always be set. (At least after we flushed the entity).
 */
trait WithCreatedAtTrait
{
    #[SetOnPersist]
    protected \DateTimeInterface $createdAt;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
