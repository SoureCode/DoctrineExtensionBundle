<?php

namespace SoureCode\Bundle\DoctrineExtension\Mapping;

use Symfony\Contracts\Service\ResetInterface;

final class ClassMetadataFactory implements ResetInterface
{
    /**
     * @var array<class-string, ClassMetadata>
     */
    private array $cache = [];

    public function __construct(
        private readonly ClassMetadataGenerator $mappingGenerator,
    ) {
    }

    /**
     * @param class-string $className
     */
    public function create(string $className): ClassMetadata
    {
        return $this->cache[$className] ??= $this->doCreate($className);
    }

    /**
     * @param class-string $className
     */
    private function doCreate(string $className): ClassMetadata
    {
        $classMetadata = $this->mappingGenerator->get($className);

        return ClassMetadata::fromArray($classMetadata);
    }

    public function reset(): void
    {
        $this->cache = [];
    }
}
