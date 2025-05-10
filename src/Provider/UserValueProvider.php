<?php

namespace SoureCode\Bundle\DoctrineExtension\Provider;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @implements ValueProviderInterface<UserInterface>
 */
final class UserValueProvider implements ValueProviderInterface, ResetInterface
{
    private ?UserInterface $cache = null;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function provide(string $type): ?UserInterface
    {
        return $this->cache ??= $this->tokenStorage->getToken()?->getUser();
    }

    public function supports(string $type): bool
    {
        return match ($type) {
            UserInterface::class => true,
            default => false,
        };
    }

    public function reset(): void
    {
        $this->cache = null;
    }
}
