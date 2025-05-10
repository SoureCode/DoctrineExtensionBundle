<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SoureCode\Bundle\DoctrineExtension\Attributes\Translatable;
use SoureCode\Bundle\DoctrineExtension\Traits\TimestampAwareTrait;
use SoureCode\Bundle\DoctrineExtension\Traits\TranslationAwareTrait;

#[ORM\Entity]
#[Translatable(ProductTranslation::class)]
class Product
{
    use TimestampAwareTrait;
    /**
     * @use TranslationAwareTrait<ProductTranslation>
     */
    use TranslationAwareTrait;

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
