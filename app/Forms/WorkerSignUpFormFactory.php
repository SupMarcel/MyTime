<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\WorkerModel;
use App\Model\RoleModel;
use Contributte\Translation\Translator;

class WorkerSignUpFormFactory extends SignUpFormFactory
{
    private WorkerModel $workerModel;
    private RoleModel $roleModel;

    public function __construct(FormFactory $factory, Translator $translator, WorkerModel $workerModel, RoleModel $roleModel)
    {
        parent::__construct($factory, $translator);
        $this->workerModel = $workerModel;
        $this->roleModel = $roleModel;
    }

    /**
     * Vytvoření formuláře pro registraci pracovníka nebo přidání role.
     *
     * @param callable $onSuccess Callback pro úspěšné dokončení registrace
     * @param array|null $user Data přihlášeného uživatele, pokud se jedná o přidání role (jinak null)
     * @return Form Vrací formulář pro registraci nebo doplnění role
     */
    public function create(callable $onSuccess, ?array $user = null): Form
    {
        $form = $this->factory->create();
        $form->addGroup($this->translator->translate('messages.signUpForm.workerDescription'));

        // Přidání základních polí pro obě varianty (nová registrace / doplnění role)
        $this->addCommonFields($form, $user, !empty($user));

        // Přidání pole pro heslo (zobrazí se jen při plné registraci, jinak se zobrazí pole pro aktuální heslo)
        $this->addPasswordField($form, !empty($user));

        // Přidání polí pro image a description, pokud nejde pouze o doplnění role
        if (empty($user)) {
            $form->addText('image', $this->translator->translate('messages.signUpForm.image'))
                ->setRequired(false);

            $form->addTextArea('description', $this->translator->translate('messages.signUpForm.description'))
                ->setRequired(false);
        }

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $user): void {
            if (!empty($user)) {
                // Ověření aktuálního hesla pro přidání nové role
                $existingUser = $this->workerModel->getUserById($user['id']);
                if ($existingUser && !password_verify($data->currentPassword, $existingUser->password)) {
                    $form->addError($this->translator->translate('messages.signUpForm.incorrect_password'));
                    return;
                }
                // Pouze přidání role "worker" s použitím konstanty
                $this->roleModel->addRoleToUser($user['id'], RoleModel::ROLE_WORKER);
            } else {
                // Přidání nového pracovníka
                $this->workerModel->addWorker(
                    $data->username,
                    $data->email,
                    $data->password,
                    $data->image,
                    $data->description,
                    $data->phone,
                    null
                );
            }
            $onSuccess();
        };

        return $form;
    }
}
