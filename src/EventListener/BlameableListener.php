<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SoureCode\Bundle\DoctrineExtension\Contracts\BlameableInterface;
use SoureCode\Bundle\DoctrineExtension\Traits\BlameableTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\ResetInterface;

final class BlameableListener implements ResetInterface
{
    private ?UserInterface $user = null;

    public function __construct(
        private readonly Security $security,
        /**
         * @var class-string<UserInterface>
         */
        private readonly string $userClass,
    ) {
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $object = $event->getObject();
        $user = $this->getUser();

        if (null !== $user && $object instanceof BlameableInterface) {
            $object->setCreatedBy($user);
        }
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $object = $event->getObject();
        $user = $this->getUser();

        if (null !== $user && $object instanceof BlameableInterface) {
            $object->setUpdatedBy($user);
        }
    }

    private function getUser(): ?UserInterface
    {
        return $this->user ??= $this->security->getUser();
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $reflectionClass = $classMetadata->getReflectionClass();

        if (
            $reflectionClass->implementsInterface(BlameableInterface::class)
            && \in_array(BlameableTrait::class, $reflectionClass->getTraitNames(), true)
        ) {
            $classMetadataBuilder = new ClassMetadataBuilder($classMetadata);

            $classMetadataBuilder->createManyToOne('createdBy', $this->userClass)
                ->addJoinColumn('created_by', 'id', nullable: false)
                ->cascadeDetach()
                ->cascadePersist()
                ->build();

            $classMetadataBuilder->createManyToOne('updatedBy', $this->userClass)
                ->addJoinColumn('updated_by', 'id')
                ->cascadeDetach()
                ->cascadePersist()
                ->build();
        }
    }

    public function reset(): void
    {
        $this->user = null;
    }
}
