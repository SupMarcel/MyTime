<?php

namespace App\Module\Common\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Security\User;

final class SignPresenter extends Presenter
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function actionOut(): void
    {
        $this->user->logout();
        $this->flashMessage('You have been signed out.', 'success');
        $this->redirect('HomePage:default');
    }
}

