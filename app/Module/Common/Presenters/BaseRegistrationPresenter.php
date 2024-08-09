<?php

declare(strict_types=1);

namespace App\Common\Presenters;

use Nette;
use App\Model\UserFacade;
use Contributte\Translation\Translator;
use Nette\Database\Table\ActiveRow;

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

    protected function getUserData(): ?ActiveRow
    {
        if ($this->getUser()->isLoggedIn()) {
            return $this->userFacade->findBy(['email' => $this->getUser()->getIdentity()->email]);
        }
        return null;
    }

    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->translator = $this->translator;
    }
}
