<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Contracts\TimestampableInterface;
use SoureCode\Bundle\DoctrineExtension\Traits\TimestampableTrait;

#[ORM\Entity]
class Post implements TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column]
    private string $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
