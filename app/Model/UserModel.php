<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class UserModel extends BaseModel
{
    const TABLE_NAME = 'users';
    const COLUMN_ID = 'id';
    const COLUMN_USERNAME = 'username';
    const COLUMN_EMAIL = 'email';
    const COLUMN_PASSWORD = 'password';
    const COLUMN_LOCATION_ID = 'location_id';
    const COLUMN_APPROVED = 'approved';
    const COLUMN_BANNED = 'banned';
    const COLUMN_IMAGE = 'image';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';
    const COLUMN_LANGUAGE_ID = 'languageId';

    public function addUser(array $data): ActiveRow
    {
        return $this->database->table(self::TABLE_NAME)->insert($data);
    }

    public function findBy(array $criteria): ?ActiveRow
    {
        return $this->database->table(self::TABLE_NAME)->where($criteria)->fetch();
    }

    public function updateUser(int $id, array $data): bool
    {
        return (bool) $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update($data);
    }

    public function deleteUser(int $id): bool
    {
        return (bool) $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
    }

    public function approveUser(int $id): bool
    {
        return $this->updateUser($id, [self::COLUMN_APPROVED => 1]);
    }

    public function banUser(int $id): bool
    {
        return $this->updateUser($id, [self::COLUMN_BANNED => 1]);
    }

    public function unbanUser(int $id): bool
    {
        return $this->updateUser($id, [self::COLUMN_BANNED => 0]);
    }

    public function getUsersByRole(string $role): Selection
    {
        return $this->database->table('user_roles')
            ->where('role', $role)
            ->fetchAll();
    }

    public function getUserRoles(int $userId): array
    {
        return $this->database->table('user_roles')
            ->where('user_id', $userId)
            ->fetchPairs('role_id', 'role_id');
    }

    public function addRole(int $userId, string $roleName): void
    {
        $role = $this->database->table('roles')->where('name', $roleName)->fetch();
        if ($role) {
            $this->database->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $role->id,
            ]);
        }
    }

    public function removeRole(int $userId, string $roleName): void
    {
        $role = $this->database->table('roles')->where('name', $roleName)->fetch();
        if ($role) {
            $this->database->table('user_roles')
                ->where('user_id', $userId)
                ->where('role_id', $role->id)
                ->delete();
        }
    }
    
    public function getUserData(int $userId): array
    {
        $user = $this->database->table(self::TABLE_NAME)->get($userId);
        return $user ? $user->toArray() : [];
    }
}
