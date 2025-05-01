<?php

namespace SoureCode\Bundle\DoctrineExtension\Contracts;

use Doctrine\Common\Collections\Collection;

interface TranslatableInterface
{
    public function getTranslations(): Collection;

    public function addTranslation(TranslationInterface $translation): static;

    public function removeTranslation(TranslationInterface $translation): static;

    public function getTranslation(string $locale): ?TranslationInterface;
}
