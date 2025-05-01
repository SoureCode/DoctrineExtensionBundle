<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use SoureCode\Bundle\DoctrineExtension\Attributes\Translatable;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;
use SoureCode\Bundle\DoctrineExtension\Traits\TranslatableTrait;
use Doctrine\ORM\Mapping as ORM;

#[Translatable(ProductTranslation::class)]
#[ORM\Entity]
class Product implements TranslatableInterface
{
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}