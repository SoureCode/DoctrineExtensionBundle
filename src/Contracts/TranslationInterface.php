<?php

namespace SoureCode\Bundle\DoctrineExtension\Contracts;

interface TranslationInterface
{
    public function getLocale(): string;

    public function setLocale(string $locale): self;

    public function getTranslatable(): TranslatableInterface;

    public function setTranslatable(TranslatableInterface $translatable): self;
}
