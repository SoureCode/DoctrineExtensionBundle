<?php

namespace SoureCode\Bundle\DoctrineExtension\Contracts;

use Doctrine\Common\Collections\Collection;

/**
 * @template T of TranslationInterface = TranslationInterface
 *
 * @templa
 */
interface TranslatableInterface
{
    /**
     * @return Collection<string, T>
     */
    public function getTranslations(): Collection;

    /**
     * @phpstan-param T $translation
     */
    public function addTranslation(TranslationInterface $translation): static;

    /**
     * @phpstan-param T $translation
     */
    public function removeTranslation(TranslationInterface $translation): static;

    /**
     * @phpstan-return T|null
     */
    public function getTranslation(string $locale): ?TranslationInterface;
}
