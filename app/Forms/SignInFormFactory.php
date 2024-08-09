<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;

final class SignInFormFactory
{
    use Nette\SmartObject;

    private FormFactory $factory;
    private User $user;

    public function __construct(FormFactory $factory, User $user)
    {
        $this->factory = $factory;
        $this->user = $user;
    }

    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        $form->addText('email', 'signInForm.email')
            ->setRequired('Please enter your email.')
            ->addRule($form::EMAIL, 'Please enter a valid email address.');

        $form->addPassword('password', 'signInForm.password')
            ->setRequired('Please enter your password.');

        $form->addCheckbox('remember', 'signInForm.remember');

        $form->addSubmit('send', 'signInForm.signIn');

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {
            try {
                $this->user->setExpiration($data->remember ? '14 days' : '20 minutes');
                $this->user->login($data->email, $data->password);
                $onSuccess();
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError('signInForm.incorrect');
            }
        };

        return $form;
    }
}
