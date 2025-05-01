<?php

namespace SoureCode\Bundle\DoctrineExtension\Traits;

use Doctrine\Common\Collections\Collection;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;

/**
 * @template T of TranslationInterface = TranslationInterface
 */
trait TranslatableTrait
{
    /**
     * @var Collection<string, T>
     */
    private Collection $translations;

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TranslationInterface $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->set($translation->getLocale(), $translation);

            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(TranslationInterface $translation): static
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    public function getTranslation(string $locale): ?TranslationInterface
    {
        return $this->translations->get($locale);
    }
}
