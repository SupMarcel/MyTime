<?php

namespace App\Model;

use Nette\Database\Table\Selection;

class RoleModel extends BaseModel
{
    // Konstanty pro názvy tabulek
    public const TABLE_NAME = 'roles';
    public const TABLE_USER_ROLES = 'user_roles';

    // Konstanty pro názvy sloupců v tabulce 'roles'
    public const COLUMN_ROLE_ID = 'id';
    public const COLUMN_ROLE_NAME = 'name';

    // Konstanty pro názvy sloupců v tabulce 'user_roles'
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_ROLE_ID_IN_USER_ROLES = 'role_id';

    // Konstanty pro jednotlivé role
    public const ROLE_CLIENT = 1;
    public const ROLE_WORKER = 2;
    public const ROLE_CHIEF = 3;
    public const ROLE_ADMINISTRATOR = 4;

    public function getRoleIdByName(string $roleName): ?int
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ROLE_NAME, $roleName)
            ->fetchField(self::COLUMN_ROLE_ID);
    }

    public function addRoleToUser(int $userId, int $roleId): void
    {
        $this->database->table(self::TABLE_USER_ROLES)->insert([
            self::COLUMN_USER_ID => $userId,
            self::COLUMN_ROLE_ID_IN_USER_ROLES => $roleId,
        ]);
    }

    public function userHasRole(int $userId, int $roleId): bool
    {
        return $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_ROLE_ID_IN_USER_ROLES, $roleId)
            ->count('*') > 0;
    }

    public function removeRoleFromUser(int $userId, int $roleId): void
    {
        $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_ROLE_ID_IN_USER_ROLES, $roleId)
            ->delete();
    }

    public function removeAllRolesFromUser(int $userId): void
    {
        $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->delete();
    }

    public function getUserRoles(int $userId): array
    {
        return $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->fetchPairs(self::COLUMN_ROLE_ID_IN_USER_ROLES, self::COLUMN_ROLE_ID_IN_USER_ROLES);
    }
}
