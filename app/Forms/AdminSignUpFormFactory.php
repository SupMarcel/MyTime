<?php

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\AdminModel;
use App\Model\RoleModel;
use Contributte\Translation\Translator;

class AdminSignUpFormFactory extends SignUpFormFactory
{
    private AdminModel $adminModel;
    private RoleModel $roleModel;

    public function __construct(FormFactory $factory, Translator $translator, AdminModel $adminModel, RoleModel $roleModel)
    {
        parent::__construct($factory, $translator);
        $this->adminModel = $adminModel;
        $this->roleModel = $roleModel;
    }

    public function create(callable $onSuccess, ?array $user = null): Form
    {       
        $form = $this->factory->create();
        $form->addGroup($this->translator->translate('messages.signUpForm.adminDescription'));

        $this->addCommonFields($form, $user, $user !== null);

        $this->addPasswordField($form, $user !== null);

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $user): void {
            if ($user !== null) {
                if ($user && !password_verify($data->currentPassword, $user['password'])) {
                    $form->addError($this->translator->translate('messages.signUpForm.incorrect_password'));
                    return;
                }
                $this->roleModel->addRoleToUser($user['id'], RoleModel::ROLE_ADMINISTRATOR );
            } else {
                $this->adminModel->addAdmin($data->username, $data->email, $data->password, $data->phone);
            }
            $onSuccess();
        };

        return $form;
    }
}
