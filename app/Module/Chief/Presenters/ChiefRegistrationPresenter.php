<?php

declare(strict_types=1);

namespace App\Module\Chief\Presenters;

use Nette\Application\UI\Form;
use App\Common\Presenters\BaseRegistrationPresenter;
use App\Forms\ChiefSignUpFormFactory; // Změněno z SignUpFormFactory na ChiefSignUpFormFactory
use App\Model\UserFacade;
use Contributte\Translation\Translator;

final class ChiefRegistrationPresenter extends BaseRegistrationPresenter
{
    protected ChiefSignUpFormFactory $chiefSignUpFactory;

    public function __construct(
        ChiefSignUpFormFactory $chiefSignUpFactory, // Použijeme správnou továrnu pro šéfy
        UserFacade $userFacade,
        Translator $translator
    ) {
        parent::__construct($translator, $userFacade);
        $this->chiefSignUpFactory = $chiefSignUpFactory;
    }

    protected function createComponentSignUpForm(): Form
    {
        $user = $this->getUserData();
        return $this->chiefSignUpFactory->create(function () {
            $this->flashMessage('Registration successful.', 'success');
            $this->redirect(':Common:HomePage:default');
        }, $user);
    }

    

    public function renderSignUp(): void
    {
        // Specifická logika pro registraci šéfa provozovny
    }
}
