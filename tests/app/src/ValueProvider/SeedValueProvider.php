<?php

namespace App\ValueProvider;

use SoureCode\Bundle\DoctrineExtension\Provider\ValueProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @implements ValueProviderInterface<string>
 */
#[AutoconfigureTag('soure_code.doctrine_extension.value_provider')]
class SeedValueProvider implements ValueProviderInterface
{
    public function provide(string $type): ?string
    {
        return bin2hex(random_bytes(16));
    }

    public function supports(string $type): bool
    {
        return 'seed' === $type;
    }
}
