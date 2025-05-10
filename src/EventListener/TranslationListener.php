<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadata;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataFactory;

final class TranslationListener
{
    public function __construct(
        private readonly ClassMetadataFactory $classMetadataFactory,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /**
         * @var DoctrineClassMetadata<TranslationInterface> $doctrineClassMetadata
         */
        $doctrineClassMetadata = $event->getClassMetadata();
        $classMetadata = $this->classMetadataFactory->create($doctrineClassMetadata->getName());

        if (
            null !== $classMetadata->translatableClassName
            && $doctrineClassMetadata->getReflectionClass()->implementsInterface(TranslationInterface::class)
        ) {
            $this->mapTranslation($event->getEntityManager(), $doctrineClassMetadata, $classMetadata);

            $classMetadataBuilder = new ClassMetadataBuilder($doctrineClassMetadata);
            $classMetadataBuilder->createField('locale', Types::STRING)
                ->nullable(false)
                ->length(5)
                ->build();
        }
    }

    /**
     * @phpstan-param DoctrineClassMetadata<TranslationInterface> $translationDoctrineClassMetadata
     */
    private function mapTranslation(EntityManagerInterface $entityManager, DoctrineClassMetadata $translationDoctrineClassMetadata, ClassMetadata $classMetadata): void
    {
        $translatableClassName = $classMetadata->translatableClassName;

        if (null === $translatableClassName) {
            return;
        }

        $translatableDoctrineClassMetadata = $entityManager->getClassMetadata($translatableClassName);
        $classMetadataBuilder = new ClassMetadataBuilder($translationDoctrineClassMetadata);

        $associationBuilder = $classMetadataBuilder
            ->createManyToOne('translatable', $translatableClassName)
            ->inversedBy('translations')
        ;

        foreach ($translatableDoctrineClassMetadata->getIdentifierColumnNames() as $columnName) {
            $associationBuilder->addJoinColumn(
                \sprintf('translatable_%s', $columnName),
                $columnName,
                true,
                false,
                'CASCADE'
            );
        }

        $associationBuilder->build();

        /*
         * @note: I do not use $classMetadataBuilder->addUniqueConstraint() as it requires a name property, which I do NOT want to generate myself.
         * @see ClassMetadataBuilder::addUniqueConstraint()
         */
        $translationDoctrineClassMetadata->table['uniqueConstraints'][] = ['fields' => ['locale', 'translatable']];
    }
}
