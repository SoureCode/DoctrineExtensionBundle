<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Contracts\BlameableInterface;
use SoureCode\Bundle\DoctrineExtension\Traits\BlameableTrait;

#[ORM\Entity]
class Category implements BlameableInterface
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $title = null;

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
}
