<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

use Symfony\Component\Security\Core\User\UserInterface;

trait BlameableTrait
{
    protected ?UserInterface $createdBy = null;

    protected ?UserInterface $updatedBy = null;

    public function getCreatedBy(): ?UserInterface
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserInterface $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?UserInterface
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(UserInterface $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
