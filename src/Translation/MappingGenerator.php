<?php

namespace SoureCode\Bundle\DoctrineExtension\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use SoureCode\Bundle\DoctrineExtension\Attributes\Translatable;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;

final class MappingGenerator
{
    public const string CACHE_KEY_MAPPING = 'soure_code.doctrine_extension.mapping';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @return array<class-string<TranslatableInterface>, class-string<TranslationInterface>>
     */
    public function generate(): array
    {
        /**
         * @var array<class-string<TranslatableInterface>, class-string<TranslationInterface>> $mapping
         */
        $mapping = [];
        $translatableClassNames = [];
        $translationClassNames = [];
        $configuration = $this->entityManager->getConfiguration();
        $classMetadataFactory = $configuration->getMetadataDriverImpl();

        if (!$classMetadataFactory) {
            throw new \RuntimeException('No metadata driver found.');
        }

        $classNames = $classMetadataFactory->getAllClassNames();

        foreach ($classNames as $className) {
            $translatableReflectionClass = new \ReflectionClass($className);

            if (!$translatableReflectionClass->implementsInterface(TranslatableInterface::class)) {
                continue;
            }

            if ($attribute = $translatableReflectionClass->getAttributes(Translatable::class)[0] ?? null) {
                /**
                 * @var Translatable $attributeInstance
                 */
                $attributeInstance = $attribute->newInstance();
                $translationClassName = $attributeInstance->translationClass;

                if (!\in_array($translationClassName, $classNames, true)) {
                    throw new \RuntimeException(\sprintf('Class "%s" is not a entity class.', $translationClassName));
                }

                $translatableClassName = $translatableReflectionClass->getName();

                if ($translationClassName === $translatableClassName) {
                    throw new \RuntimeException(\sprintf('Class "%s" cannot be used as translation class for itself.', $translationClassName));
                }

                if (\in_array($translatableClassName, $translatableClassNames, true)) {
                    throw new \RuntimeException(\sprintf('Class "%s" is already used as a translatable class.', $translatableClassName));
                }

                if (\in_array($translationClassName, $translationClassNames, true)) {
                    throw new \RuntimeException(\sprintf('Class "%s" is already used as a translation class.', $translationClassName));
                }

                $translatableClassNames[] = $translatableClassName;
                $translationClassNames[] = $translationClassName;
                $mapping[$translatableClassName] = $translationClassName;
            } else {
                throw new \RuntimeException(\sprintf('Missing attribute "%s" in class "%s".', Translatable::class, $translatableReflectionClass->getName()));
            }
        }

        $cacheItem = $this->cache->getItem(self::CACHE_KEY_MAPPING);
        $cacheItem->set($mapping);

        $this->cache->save($cacheItem);

        return $mapping;
    }
}
