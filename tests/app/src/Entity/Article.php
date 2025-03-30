<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Contracts\BlameableInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class Article implements BlameableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $title = null;

    #[ORM\Column()]
    private ?string $createdBy = null;

    #[ORM\Column(nullable: true)]
    private ?string $updatedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getCreatedBy(): ?UserInterface
    {
        return $this->createdBy ? new InMemoryUser($this->createdBy, '') : null;
    }

    public function setCreatedBy(UserInterface $createdBy): self
    {
        $this->createdBy = $createdBy->getUserIdentifier();

        return $this;
    }

    public function getUpdatedBy(): ?UserInterface
    {
        return $this->updatedBy ? new InMemoryUser($this->updatedBy, '') : null;
    }

    public function setUpdatedBy(UserInterface $updatedBy): self
    {
        $this->updatedBy = $updatedBy->getUserIdentifier();

        return $this;
    }
}
