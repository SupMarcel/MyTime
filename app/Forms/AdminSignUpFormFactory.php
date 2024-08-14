<?php

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\AdminModel;
use Contributte\Translation\Translator; // Přidání správného use příkazu

class AdminSignUpFormFactory extends SignUpFormFactory
{
    private AdminModel $adminModel;

    public function __construct(FormFactory $factory, Translator $translator, AdminModel $adminModel)
    {
        parent::__construct($factory, $translator);
        $this->adminModel = $adminModel;
    }

    public function create(callable $onSuccess, ?array $existingUser = null): Form
    {
        $form = $this->factory->create();
        $form->addGroup($this->translator->translate('messages.signUpForm.adminDescription'));

        $this->addCommonFields($form, $existingUser);

        $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
            ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), AdminModel::PASSWORD_MIN_LENGTH))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
            ->addRule($form::MIN_LENGTH, null, AdminModel::PASSWORD_MIN_LENGTH);

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            if (!$existingUser) {
                // Přidání nového administrátora
                $this->adminModel->addAdmin($data->username, $data->email, $data->password, $data->phone);
            }
            $onSuccess();
        };

        return $form;
    }
}
