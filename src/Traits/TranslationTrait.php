<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;

trait TranslationTrait
{
    private string $locale;

    private TranslatableInterface $translatable;

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTranslatable(): TranslatableInterface
    {
        return $this->translatable;
    }

    public function setTranslatable(TranslatableInterface $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }
}
