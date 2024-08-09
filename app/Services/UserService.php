<?php

namespace App\Services;

use App\Model\UserModel;
use App\Model\LanguageModel;
use App\Model\AddressModel;
use Nette\Security\Passwords;
use Nette\Database\Table\ActiveRow;

class UserService
{
    private UserModel $userModel;
    private LanguageModel $languageModel;
    private AddressModel $addressModel;
    private Passwords $passwords;

    public function __construct(
        UserModel $userModel,
        LanguageModel $languageModel,
        AddressModel $addressModel,
        Passwords $passwords
    ) {
        $this->userModel = $userModel;
        $this->languageModel = $languageModel;
        $this->addressModel = $addressModel;
        $this->passwords = $passwords;
    }

    public function registerUser(array $data, string $role): ActiveRow
    {
        $data['password'] = $this->passwords->hash($data['password']);
        $user = $this->userModel->addUser($data);
        $this->userModel->addRole($user->id, $role);

        return $user;
    }

    public function addRoleToUser(int $userId, string $role): void
    {
        $this->userModel->addRole($userId, $role);
    }

    public function removeRoleFromUser(int $userId, string $role): void
    {
        $this->userModel->removeRole($userId, $role);
    }

    public function approveUser(int $userId): bool
    {
        return $this->userModel->approveUser($userId);
    }

    public function banUser(int $userId): bool
    {
        return $this->userModel->banUser($userId);
    }

    public function unbanUser(int $userId): bool
    {
        return $this->userModel->unbanUser($userId);
    }
}
