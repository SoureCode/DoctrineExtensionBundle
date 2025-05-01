<?php

namespace SoureCode\Bundle\DoctrineExtension\Attributes;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Translatable
{
    public function __construct(
        /**
         * @var class-string
         */
        public string $translationClass,
    ) {
        if (!is_subclass_of($translationClass, TranslationInterface::class)) {
            throw new \InvalidArgumentException(\sprintf('The class "%s" must implement "%s".', $translationClass, TranslationInterface::class));
        }
    }
}
