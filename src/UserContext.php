<?php

namespace DualMedia\EsLogBundle;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserContext
{
    private UserInterface|null $user = null;

    public function __construct(
        private readonly TokenStorageInterface|null $tokenStorage
    ) {
    }

    public function setUser(
        UserInterface|null $user
    ): void {
        $this->user = $user;
    }

    public function getUser(): UserInterface|null
    {
        return $this->user ?? $this->tokenStorage?->getToken()?->getUser();
    }
}
