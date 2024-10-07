<?php

declare(strict_types=1);

namespace App\Module\Chief\Presenters;

use Nette\Application\UI\Form;
use App\Common\Presenters\BaseRegistrationPresenter;
use App\Forms\ChiefSignUpFormFactory;
use App\Forms\AddLocationFormFactory;
use App\Model\ChiefModel;
use Contributte\Translation\Translator;

final class ChiefRegistrationPresenter extends BaseRegistrationPresenter
{
    protected ChiefSignUpFormFactory $chiefSignUpFactory;
    protected AddLocationFormFactory $addLocationFormFactory;
    protected ChiefModel $chiefModel;

    public function __construct(
        ChiefSignUpFormFactory $chiefSignUpFactory,
        AddLocationFormFactory $addLocationFormFactory,
        ChiefModel $chiefModel,
        Translator $translator
    ) {
        parent::__construct($translator);
        $this->chiefSignUpFactory = $chiefSignUpFactory;
        $this->addLocationFormFactory = $addLocationFormFactory;
        $this->chiefModel = $chiefModel;
    }

    protected function createComponentSignUpForm(): Form
    {
        $user = $this->getUserData();
        return $this->chiefSignUpFactory->create(function () {
            $this->flashMessage($this->translator->translate('messages.flashMessages.registrationSuccess'), 'success');
            $this->redirect(':Common:HomePage:');
        }, $user);
    }

    protected function createComponentAddLocationForm(): Form
    {
        $user = $this->getUserData();
        return $this->addLocationFormFactory->create(function () {
            $this->flashMessage($this->translator->translate('messages.flashMessages.locationAddedSuccess'), 'success');
            $this->redirect('this');
        }, $user);
    }

    public function renderSignUp(): void
    {
        // Logika pro registraci šéfa
    }

    public function renderAddLocation(): void
    {
        $this->template->existingLocations = $this->getUserLocations();
    }

    private function getUserLocations(): array
    {
        $user = $this->getUserData();
        if ($user) {
            return $this->chiefModel->getChiefLocations($user['id']);
        }
        return [];
    }
}
