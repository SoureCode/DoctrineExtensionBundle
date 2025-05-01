<?php

namespace App\Entity;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use SoureCode\Bundle\DoctrineExtension\Traits\TranslationTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProductTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): ProductTranslation
    {
        $this->name = $name;
        return $this;
    }
}