<?php

namespace SoureCode\Bundle\DoctrineExtension\Mapping;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;

/**
 * @phpstan-import-type PropertyCollectionMetadataType from PropertyMetadata
 *
 * @phpstan-type ClassMetadataType array{
 *      className: class-string,
 *      translationClassName: class-string<TranslationInterface>|null,
 *      translatableClassName: class-string|null,
 *      persistProperties: PropertyCollectionMetadataType,
 *      updateProperties: PropertyCollectionMetadataType,
 *  }
 */
final class ClassMetadata
{
    public function __construct(
        /**
         * @var class-string
         */
        public readonly string $className,
        /**
         * @var class-string<TranslationInterface>|null
         */
        public readonly ?string $translationClassName,
        /**
         * @var class-string|null
         */
        public readonly ?string $translatableClassName,
        /**
         * @var array<string, PropertyMetadata>
         */
        public readonly array $persistProperties,
        /**
         * @var array<string, PropertyMetadata>
         */
        public readonly array $updateProperties,
    ) {
    }

    /**
     * @phpstan-return ClassMetadataType
     */
    public function toArray(): array
    {
        return [
            'className' => $this->className,
            'translationClassName' => $this->translationClassName,
            'translatableClassName' => $this->translatableClassName,
            'persistProperties' => array_map(static fn (PropertyMetadata $propertyMetadata) => $propertyMetadata->toArray(), $this->persistProperties),
            'updateProperties' => array_map(static fn (PropertyMetadata $propertyMetadata) => $propertyMetadata->toArray(), $this->updateProperties),
        ];
    }

    /**
     * @phpstan-param ClassMetadataType $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            className: $data['className'],
            translationClassName: $data['translationClassName'],
            translatableClassName: $data['translatableClassName'],
            persistProperties: array_map(static fn (array $propertyMetadata) => PropertyMetadata::fromArray($propertyMetadata), $data['persistProperties']),
            updateProperties: array_map(static fn (array $propertyMetadata) => PropertyMetadata::fromArray($propertyMetadata), $data['updateProperties']),
        );
    }
}
