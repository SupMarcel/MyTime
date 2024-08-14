<?php

namespace App\Module\Common\Security;

use Nette\Security\Authenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use Nette\Security\AuthenticationException;
use Nette\Database\Explorer;

class SimpleAuthenticator implements Authenticator
{
    private Explorer $database;
    private Passwords $passwords;

    public function __construct(Explorer $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(string $email, string $password): Identity
    {
        $userRow = $this->database->table('users')
            ->where('email', $email)
            ->fetch();

        if (!$userRow) {
            throw new AuthenticationException('The email is incorrect.');
        }

        if (!$this->passwords->verify($password, $userRow->password)) {
            throw new AuthenticationException('The password is incorrect.');
        }

        if ($this->passwords->needsRehash($userRow->password)) {
            $userRow->update([
                'password' => $this->passwords->hash($password),
            ]);
        }

        return new SimpleIdentity(
            $userRow->id,
            $this->getUserRoles($userRow->id),
            ['username' => $userRow->username, 'email' => $userRow->email]
        );
    }

    private function getUserRoles(int $userId): array
    {
        return $this->database->table('user_roles')
            ->where('user_id', $userId)
            ->fetchPairs(null, 'role_id');
    }
}

