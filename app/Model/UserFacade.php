<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use App\Model\UserModel;
use App\Model\RoleModel;
use App\Model\LocationModel;

class UserFacade
{
    private UserModel $userModel;
    private RoleModel $roleModel;
    private LocationModel $locationModel;

    public function __construct(UserModel $userModel, RoleModel $roleModel, LocationModel $locationModel)
    {
        $this->userModel = $userModel;
        $this->roleModel = $roleModel;
        $this->locationModel = $locationModel;
    }

    public function addUser(string $username, string $email, string $password, string $role, ?string $phone = null): int
    {
        $userId = $this->userModel->addUser([
            UserModel::COLUMN_USERNAME => $username,
            UserModel::COLUMN_EMAIL => $email,
            UserModel::COLUMN_PASSWORD => $this->passwords->hash($password),
            UserModel::COLUMN_PHONE => $phone,
        ])->getPrimary();

        $this->roleModel->addRoleToUser($userId, $this->roleModel::getRoleIdByName($role));

        return $userId;
    }

    public function editUser(int $userId, array $data): bool
    {
        return $this->userModel->updateUser($userId, $data);
    }

    public function changePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = $this->passwords->hash($newPassword);
        return $this->userModel->updateUser($userId, [UserModel::COLUMN_PASSWORD => $hashedPassword]);
    }

    public function deleteUser(int $userId): void
    {
        $this->roleModel->removeAllRolesFromUser($userId);
        $this->locationModel->removeWorkerFromAllLocations($userId);
        $this->locationModel->removeChiefFromAllLocations($userId);
        $this->userModel->deleteUser($userId);
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

    public function getUserDataByEmail(string $email): ?array
    {
        $user = $this->userModel->findByEmail($email);
        return $user ? $user->toArray() : null;
    }

    public function getUserDataByPhone(string $phone): ?array
    {
        $user = $this->userModel->findByPhone($phone);
        return $user ? $user->toArray() : null;
    }

        public function getUserBasicInfo(int $userId): ?array
    {
        $user = $this->userModel->getById($userId);

        if ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'image' => $user->image,
                'password' => $user->password
            ];
        }

        return null;
    }
}
