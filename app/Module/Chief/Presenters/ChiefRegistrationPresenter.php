<?php

declare(strict_types=1);

namespace App\Module\Chief\Presenters;

use Nette\Application\UI\Form;
use App\Common\Presenters\BaseRegistrationPresenter;
use App\Forms\SignUpFormFactory;
use App\Model\UserFacade;
use Contributte\Translation\Translator;
use Nette\Database\Table\ActiveRow;

final class ChiefRegistrationPresenter extends BaseRegistrationPresenter
{
    protected SignUpFormFactory $signUpFactory;

    public function __construct(
        SignUpFormFactory $signUpFactory,
        UserFacade $userFacade,
        Translator $translator
    ) {
        parent::__construct($translator, $userFacade);
        $this->signUpFactory = $signUpFactory;
    }

    protected function createComponentSignUpForm(): Form
    {
        $user = $this->getUserData();
        return $this->signUpFactory->createChiefForm(function () {
            $this->flashMessage('Registration successful.', 'success');
            $this->redirect('Homepage:');
        }, $user);
    }

    protected function createComponentSignInForm(): Form
    {
        return $this->signUpFactory->createSignInForm(function () {
            $this->flashMessage('Sign in successful.', 'success');
            $this->redirect('Homepage:');
        });
    }

    public function renderSignUp(): void
    {
        // Specifická logika pro registraci šéfa provozovny
    }
}
