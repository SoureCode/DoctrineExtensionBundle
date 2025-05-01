<?php

namespace SoureCode\Bundle\DoctrineExtension\Translation;

use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\DoctrineExtension\Attributes\Translatable;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @todo add cache warmer
 */
final class TranslationMapping
{
    /**
     * @var array<class-string<TranslatableInterface>, class-string<TranslationInterface>>
     */
    private ?array $mapping = null;

    /**
     * @var array<class-string<TranslatableInterface>>
     */
    private ?array $translatableClassNames = null;

    /**
     * @var array<class-string<TranslationInterface>>
     */
    private ?array $translationClassNames = null;
    /**
     * @var array<class-string<TranslationInterface>, class-string<TranslatableInterface>>
     */
    private ?array $reverseMapping = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
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
     * @psalm-return array<class-string<TranslationInterface>>
     */
    public function getTranslationClassNames(): array
    {
        return $this->translationClassNames ??= array_values($this->getMapping());
    }

    public function getMapping(): array
    {
        return $this->mapping ??= $this->generate();
    }

    /**
     * @return array<class-string<TranslatableInterface>, class-string<TranslationInterface>>
     */
    private function generate(): array
    {
        return $this->cache->get('soure_code_doctrine_extension.translatable_mapping', function () {
            return $this->doGenerate();
        });
    }

    /**
     * @return array<class-string<TranslatableInterface>, class-string<TranslationInterface>>
     */
    private function doGenerate(): array
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

        return $mapping;
    }

    /**
     * @param class-string $className
     */
    public function hasTranslatableClass(string $className): bool
    {
        return \in_array($className, $this->getTranslatableClassNames(), true);
    }

    /**
     * @psalm-return array<class-string<TranslatableInterface>>
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
