<?php

declare(strict_types=1);

namespace App\Module\Common\Presenters;

use Nette\Application\UI\Form;
use Contributte\Translation\Translator;

final class HomePagePresenter extends \Nette\Application\UI\Presenter
{
    private Translator $translator;
    private \App\Forms\SignInFormFactory $signInFormFactory;

    public function __construct(
        Translator $translator,
        \App\Forms\SignInFormFactory $signInFormFactory
    ) {
        $this->translator = $translator;
        $this->signInFormFactory = $signInFormFactory;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->translator = $this->translator;
        $user = $this->getUser();

        $this->template->isClient = $user->isLoggedIn() && $user->isInRole('client');
        $this->template->isWorker = $user->isLoggedIn() && $user->isInRole('worker');
        $this->template->isChief = $user->isLoggedIn() && $user->isInRole('chief');
        $this->template->isAdmin = $user->isLoggedIn() && $user->isInRole('administrator');
        $this->template->userName = $user->isLoggedIn() ? $user->getIdentity()->username : null;
    }

    protected function createComponentSignInForm(): Form
    {
        return $this->signInFormFactory->create(function () {
            $this->flashMessage('Sign in successful.', 'success');
            $this->redirect('this'); // reload the page after successful login
        });
    }
}

