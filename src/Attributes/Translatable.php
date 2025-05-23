<?php

namespace SoureCode\Bundle\DoctrineExtension\Attributes;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Translatable
{
    /**
     * @var class-string<TranslationInterface>
     */
    public string $translationClass;

    /**
     * @param class-string $translationClass
     */
    public function __construct(string $translationClass)
    {
        if (!is_subclass_of($translationClass, TranslationInterface::class)) {
            throw new \InvalidArgumentException(\sprintf('The class "%s" must implement "%s".', $translationClass, TranslationInterface::class));
        }

        $this->translationClass = $translationClass;
    }
}
