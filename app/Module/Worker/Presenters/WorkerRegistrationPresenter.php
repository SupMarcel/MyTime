<?php

declare(strict_types=1);

namespace App\Module\Worker\Presenters;

use Nette\Application\UI\Form;
use App\Common\Presenters\BaseRegistrationPresenter;
use App\Forms\WorkerSignUpFormFactory; // Změněno z SignUpFormFactory na WorkerSignUpFormFactory
use App\Model\UserFacade;
use Contributte\Translation\Translator;

final class WorkerRegistrationPresenter extends BaseRegistrationPresenter
{
    protected WorkerSignUpFormFactory $workerSignUpFactory;

    public function __construct(
        WorkerSignUpFormFactory $workerSignUpFactory, // Použijeme správnou továrnu pro pracovníky
        UserFacade $userFacade,
        Translator $translator
    ) {
        parent::__construct($translator, $userFacade);
        $this->workerSignUpFactory = $workerSignUpFactory;
    }

    protected function createComponentSignUpForm(): Form
    {
        $user = $this->getUserData();
        return $this->workerSignUpFactory->create(function () {
            $this->flashMessage('Registration successful.', 'success');
            $this->redirect('Homepage:');
        }, $user);
    }

    

    public function renderSignUp(): void
    {
        // Specifická logika pro registraci pracovníka
    }
}
