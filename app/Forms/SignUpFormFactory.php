<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Contributte\Translation\Translator;

abstract class SignUpFormFactory
{
    protected FormFactory $factory;
    protected Translator $translator;

    public function __construct(FormFactory $factory, Translator $translator)
    {
        $this->factory = $factory;
        $this->translator = $translator;
    }

    protected function addCommonFields(Form $form, ?array $existingUser): void
    {
        $form->addText('username', $this->translator->translate('messages.signUpForm.username'))
            ->setDisabled(true)
            ->setDefaultValue(!empty($existingUser) ? $existingUser['username'] : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_username'));

        $form->addEmail('email', $this->translator->translate('messages.signUpForm.email'))
            ->setDisabled(true)
            ->setDefaultValue(!empty($existingUser) ? $existingUser['email'] : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_email'));

        $form->addText('phone', $this->translator->translate('messages.signUpForm.phone'))
            ->setDisabled(true)
            ->setDefaultValue(!empty($existingUser) ? $existingUser['phone'] : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_phone'));
    }

    abstract public function create(callable $onSuccess, ?array $existingUser = null): Form;
}

