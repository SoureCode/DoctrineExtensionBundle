<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnPersist;

#[ORM\Entity]
class MissingProviderName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[SetOnPersist]
    #[ORM\Column()]
    private string $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
