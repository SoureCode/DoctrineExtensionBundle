<?php

namespace SoureCode\Bundle\DoctrineExtension\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class SetOnPersist
{
    public function __construct(
        public ?string $provider = null,
        public Mode $mode = Mode::ALWAYS,
    ) {
    }
}
