<?php

namespace SoureCode\Bundle\DoctrineExtension\Translation;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;

interface EntityTranslatorInterface
{
    public function getTranslation(TranslatableInterface $translatable, ?string $locale = null): ?TranslationInterface;

    public function getTranslationValue(TranslatableInterface $translatable, string $fieldName, ?string $locale = null): mixed;
}
