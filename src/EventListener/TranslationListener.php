<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use SoureCode\Bundle\DoctrineExtension\Contracts\TranslationInterface;
use SoureCode\Bundle\DoctrineExtension\Translation\TranslationMapping;

final class TranslationListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslationMapping $translationMapping,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $classMetadata = $event->getClassMetadata();

        if (\in_array($classMetadata->getName(), $this->translationMapping->getTranslationClassNames(), true)) {
            $this->mapTranslation($classMetadata, $this->translationMapping->getReverseMapping()[$classMetadata->getName()]);
        }
    }

    /**
     * @psalm-param \ReflectionClass<TranslationInterface> $reflectionClass
     */
    private function mapTranslation(ClassMetadata $classMetadata, string $translatableClassName): void
    {
        $translatableClassMetadata = $this->entityManager->getClassMetadata($translatableClassName);
        $classMetadataBuilder = new ClassMetadataBuilder($classMetadata);

        $classMetadataBuilder->createField('locale', 'string')
            ->nullable(false)
            ->length(5)
            ->build();

        $associationBuilder = $classMetadataBuilder
            ->createManyToOne('translatable', $translatableClassName)
            ->inversedBy('translations')
        ;

        foreach ($translatableClassMetadata->getIdentifierColumnNames() as $columnName) {
            $associationBuilder->addJoinColumn(
                'translatable_'.$columnName,
                $columnName,
                true,
                false,
                'CASCADE'
            );
        }

        $associationBuilder->build();

        $classMetadata->table['uniqueConstraints'][] = ['fields' => ['locale', 'translatable']];
    }
}
