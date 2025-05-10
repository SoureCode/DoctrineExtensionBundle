<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnPersist;

#[ORM\Entity]
class UnsupportedPropertyType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[SetOnPersist]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface|\DateTime $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface|\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface|\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
