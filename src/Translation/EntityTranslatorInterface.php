<?php

namespace SoureCode\Bundle\DoctrineExtension\Translation;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;

interface EntityTranslatorInterface
{
    public function getTranslation(object $translatable, ?string $locale = null): ?TranslationInterface;

    public function getTranslationValue(object $translatable, string $fieldName, ?string $locale = null): mixed;
}
