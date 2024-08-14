<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class AdminModel extends BaseModel
{
    private UserModel $userModel;
    private RoleModel $roleModel;
    private LocationModel $locationModel;

    public function __construct(
        UserModel $userModel,
        RoleModel $roleModel,
        LocationModel $locationModel
    ) {
        $this->userModel = $userModel;
        $this->roleModel = $roleModel;
        $this->locationModel = $locationModel;
    }

    public function addAdmin(string $username, string $email, string $password, ?string $phone = null): void
    {
        // Zkontroluje, zda už existuje admin
        $existingAdmin = $this->roleModel->getUsersByRole(RoleModel::ROLE_ADMINISTRATOR);
        if ($existingAdmin->count() > 0) {
            throw new \Exception("An administrator already exists.");
        }

        // Přidá admina
        $userId = $this->userModel->addUser([
            UserModel::COLUMN_USERNAME => $username,
            UserModel::COLUMN_EMAIL => $email,
            UserModel::COLUMN_PASSWORD => password_hash($password, PASSWORD_DEFAULT),
            UserModel::COLUMN_PHONE => $phone,
        ])->getPrimary();

        $this->roleModel->addRoleToUser($userId, RoleModel::ROLE_ADMINISTRATOR);
    }

    public function removeAdmin(int $userId): void
    {
        // Zkontroluje, zda uživatel má roli admina
        if ($this->roleModel->userHasRole($userId, RoleModel::ROLE_ADMINISTRATOR)) {
            $this->roleModel->removeRoleFromUser($userId, RoleModel::ROLE_ADMINISTRATOR);
        }
    }

    public function banUser(int $userId): void
    {
        $this->userModel->banUser($userId);
    }

    public function unbanUser(int $userId): void
    {
        $this->userModel->unbanUser($userId);
    }

    public function banLocation(int $locationId): void
    {
        $this->locationModel->banLocation($locationId);
    }

    public function unbanLocation(int $locationId): void
    {
        $this->locationModel->unbanLocation($locationId);
    }
}
