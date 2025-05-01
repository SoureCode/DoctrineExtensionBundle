<?php

namespace SoureCode\Bundle\DoctrineExtension\Translation;

use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

final class TranslationMapping
{
    /**
     * @var array<class-string<TranslatableInterface>, class-string<TranslationInterface>>
     */
    private ?array $mapping = null;

    /**
     * @var list<class-string<TranslatableInterface>>
     */
    private ?array $translatableClassNames = null;

    /**
     * @var list<class-string<TranslationInterface>>
     */
    private ?array $translationClassNames = null;
    /**
     * @var array<class-string<TranslationInterface>, class-string<TranslatableInterface>>
     */
    private ?array $reverseMapping = null;

    public function __construct(
        private readonly AdapterInterface $cache,
        private readonly MappingGenerator $mappingGenerator,
    ) {
    }

    /**
     * @param class-string $className
     */
    public function hasTranslationClass(string $className): bool
    {
        return \in_array($className, $this->getTranslationClassNames(), true);
    }

    /**
     * @return list<class-string<TranslationInterface>>
     */
    public function getTranslationClassNames(): array
    {
        return $this->translationClassNames ??= array_values($this->getMapping());
    }

    /**
     * @return array<class-string<TranslatableInterface>, class-string<TranslationInterface>>
     */
    public function getMapping(): array
    {
        if (null !== $this->mapping) {
            return $this->mapping;
        }

        /**
         * @var array<class-string<TranslatableInterface>, class-string<TranslationInterface>>|null $cacheItem
         */
        $cacheItem = $this->cache->getItem(MappingGenerator::CACHE_KEY_MAPPING)->get();

        if (null === $cacheItem) {
            $this->mapping = $mapping = $this->mappingGenerator->generate();

            return $mapping;
        }

        return $this->mapping = $cacheItem;
    }

    /**
     * @param class-string $className
     */
    public function hasTranslatableClass(string $className): bool
    {
        return \in_array($className, $this->getTranslatableClassNames(), true);
    }

    /**
     * @return list<class-string<TranslatableInterface>>
     */
    public function getTranslatableClassNames(): array
    {
        return $this->translatableClassNames ??= array_keys($this->getMapping());
    }

    /**
     * @param class-string<TranslationInterface> $className
     *
     * @return class-string<TranslatableInterface>
     */
    public function getTranslatableClass(string $className): string
    {
        return $this->getReverseMapping()[$className] ?? throw new \RuntimeException(\sprintf('Class "%s" is not translatable.', $className));
    }

    /**
     * @return array<class-string<TranslationInterface>, class-string<TranslatableInterface>>
     */
    public function getReverseMapping(): array
    {
        return $this->reverseMapping ??= array_flip($this->getMapping());
    }

    /**
     * @param class-string<TranslatableInterface> $className
     *
     * @return class-string<TranslationInterface>
     */
    public function getTranslationClass(string $className): string
    {
        // @todo add more helpful info in the exception - why is it not translatable?
        return $this->getMapping()[$className] ?? throw new \RuntimeException(\sprintf('Class "%s" is not translatable.', $className));
    }
}
