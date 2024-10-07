<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use Nette\Application\UI\Form;
use App\Common\Presenters\BaseRegistrationPresenter;
use App\Forms\AdminSignUpFormFactory;
use App\Model\UserFacade;
use App\Model\RoleModel;
use Contributte\Translation\Translator;

final class AdminRegistrationPresenter extends BaseRegistrationPresenter
{
    private AdminSignUpFormFactory $adminSignUpFactory;
    private RoleModel $roleModel;

    public function __construct(
        AdminSignUpFormFactory $adminSignUpFactory,
        UserFacade $userFacade,
        RoleModel $roleModel,
        Translator $translator
    ) {
        parent::__construct($translator, $userFacade);
        $this->adminSignUpFactory = $adminSignUpFactory;
        $this->roleModel = $roleModel;
    }

    protected function createComponentSignUpForm(): Form
    {
        // Získání pole uživatelských dat, pokud je uživatel přihlášen
        $user = $this->getUserData();
        
        // Vytvoření formuláře s předaným polem $user
        return $this->adminSignUpFactory->create(function () {
            $this->flashMessage('Registration successful.', 'success');
            $this->redirect(':Common:HomePage:');
        }, $user);
    }

    public function renderSignUp(): void
    {
        if ($this->isAdministratorExists()) {
            $this->flashMessage('An administrator is already registered.', 'error');
            $this->redirect(':Common:HomePage:');
        }
    }

    /**
     * Zkontroluje, zda již existuje administrátor.
     */
    private function isAdministratorExists(): bool
    {
        $adminRoleId = $this->roleModel->getRoleIdByName('administrator');

        if ($adminRoleId === null) {
            throw new \Exception('Administrator role not found in the roles table.');
        }

        return $this->roleModel->isRoleAssignedToAnyUser($adminRoleId);
    }
}
