<?php

namespace SoureCode\Bundle\DoctrineExtension\Contracts;

interface LocaleAwareInterface
{
    public function getLocale(): string;

    public function setLocale(string $locale): self;
}
