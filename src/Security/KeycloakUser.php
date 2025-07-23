<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class KeycloakUser implements UserInterface
{
    private string $id;
    private string $username;
    private array $roles;

    public function __construct(string $id, string $username, array $roles = ['ROLE_USER'])
    {
        $this->id = $id;
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void {}

    public function getPassword(): ?string
    {
        return null; // not used
    }

    public function getSalt(): ?string
    {
        return null; // not used
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
