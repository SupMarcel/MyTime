<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Contributte\Translation\Translator;

abstract class SignUpFormFactory
{
    public const PASSWORD_MIN_LENGTH = 8;

    protected FormFactory $factory;
    protected Translator $translator;

    public function __construct(FormFactory $factory, Translator $translator)
    {
        $this->factory = $factory;
        $this->translator = $translator;
    }

    /**
     * Přidá základní pole do formuláře pro obě varianty (původní registrace / doplnění role).
     */
    protected function addCommonFields(Form $form, ?array $user, bool $isRoleOnly = false): void
    {
        $username = $user['username'] ?? '';
        $email = $user['email'] ?? '';
        $phone = $user['phone'] ?? '';
        bdump($username);
        $form->addText('username', $this->translator->translate('messages.signUpForm.username'))
            ->setDisabled($isRoleOnly)
            ->setDefaultValue($username)
            ->setRequired(!$isRoleOnly ? $this->translator->translate('messages.signUpForm.enter_username') : false);

        $form->addEmail('email', $this->translator->translate('messages.signUpForm.email'))
            ->setDisabled($isRoleOnly)    
            ->setDefaultValue($email)
            ->setRequired(!$isRoleOnly ? $this->translator->translate('messages.signUpForm.enter_email') : false);

        $form->addText('phone', $this->translator->translate('messages.signUpForm.phone'))
            ->setDefaultValue($phone)
            ->setRequired(!$isRoleOnly ? $this->translator->translate('messages.signUpForm.enter_phone') : false);
    }

    protected function addPasswordField(Form $form, bool $isRoleOnly = false): void
    {
        if (!$isRoleOnly) {
            $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
                ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), self::PASSWORD_MIN_LENGTH))
                ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
                ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);
        } else {
            $form->addPassword('currentPassword', $this->translator->translate('messages.signUpForm.confirm_password'))
                ->setRequired($this->translator->translate('messages.signUpForm.confirm_current_password'));
        }
    }

    abstract public function create(callable $onSuccess, ?array $user = null): Form;
}
