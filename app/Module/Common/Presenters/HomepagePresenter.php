<?php

declare(strict_types=1);

namespace App\Module\Common\Presenters;

use Nette\Application\UI\Form;
use Contributte\Translation\Translator;
use App\Model\RoleModel;
use App\Model\ChiefModel; // Importujeme ChiefModel

final class HomePagePresenter extends \Nette\Application\UI\Presenter
{
    private Translator $translator;
    private \App\Forms\SignInFormFactory $signInFormFactory;
    private RoleModel $roleModel;
    private ChiefModel $chiefModel;

    public function __construct(
        Translator $translator,
        \App\Forms\SignInFormFactory $signInFormFactory,
        RoleModel $roleModel,
        ChiefModel $chiefModel
    ) {
        parent::__construct();
        $this->translator = $translator;
        $this->signInFormFactory = $signInFormFactory;
        $this->roleModel = $roleModel;
        $this->chiefModel = $chiefModel;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->translator = $this->translator;
        $user = $this->getUser();

        if ($user->isLoggedIn()) {
            $userId = $user->getId();

            // Načtení všech rolí uživatele
            $userRoles = $this->roleModel->getUserRoles($userId);

            // Nastavení šablonových proměnných pro role
            $this->template->isClient = in_array(RoleModel::ROLE_CLIENT, $userRoles, true);
            $this->template->isWorker = in_array(RoleModel::ROLE_WORKER, $userRoles, true);
            $this->template->isChief = in_array(RoleModel::ROLE_CHIEF, $userRoles, true);
            $this->template->isAdmin = $this->roleModel->isRoleAssignedToAnyUser(RoleModel::ROLE_ADMINISTRATOR);
            $this->template->userName = $user->getIdentity()->username;

            // Pokud je uživatel šéf, načteme seznam jeho provozoven
            if ($this->template->isChief) {
                $this->template->chiefLocations = $this->chiefModel->getChiefLocations($userId);
            } else {
                $this->template->chiefLocations = [];
            }
        } else {
            // Výchozí hodnoty pro nepřihlášeného uživatele
            $this->template->isClient = false;
            $this->template->isWorker = false;
            $this->template->isChief = false;
            $this->template->isAdmin = false;
            $this->template->userName = null;
            $this->template->chiefLocations = [];
        }
    }

    protected function createComponentSignInForm(): Form
    {
        return $this->signInFormFactory->create(function () {
            $this->flashMessage($this->translator->translate('homepage.signInSuccessful'), 'success');
            $this->redirect('this'); // Reload the page after successful login
        });
    }
}


