<?php


declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\ClientModel;
use Contributte\Translation\Translator;

class ClientSignUpFormFactory extends SignUpFormFactory
{
    private ClientModel $clientModel;

    public function __construct(FormFactory $factory, Translator $translator, ClientModel $clientModel)
    {
        parent::__construct($factory, $translator);
        $this->clientModel = $clientModel;
    }

    public function create(callable $onSuccess, ?array $existingUser = null): Form
    {
        $form = $this->factory->create();
        $form->addGroup($this->translator->translate('messages.signUpForm.clientDescription'));

        $this->addCommonFields($form, $existingUser);

        $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
            ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), ClientModel::PASSWORD_MIN_LENGTH))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
            ->addRule($form::MIN_LENGTH, null, ClientModel::PASSWORD_MIN_LENGTH);

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            if (!$existingUser) {
                $this->clientModel->addClient($data->username, $data->email, $data->password, $data->phone);
            }
            $onSuccess();
        };

        return $form;
    }
}


