<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\UnitOfWork;
use SoureCode\Bundle\DoctrineExtension\Attributes\Mode;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadata;
use SoureCode\Bundle\DoctrineExtension\Mapping\ClassMetadataFactory;
use SoureCode\Bundle\DoctrineExtension\Mapping\PropertyMetadata;
use SoureCode\Bundle\DoctrineExtension\Provider\ValueProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @phpstan-import-type PropertyCollectionMetadataType from PropertyMetadata
 * @phpstan-import-type PropertyMetadataType from PropertyMetadata
 */
final class PropertyListener implements ResetInterface
{
    /**
     * @var array<class-string, ClassMetadata>
     */
    private array $classMetadataCache = [];

    /**
     * @var array<string, ValueProviderInterface>
     */
    private array $valueProvidersCache = [];

    public function __construct(
        /**
         * @var iterable<object>
         */
        private readonly iterable $valueProviders,
        private readonly ClassMetadataFactory $classMetadataFactory,
    ) {
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $object = $event->getObject();
        $objectManager = $event->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $className = $object::class;
        $doctrineClassMetadata = $objectManager->getClassMetadata($className);
        $classMetadata = $this->classMetadataCache[$className] ??= $this->classMetadataFactory->create($className);

        foreach ($classMetadata->persistProperties as $propertyMetadata) {
            $this->setField($unitOfWork, $doctrineClassMetadata, $object, $propertyMetadata);
        }
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $object = $event->getObject();
        $objectManager = $event->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $className = $object::class;
        $doctrineClassMetadata = $objectManager->getClassMetadata($className);
        $classMetadata = $this->classMetadataCache[$className] ??= $this->classMetadataFactory->create($className);

        foreach ($classMetadata->updateProperties as $propertyMetadata) {
            $this->setField($unitOfWork, $doctrineClassMetadata, $object, $propertyMetadata);
        }
    }

    private function getValueProvider(string $type): ValueProviderInterface
    {
        return $this->valueProvidersCache[$type] ?? $this->doGetValueProvider($type);
    }

    private function doGetValueProvider(string $type): ValueProviderInterface
    {
        foreach ($this->valueProviders as $valueProvider) {
            if (!($valueProvider instanceof ValueProviderInterface)) {
                throw new \RuntimeException(\sprintf('Value provider "%s" for type "%s" does not implement "%s".', $valueProvider::class, $type, ValueProviderInterface::class));
            }

            if ($valueProvider->supports($type)) {
                return $valueProvider;
            }
        }

        throw new \RuntimeException(\sprintf('No value provider found for type "%s".', $type));
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $doctrineClassMetadata = $event->getClassMetadata();
        $classMetadata = $this->classMetadataFactory->create($doctrineClassMetadata->getName());

        foreach ($classMetadata->persistProperties as $propertyName => $propertyMetadata) {
            if ($doctrineClassMetadata->hasField($propertyName) || $doctrineClassMetadata->hasAssociation($propertyName)) {
                continue;
            }

            $this->buildProperty(
                $event,
                $classMetadata,
                $propertyMetadata,
                'created',
            );
        }

        foreach ($classMetadata->updateProperties as $propertyName => $propertyMetadata) {
            if ($doctrineClassMetadata->hasField($propertyName) || $doctrineClassMetadata->hasAssociation($propertyName)) {
                continue;
            }

            $this->buildProperty(
                $event,
                $classMetadata,
                $propertyMetadata,
                'updated',
            );
        }
    }

    private function buildProperty(
        LoadClassMetadataEventArgs $event,
        ClassMetadata $classMetadata,
        PropertyMetadata $propertyMetadata,
        string $prefix,
    ): void {
        $entityManager = $event->getEntityManager();
        $doctrineClassMetadata = $event->getClassMetadata();

        if (UserInterface::class === $propertyMetadata->provider) {
            // Reference itself
            if (is_a($propertyMetadata->propertyType, $event->getClassMetadata()->getName(), true)) {
                $targetClassMetadata = $doctrineClassMetadata;
            } else {
                /**
                 * @phpstan-ignore-next-line
                 */
                $targetClassMetadata = $entityManager->getClassMetadata($propertyMetadata->propertyType);
            }

            $classMetadataBuilder = new ClassMetadataBuilder($doctrineClassMetadata);
            $associationBuilder = $classMetadataBuilder->createManyToOne($propertyMetadata->name, $propertyMetadata->propertyType)
                ->cascadePersist()
                ->cascadeDetach();

            foreach ($targetClassMetadata->getIdentifierFieldNames() as $identifierFieldName) {
                $associationBuilder->addJoinColumn(
                    \sprintf('%s_by_%s', $prefix, $identifierFieldName),
                    $identifierFieldName,
                    $propertyMetadata->nullable,
                );
            }

            $associationBuilder->build();

            return;
        }

        if (null === $propertyMetadata->doctrineType) {
            throw new \RuntimeException(\sprintf('Property "%s" in class "%s" does not have a doctrine type.', $propertyMetadata->name, $classMetadata->className));
        }

        $classMetadataBuilder = new ClassMetadataBuilder($doctrineClassMetadata);
        $classMetadataBuilder->createField($propertyMetadata->name, $propertyMetadata->doctrineType)
            ->nullable($propertyMetadata->nullable)
            ->build();
    }

    public function reset(): void
    {
        $this->classMetadataCache = [];
        $this->valueProvidersCache = [];
    }

    /**
     * @template T of object
     *
     * @phpstan-param DoctrineClassMetadata<T> $doctrineClassMetadata
     * @phpstan-param T $object
     */
    public function setField(
        UnitOfWork $unitOfWork,
        DoctrineClassMetadata $doctrineClassMetadata,
        object $object,
        PropertyMetadata $propertyMetadata,
    ): void {
        $currentValue = $doctrineClassMetadata->getFieldValue($object, $propertyMetadata->name);

        if (Mode::NULL === $propertyMetadata->mode && null !== $currentValue) {
            return;
        }

        $valueProvider = $this->getValueProvider($propertyMetadata->provider);
        $value = $valueProvider->provide($propertyMetadata->propertyType);

        $doctrineClassMetadata->setFieldValue($object, $propertyMetadata->name, $value);
        $unitOfWork->propertyChanged($object, $propertyMetadata->name, $currentValue, $value);
    }
}
