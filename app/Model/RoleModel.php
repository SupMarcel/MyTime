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

    /**
     * Získá ID role podle jejího názvu.
     *
     * @param string $roleName Název role.
     * @return int|null ID role nebo null, pokud neexistuje.
     */
    public function getRoleIdByName(string $roleName): ?int
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ROLE_NAME, $roleName)
            ->fetchField(self::COLUMN_ROLE_ID);
    }

    /**
     * Přidá roli k uživateli.
     *
     * @param int $userId ID uživatele.
     * @param int $roleId ID role.
     */
    public function addRoleToUser(int $userId, int $roleId): void
    {
        $this->database->table(self::TABLE_USER_ROLES)->insert([
            self::COLUMN_USER_ID => $userId,
            self::COLUMN_ROLE_ID_IN_USER_ROLES => $roleId,
        ]);
    }

    /**
     * Zjistí, zda uživatel má danou roli.
     *
     * @param int $userId ID uživatele.
     * @param int $roleId ID role.
     * @return bool True, pokud uživatel má danou roli.
     */
    public function userHasRole(int $userId, int $roleId): bool
    {
        return $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_ROLE_ID_IN_USER_ROLES, $roleId)
            ->count('*') > 0;
    }

    /**
     * Odebere roli uživateli.
     *
     * @param int $userId ID uživatele.
     * @param int $roleId ID role.
     */
    public function removeRoleFromUser(int $userId, int $roleId): void
    {
        $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->where(self::COLUMN_ROLE_ID_IN_USER_ROLES, $roleId)
            ->delete();
    }

    /**
     * Odebere všechny role uživateli.
     *
     * @param int $userId ID uživatele.
     */
    public function removeAllRolesFromUser(int $userId): void
    {
        $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->delete();
    }

    /**
     * Získá seznam ID rolí přiřazených uživateli.
     *
     * @param int $userId ID uživatele.
     * @return array Pole ID rolí.
     */
    public function getUserRoles(int $userId): array
    {
        return $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_USER_ID, $userId)
            ->fetchPairs(self::COLUMN_ROLE_ID_IN_USER_ROLES, self::COLUMN_ROLE_ID_IN_USER_ROLES);
    }

    /**
     * Zjistí, zda je role přiřazena alespoň jednomu uživateli.
     *
     * @param int $roleId ID role.
     * @return bool True, pokud existuje alespoň jeden uživatel s touto rolí.
     */
    public function isRoleAssignedToAnyUser(int $roleId): bool
    {
        return $this->database->table(self::TABLE_USER_ROLES)
            ->where(self::COLUMN_ROLE_ID_IN_USER_ROLES, $roleId)
            ->count('*') > 0;
    }
    


}
