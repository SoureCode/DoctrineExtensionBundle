<?php

namespace SoureCode\Bundle\DoctrineExtension\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use SoureCode\Bundle\DoctrineExtension\Contracts\BlameableInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\ResetInterface;

final class BlameableListener implements ResetInterface
{
    private ?UserInterface $user = null;
    private bool $isNull = false;

    public function __construct(
        private readonly Security $security,
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
        if ($this->isNull) {
            return null;
        }

        if (null === $this->user) {
            $this->user = $this->security->getUser();
        }

        if (null === $this->user) {
            $this->isNull = true;

            return null;
        }

        if ($this->user instanceof UserInterface) {
            return $this->user;
        }

        $this->isNull = true;

        return null;
    }

    public function reset(): void
    {
        $this->user = null;
        $this->isNull = false;
    }
}
