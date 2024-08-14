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

    protected function createComponentSignUpForm(): Form
    {
        $user = $this->getUserData();
        return $this->clientSignUpFactory->createClientForm(function () {
            $this->flashMessage('Registration successful.', 'success');
            $this->redirect('Homepage:');
        }, $user);
    }

    protected function createComponentSignInForm(): Form
    {
        return $this->clientSignUpFactory->createSignInForm(function () {
            $this->flashMessage('Sign in successful.', 'success');
            $this->redirect('Homepage:');
        });
    }

    public function renderSignUp(): void
    {
        // Specifická logika pro registraci klienta
    }
}

