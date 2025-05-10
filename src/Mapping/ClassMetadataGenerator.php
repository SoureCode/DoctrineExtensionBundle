<?php

namespace SoureCode\Bundle\DoctrineExtension\Mapping;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Column;
use SoureCode\Bundle\DoctrineExtension\Attributes\Mode;
use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnPersist;
use SoureCode\Bundle\DoctrineExtension\Attributes\SetOnUpdate;
use SoureCode\Bundle\DoctrineExtension\Attributes\Translatable;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @phpstan-import-type PropertyCollectionMetadataType from PropertyMetadata
 * @phpstan-import-type PropertyMetadataType from PropertyMetadata
 * @phpstan-import-type ClassMetadataType from ClassMetadata
 */
final class ClassMetadataGenerator
{
    public const string CACHE_KEY_METADATA = 'soure_code.doctrine_extension.metadata';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdapterInterface $cache,
        private readonly string $persistDateTimeClassName,
        private readonly string $updatedDateTimeClassName,
        private readonly string $userClassName,
    ) {
    }

    public function generateAll(): void
    {
        $configuration = $this->entityManager->getConfiguration();
        $classMetadataFactory = $configuration->getMetadataDriverImpl();

        if (!$classMetadataFactory) {
            throw new \RuntimeException('No metadata driver found.');
        }

        $classNames = $classMetadataFactory->getAllClassNames();

        foreach ($classNames as $className) {
            $this->generate($className);
        }
    }

    /**
     * Regenerate the metadata for a class and store it in the cache.
     *
     * @param class-string $className
     *
     * @return ClassMetadataType
     */
    public function generate(string $className): array
    {
        /**
         * @var array<class-string<TranslationInterface>, class-string> $translationClassMapping
         */
        static $translationClassMapping = [];

        $reflectionClass = new \ReflectionClass($className);

        /**
         * @var class-string<TranslationInterface>|null $translationClassName
         */
        $translationClassName = null;
        $translatableClassName = null;
        /**
         * @var PropertyCollectionMetadataType $persistProperties
         */
        $persistProperties = [];
        /**
         * @var PropertyCollectionMetadataType $updateProperties
         */
        $updateProperties = [];

        if ($attribute = $reflectionClass->getAttributes(Translatable::class)[0] ?? null) {
            /**
             * @var Translatable $attributeInstance
             */
            $attributeInstance = $attribute->newInstance();
            $translationClassName = $attributeInstance->translationClass;
            $translatableClassName = $className;

            if ($translationClassName === $className) {
                throw new \LogicException(\sprintf('Class "%s" cannot be used as translation class for itself.', $translationClassName));
            }

            $translationClassMapping[$translationClassName] = $className;

            // Regenerate metadata for translation class - as the translatable is now determined
            $this->generate($translationClassName);
        }

        // it does not have any Translatable attribute, but maybe it is already used as a translation?
        if ((null === $translatableClassName) && isset($translationClassMapping[$className])) {
            $translatableClassName = $translationClassMapping[$className];
            /**
             * @var class-string<TranslationInterface> $translationClassName
             */
            $translationClassName = $className;
        }

        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            if ($attribute = $property->getAttributes(SetOnPersist::class)[0] ?? null) {
                /**
                 * @var SetOnPersist $attributeInstance
                 */
                $attributeInstance = $attribute->newInstance();
                $persistProperties[$property->getName()] = [
                    ...$this->resolveData($attributeInstance->provider, $property, true),
                    'mode' => $attributeInstance->mode,
                ];
            }

            if ($attribute = $property->getAttributes(SetOnUpdate::class)[0] ?? null) {
                /**
                 * @var SetOnUpdate $attributeInstance
                 */
                $attributeInstance = $attribute->newInstance();
                $updateProperties[$property->getName()] = [
                    ...$this->resolveData($attributeInstance->provider, $property, false),
                    'mode' => $attributeInstance->mode,
                ];
            }
        }

        $classMetadata = [
            'className' => $className,
            'translationClassName' => $translationClassName,
            'translatableClassName' => $translatableClassName,
            'persistProperties' => $persistProperties,
            'updateProperties' => $updateProperties,
        ];

        $cacheItem = $this->cache->getItem(\sprintf('%s.%s', self::CACHE_KEY_METADATA, hash('sha256', $className)));
        $cacheItem->set($classMetadata);

        $this->cache->save($cacheItem);

        return $classMetadata;
    }

    /**
     * @return PropertyMetadataType
     */
    private function resolveData(?string $providerName, \ReflectionProperty $reflectionProperty, bool $persist): array
    {
        $propertyType = $reflectionProperty->getType();
        $reflectionType = $this->getReflectionType($propertyType);

        if (null === $reflectionType) {
            throw new \RuntimeException('Field without a type is not supported.');
        }

        $reflectionTypeName = $reflectionType->getName();
        $doctrineType = $this->resolveDoctrineType($reflectionProperty, $reflectionType);
        $nullable = $reflectionType->allowsNull();

        $base = [
            'name' => $reflectionProperty->getName(),
            'provider' => $providerName,
            'propertyType' => $reflectionTypeName,
            'doctrineType' => $doctrineType,
            'nullable' => $nullable,
            'mode' => Mode::ALWAYS,
        ];

        // Default user class
        if (UserInterface::class === $reflectionTypeName || is_subclass_of($reflectionTypeName, UserInterface::class)) {
            return [
                ...$base,
                'provider' => $providerName ?? UserInterface::class,
                'propertyType' => $this->userClassName,
            ];
        }

        // Default DateTime or DateTimeImmutable class on interface
        if (\DateTimeInterface::class === $reflectionTypeName) {
            return [
                ...$base,
                'provider' => $providerName ?? ($persist ? $this->persistDateTimeClassName : $this->updatedDateTimeClassName),
                'propertyType' => ($persist ? $this->persistDateTimeClassName : $this->updatedDateTimeClassName),
            ];
        }

        if (\DateTime::class === $reflectionTypeName) {
            return [
                ...$base,
                'provider' => $providerName ?? \DateTime::class,
                'propertyType' => \DateTime::class,
            ];
        }

        if (\DateTimeImmutable::class === $reflectionTypeName) {
            return [
                ...$base,
                'provider' => $providerName ?? \DateTimeImmutable::class,
                'propertyType' => \DateTimeImmutable::class,
            ];
        }

        if (null === $providerName) {
            throw new \RuntimeException('Provider is required if type is not a automatically resolvable type (e.g. DateTime, UserInterface).');
        }

        return [
            ...$base,
            'provider' => $providerName,
        ];
    }

    private function getReflectionType(?\ReflectionType $propertyType): ?\ReflectionNamedType
    {
        if (null === $propertyType) {
            return null;
        }

        if ($propertyType instanceof \ReflectionNamedType) {
            return $propertyType;
        }

        throw new \RuntimeException('Intersection and union types are not supported.');
    }

    private function resolveDoctrineType(\ReflectionProperty $reflectionProperty, \ReflectionNamedType $reflectionType): ?string
    {
        if ($attribute = $reflectionProperty->getAttributes(Column::class)[0] ?? null) {
            /**
             * @var Column $attributeInstance
             */
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance->type) {
                return $attributeInstance->type;
            }
        }

        $type = $reflectionType->getName();

        if (\DateTime::class === $type) {
            return Types::DATETIME_MUTABLE;
        }

        if (\DateTimeInterface::class === $type) {
            return Types::DATETIME_IMMUTABLE;
        }

        if (\DateTimeImmutable::class === $type) {
            return Types::DATETIME_IMMUTABLE;
        }

        return null;
    }

    /**
     * Get the metadata for a class from the cache or generate it if not available.
     *
     * @param class-string $className
     *
     * @return ClassMetadataType
     */
    public function get(string $className): array
    {
        /**
         * @var ClassMetadataType|null $cacheItem
         */
        $cacheItem = $this->cache->getItem(\sprintf('%s.%s', self::CACHE_KEY_METADATA, hash('sha256', $className)))->get();

        return $cacheItem ?? $this->generate($className);
    }
}
