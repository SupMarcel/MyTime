<?php

declare(strict_types=1);

namespace App\Module\Client\Presenters;

use Nette\Application\UI\Form;
use App\Common\Presenters\BaseRegistrationPresenter;
use App\Forms\ClientSignUpFormFactory;
use App\Model\UserFacade;
use Contributte\Translation\Translator;

final class ClientRegistrationPresenter extends BaseRegistrationPresenter
{
    protected ClientSignUpFormFactory $clientSignUpFactory;

    public function __construct(
        ClientSignUpFormFactory $clientSignUpFactory,
        UserFacade $userFacade,
        Translator $translator
    ) {
        parent::__construct($translator, $userFacade);
        $this->clientSignUpFactory = $clientSignUpFactory;
    }

    /**
     * Vytvoří komponentu registračního formuláře pro klienta.
     */
    protected function createComponentSignUpForm(): Form
    {
        $user = $this->getUserData();
        return $this->chiefSignUpFactory->create(function () {
            $this->flashMessage('Registration successful.', 'success');
            $this->redirect(':Common:HomePage:');
        }, $user);
}


    /**
     * Vytvoří komponentu přihlašovacího formuláře.
     */
    protected function createComponentSignInForm(): Form
    {
        // Vytvoření přihlašovacího formuláře
        return $this->clientSignUpFactory->createSignInForm(function () {
            $this->flashMessage('Sign in successful.', 'success');
            $this->redirect('Homepage:');
        });
    }

    public function renderSignUp(): void
    {
        // Specifická logika pro registraci klienta (může obsahovat zobrazení detailů šablony nebo jiné ovládání)
    }
}
