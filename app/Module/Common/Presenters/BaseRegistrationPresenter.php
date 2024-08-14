<?php

declare(strict_types=1);

namespace App\Common\Presenters;

use Nette;
use App\Model\UserFacade;
use Contributte\Translation\Translator;


abstract class BaseRegistrationPresenter extends Nette\Application\UI\Presenter
{
    protected Translator $translator;
    protected UserFacade $userFacade;

    public function __construct(Translator $translator, UserFacade $userFacade)
    {
        parent::__construct();
        $this->translator = $translator;
        $this->userFacade = $userFacade;
    }

    
        protected function getUserData(): ?array
    {
        if ($this->getUser()->isLoggedIn()) {
            $userId = $this->getUser()->getId(); // Získání ID přihlášeného uživatele
            return $this->userFacade->getUserBasicInfo($userId); // Použití funkce z UserFacade pro získání základních informací
        }
        return null;
    }

    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->translator = $this->translator;
    }
}
