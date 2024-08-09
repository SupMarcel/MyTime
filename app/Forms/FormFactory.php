<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Contributte\Translation\Translator;

final class FormFactory
{
    use Nette\SmartObject;

    private Nette\Security\User $user;
    private Translator $translator;

    public function __construct(Nette\Security\User $user, Translator $translator)
    {
        $this->user = $user;
        $this->translator = $translator;
    }

    public function create(): Form
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        if ($this->user->isLoggedIn()) {
            $form->addProtection('messages.general.protection');
        }
        return $form;
    }
}
