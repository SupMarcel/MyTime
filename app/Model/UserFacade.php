<?php

namespace App\Model;

use Nette;
use Nette\Database\Table\Selection;
use Nette\Security\Passwords;

class UserFacade
{
    use Nette\SmartObject;

    private $database;
    private $passwords;
    
    public const PasswordMinLength = 8;

    public function __construct(Nette\Database\Explorer $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

        public function add(string $username, string $email, string $password, string $role): void
    {
        $userId = $this->database->table('users')->insert([
            'username' => $username,
            'email' => $email,
            'password' => $this->passwords->hash($password),
        ])->getPrimary();

        // Přiřazení role k uživateli v tabulce user_roles
        $this->database->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $this->getRoleId($role),
        ]);
    }
    
    private function getRoleId(string $role): int
    {
        return $this->database->table('roles')->where('name', $role)->fetchField('id');
    }
    
        public function addWorker(string $username, string $email, string $password, string $image, string $description, ?int $locationId, string $role): void
    {
        $userId = $this->database->table('users')->insert([
            'username' => $username,
            'email' => $email,
            'password' => $this->passwords->hash($password),
            'image' => $image,
            'description' => $description,
        ])->getPrimary();

        if ($locationId !== null) {
            $this->database->table('worker_locations')->insert([
                'worker_id' => $userId,
                'location_id' => $locationId,
            ]);
        }

        // Přiřazení role k uživateli
        $this->database->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $this->getRoleId($role), // Dynamické přiřazení role
        ]);
    }

    public function createLocation(string $name, string $description): int
    {
        return $this->database->table('locations')->insert([
            'name' => $name,
            'description' => $description,
        ])->getPrimary();
    }

    public function findOneBy(array $criteria): ?Nette\Database\Table\ActiveRow
    {
        return $this->database->table('users')->where($criteria)->fetch();
    }
}
