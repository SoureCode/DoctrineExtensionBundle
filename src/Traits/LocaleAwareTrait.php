<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

trait LocaleAwareTrait
{
    protected string $locale;

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
