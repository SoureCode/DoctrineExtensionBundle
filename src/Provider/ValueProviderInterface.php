<?php

namespace SoureCode\Bundle\DoctrineExtension\Provider;

/**
 * @template T of mixed = mixed
 */
interface ValueProviderInterface
{
    /**
     * @return T|null
     */
    public function provide(string $type): mixed;

    public function supports(string $type): bool;
}
