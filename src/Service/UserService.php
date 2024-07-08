<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserService
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getAuthenticatedUser(): User
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedException('You must be logged in to access this resource.');
        }

        if (!$user instanceof User) {
            throw new \LogicException('The user is not an instance of the expected User class.');
        }

        return $user;
    }
}
