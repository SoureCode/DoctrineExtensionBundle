<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Attributes\Mode;
use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnPersist;
use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnUpdate;

#[ORM\Entity]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[SetOnPersist(provider: 'seed')]
    #[ORM\Column]
    private string $name;

    #[SetOnUpdate(provider: 'seed', mode: Mode::ALWAYS)]
    #[ORM\Column(nullable: true)]
    private ?string $etag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEtag(): ?string
    {
        return $this->etag;
    }

    public function setEtag(?string $etag): void
    {
        $this->etag = $etag;
    }
}
