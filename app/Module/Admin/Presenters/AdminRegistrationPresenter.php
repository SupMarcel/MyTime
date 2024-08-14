<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use Nette\Application\UI\Form;
use App\Common\Presenters\BaseRegistrationPresenter;
use App\Forms\AdminSignUpFormFactory;
use App\Model\UserFacade;
use Contributte\Translation\Translator;

final class AdminRegistrationPresenter extends BaseRegistrationPresenter
{
    private AdminSignUpFormFactory $adminSignUpFactory;

    public function __construct(
        AdminSignUpFormFactory $adminSignUpFactory,
        UserFacade $userFacade,
        Translator $translator
    ) {
        parent::__construct($translator, $userFacade);
        $this->adminSignUpFactory = $adminSignUpFactory;
    }

    protected function createComponentSignUpForm(): Form
    {
        $user = $this->getUserData();
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

    private function isAdministratorExists(): bool
    {
        // This check should be updated to use RoleModel or a similar mechanism if UserFacade does not handle roles anymore.
        return $this->userFacade->findOneBy(['role' => 'administrator']) !== null;
    }
}
