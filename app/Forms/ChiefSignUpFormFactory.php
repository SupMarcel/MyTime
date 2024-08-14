<?php

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\ChiefModel;
use Contributte\Translation\Translator; // Správný import třídy Translator

class ChiefSignUpFormFactory extends SignUpFormFactory
{
    private ChiefModel $chiefModel;

    public function __construct(FormFactory $factory, Translator $translator, ChiefModel $chiefModel)
    {
        parent::__construct($factory, $translator);
        $this->chiefModel = $chiefModel;
    }

    public function create(callable $onSuccess, ?array $existingUser = null): Form
    {
        $form = $this->factory->create();
        $form->addGroup($this->translator->translate('messages.signUpForm.chiefDescription'));

        $this->addCommonFields($form, $existingUser);

        $form->addText('location_name', $this->translator->translate('messages.signUpForm.locationName'))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_location_name'));

        $form->addTextArea('location_description', $this->translator->translate('messages.signUpForm.locationDescription'))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_location_description'));

        $form->addUpload('location_image', $this->translator->translate('messages.signUpForm.locationImage'))
            ->setRequired(false);

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            if (!$existingUser) {
                $this->chiefModel->addChief($data->username, $data->email, $data->password, $data->location_name, $data->location_description, $data->location_image, $data->phone);
            }
            $onSuccess();
        };

        return $form;
    }
}
