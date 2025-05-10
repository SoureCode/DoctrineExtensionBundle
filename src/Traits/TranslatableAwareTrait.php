<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

trait TranslatableAwareTrait
{
    use LocaleAwareTrait;

    private object $translatable;

    public function setTranslatable(object $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getTranslatable(): object
    {
        return $this->translatable;
    }
}
