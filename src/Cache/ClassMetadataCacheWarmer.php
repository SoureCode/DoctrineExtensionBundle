<?php

namespace SoureCode\Bundle\DoctrineExtension\Cache;

use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataGenerator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class ClassMetadataCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly ClassMetadataGenerator $classMetadataGenerator,
    ) {
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->classMetadataGenerator->generateAll();

        return [];
    }

    public function isOptional(): bool
    {
        return true;
    }
}
