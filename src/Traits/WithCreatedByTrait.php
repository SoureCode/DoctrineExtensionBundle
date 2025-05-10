<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnPersist;
use Symfony\Component\Security\Core\User\UserInterface;

trait WithCreatedByTrait
{
    #[SetOnPersist]
    protected UserInterface $createdBy;

    public function getCreatedBy(): UserInterface
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserInterface $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
