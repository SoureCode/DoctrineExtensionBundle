<?php

namespace SoureCode\Bundle\DoctrineExtension\Contracts;

interface TranslationInterface extends LocaleAwareInterface
{
    public function setTranslatable(object $translatable): self;

    public function getTranslatable(): object;
}
