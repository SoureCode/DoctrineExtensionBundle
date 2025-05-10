<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnUpdate;
use Symfony\Component\Security\Core\User\UserInterface;

trait WithUpdatedByTrait
{
    #[SetOnUpdate]
    protected ?UserInterface $updatedBy = null;

    public function getUpdatedBy(): ?UserInterface
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?UserInterface $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
