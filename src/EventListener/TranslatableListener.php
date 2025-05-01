<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslatableInterface;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use SoureCode\Bundle\DoctrineExtension\Translation\TranslationMapping;

final class TranslatableListener
{
    public function __construct(
        private readonly TranslationMapping $translationMapping,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /**
         * @var ClassMetadata<TranslatableInterface> $classMetadata
         */
        $classMetadata = $event->getClassMetadata();

        if (\in_array($classMetadata->getName(), $this->translationMapping->getTranslatableClassNames(), true)) {
            $this->mapTranslatable($classMetadata, $this->translationMapping->getMapping()[$classMetadata->getName()]);
        }
    }

    /**
     * @param ClassMetadata<TranslatableInterface> $classMetadata
     * @param class-string<TranslationInterface>   $translationClassName
     */
    private function mapTranslatable(ClassMetadata $classMetadata, string $translationClassName): void
    {
        $classMetadataBuilder = new ClassMetadataBuilder($classMetadata);

        $classMetadataBuilder->createOneToMany('translations', $translationClassName)
            ->mappedBy('translatable')
            ->orphanRemoval()
            ->cascadePersist()
            ->cascadeRemove()
            ->setIndexBy('locale')
            ->build();
    }
}
