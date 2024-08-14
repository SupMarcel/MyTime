<?php

namespace App\Model;

class ClientModel
{
    private UserModel $userModel;
    private RoleModel $roleModel;

    public function __construct(UserModel $userModel, RoleModel $roleModel)
    {
        $this->userModel = $userModel;
        $this->roleModel = $roleModel;
    }

    public function addClient(string $username, string $email, string $password, string $phone): void
    {
        $userId = $this->userModel->addUser([
            UserModel::COLUMN_USERNAME => $username,
            UserModel::COLUMN_EMAIL => $email,
            UserModel::COLUMN_PASSWORD => password_hash($password, PASSWORD_DEFAULT),
            UserModel::COLUMN_PHONE => $phone, // Přidání telefonního čísla
        ])->getPrimary();

        $this->roleModel->addRoleToUser($userId, RoleModel::ROLE_CLIENT);
    }
}
