<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnUpdate;

/**
 * The updatedAt is nullable as we may want to track if something has been updated at all.
 */
trait WithUpdatedAtTrait
{
    #[SetOnUpdate]
    protected ?\DateTimeInterface $updatedAt = null;

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
