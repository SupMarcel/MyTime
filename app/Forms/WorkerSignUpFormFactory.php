<?php

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\WorkerModel;
use Contributte\Translation\Translator;

class WorkerSignUpFormFactory extends SignUpFormFactory
{
    private WorkerModel $workerModel;

    public function __construct(FormFactory $factory, Translator $translator, WorkerModel $workerModel)
    {
        parent::__construct($factory, $translator);
        $this->workerModel = $workerModel;
    }

    public function create(callable $onSuccess, ?array $existingUser = null): Form
    {
        $form = $this->factory->create();
        $form->addGroup($this->translator->translate('messages.signUpForm.workerDescription'));

        $this->addCommonFields($form, $existingUser);

        $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
            ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), WorkerModel::PASSWORD_MIN_LENGTH))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
            ->addRule($form::MIN_LENGTH, null, WorkerModel::PASSWORD_MIN_LENGTH);

        $form->addText('image', $this->translator->translate('messages.signUpForm.image'))
            ->setRequired(false);

        $form->addTextArea('description', $this->translator->translate('messages.signUpForm.description'))
            ->setRequired(false);

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            if (!$existingUser) {
                $this->workerModel->addWorker($data->username, $data->email, $data->password, $data->image, $data->description, $data->phone, null);
            }
            $onSuccess();
        };

        return $form;
    }
}
