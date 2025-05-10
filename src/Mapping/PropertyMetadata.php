<?php

namespace SoureCode\Bundle\DoctrineExtension\Mapping;

use SoureCode\Bundle\DoctrineExtension\Attributes\Mode;

/**
 * @phpstan-type PropertyMetadataType array{name: string, provider: string, propertyType: string|class-string, mode: Mode, doctrineType: string|null, nullable: bool}
 * @phpstan-type PropertyCollectionMetadataType = array<string, PropertyMetadataType>
 */
class PropertyMetadata
{
    public function __construct(
        public readonly string $name,
        public readonly string $provider,
        public readonly string $propertyType,
        public readonly Mode $mode,
        public readonly ?string $doctrineType,
        public readonly bool $nullable,
    ) {
    }

    /**
     * @phpstan-param PropertyMetadataType $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['provider'],
            $data['propertyType'],
            $data['mode'],
            $data['doctrineType'],
            $data['nullable'],
        );
    }

    /**
     * @phpstan-return PropertyMetadataType
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'provider' => $this->provider,
            'propertyType' => $this->propertyType,
            'mode' => $this->mode,
            'doctrineType' => $this->doctrineType,
            'nullable' => $this->nullable,
        ];
    }
}
