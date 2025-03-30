<?php

namespace SoureCode\Bundle\DoctrineExtension\Contracts;

use Symfony\Component\Security\Core\User\UserInterface;

interface BlameableInterface
{
    public function getCreatedBy(): ?UserInterface;

    public function setCreatedBy(UserInterface $createdBy): self;

    public function getUpdatedBy(): ?UserInterface;

    public function setUpdatedBy(UserInterface $updatedBy): self;
}
