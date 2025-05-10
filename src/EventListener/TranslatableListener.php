<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrinaClassMetadata;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadata;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataFactory;

final class TranslatableListener
{
    public function __construct(
        private readonly ClassMetadataFactory $classMetadataFactory,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /**
         * @var DoctrinaClassMetadata<object> $doctrineClassMetadata
         */
        $doctrineClassMetadata = $event->getClassMetadata();
        $classMetadata = $this->classMetadataFactory->create($doctrineClassMetadata->getName());

        if (
            null !== $classMetadata->translationClassName
            && !$doctrineClassMetadata->getReflectionClass()->implementsInterface(TranslationInterface::class)
        ) {
            $this->mapTranslatable($doctrineClassMetadata, $classMetadata);
        }
    }

    /**
     * @phpstan-param DoctrinaClassMetadata<object> $doctrineClassMetadata
     */
    private function mapTranslatable(DoctrinaClassMetadata $doctrineClassMetadata, ClassMetadata $classMetadata): void
    {
        $translationClassName = $classMetadata->translationClassName;

        if (null === $translationClassName) {
            return;
        }

        $classMetadataBuilder = new ClassMetadataBuilder($doctrineClassMetadata);

        $classMetadataBuilder->createOneToMany('translations', $translationClassName)
            ->mappedBy('translatable')
            ->orphanRemoval()
            ->cascadePersist()
            ->cascadeRemove()
            ->setIndexBy('locale')
            ->build();
    }
}
