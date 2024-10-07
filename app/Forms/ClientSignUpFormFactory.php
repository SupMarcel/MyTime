<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\ClientModel;
use App\Model\RoleModel;
use Contributte\Translation\Translator;

class ClientSignUpFormFactory extends SignUpFormFactory
{
    private ClientModel $clientModel;
    private RoleModel $roleModel;

    public function __construct(FormFactory $factory, Translator $translator, ClientModel $clientModel, RoleModel $roleModel)
    {
        parent::__construct($factory, $translator);
        $this->clientModel = $clientModel;
        $this->roleModel = $roleModel;
    }

    public function create(callable $onSuccess, ?array $user = null): Form
    {
        $form = $this->factory->create();
        $form->addGroup($this->translator->translate('messages.signUpForm.clientDescription'));

        // Přidání základních polí pro obě varianty (nová registrace / doplnění role)
        $this->addCommonFields($form, $user, !empty($user));

        // Přidání pole pro heslo (zobrazí se jen při plné registraci, jinak se zobrazí pole pro aktuální heslo)
        $this->addPasswordField($form, !empty($user));

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $user): void {
            if (!empty($user)) {
                // Ověření aktuálního hesla pro přidání nové role
                $existingUser = $this->clientModel->getUserById($user['id']);
                if ($existingUser && !password_verify($data->currentPassword, $existingUser->password)) {
                    $form->addError($this->translator->translate('messages.signUpForm.incorrect_password'));
                    return;
                }
                // Pouze přidání role "client"
                $this->clientModel->addRoleToUser($user['id'], RoleModel::ROLE_CLIENT);
            } else {
                // Přidání nového klienta
                $this->clientModel->addClient($data->username, $data->email, $data->password, $data->phone);
            }
            $onSuccess();
        };

        return $form;
    }
}
