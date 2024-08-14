<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Contributte\Translation\Translator;

final class SignInFormFactory
{
    use Nette\SmartObject;

    private FormFactory $factory;
    private User $user;
    private Translator $translator;

    public function __construct(FormFactory $factory, User $user, Translator $translator)
    {
        $this->factory = $factory;
        $this->user = $user;
        $this->translator = $translator;
    }

    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        
        $form->addText('email', $this->translator->translate('messages.signInForm.email'))
            ->setRequired($this->translator->translate('messages.signInForm.enter_email'))
            ->addRule($form::EMAIL, $this->translator->translate('messages.signInForm.valid_email'));

        $form->addPassword('password', $this->translator->translate('messages.signInForm.password'))
            ->setRequired($this->translator->translate('messages.signInForm.enter_password'));

        $form->addCheckbox('remember', $this->translator->translate('messages.signInForm.remember'));

        $form->addSubmit('send', $this->translator->translate('messages.signInForm.signIn'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {
            try {
                $this->user->setExpiration($data->remember ? '14 days' : '20 minutes');
                $this->user->login($data->email, $data->password);
                $onSuccess();
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError($this->translator->translate('messages.signInForm.incorrect'));
            }
        };

        return $form;
    }
}


