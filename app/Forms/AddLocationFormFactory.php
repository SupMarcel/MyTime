<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\ChiefModel;
use App\Model\RoleModel;
use Contributte\Translation\Translator;

class AddLocationFormFactory extends SignUpFormFactory
{
    private ChiefModel $chiefModel;
    private RoleModel $roleModel;

    public function __construct(FormFactory $factory, Translator $translator, ChiefModel $chiefModel, RoleModel $roleModel)
    {
        parent::__construct($factory, $translator);
        $this->chiefModel = $chiefModel;
        $this->roleModel = $roleModel;
    }

    public function create(callable $onSuccess, ?array $user = null): Form
    {
        $form = $this->factory->create();

        // Přidání základních polí pro přihlášeného uživatele
        $this->addCommonFields($form, $user, true); // Použijeme `true` pro `isRoleOnly`, protože uživatel je již registrován

        // Přidání specifických polí pro novou provozovnu
        $form->addText('location_name', $this->translator->translate('messages.signUpForm.locationName'))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_location_name'));

        $form->addTextArea('location_description', $this->translator->translate('messages.signUpForm.locationDescription'))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_location_description'));

        $form->addUpload('location_image', $this->translator->translate('messages.signUpForm.locationImage'))
            ->setRequired(false);

        // Přidání adresních polí
        $form->addGroup($this->translator->translate('messages.addLocationForm.addressDetails'));
        
        $form->addText('street', $this->translator->translate('messages.signUpForm.street'))
            ->setRequired(false);

        $form->addText('number_of_street', $this->translator->translate('messages.signUpForm.numberOfStreet'))
            ->setRequired(false);

        $form->addText('city', $this->translator->translate('messages.signUpForm.city'))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_city'));

        $form->addText('zip_code', $this->translator->translate('messages.signUpForm.zipCode'))
            ->setRequired(false);

        $form->addText('region', $this->translator->translate('messages.signUpForm.region'))
            ->setRequired(false);

        $form->addText('state', $this->translator->translate('messages.signUpForm.state'))
            ->setRequired($this->translator->translate('messages.signUpForm.enter_state'));

        // Skrytá pole pro JavaScript
        $form->addText('latitude')
            ->setRequired(false)
            ->setHtmlType('hidden')
            ->getLabelPrototype()->setName(''); // Skrytí labelu

        $form->addText('longitude')
            ->setRequired(false)
            ->setHtmlType('hidden')
            ->getLabelPrototype()->setName(''); // Skrytí labelu

        // Potvrzení hesla
        $this->addPasswordField($form, true); // Použití metody z `SignUpFormFactory`

        // Tlačítko pro odeslání
        $form->addSubmit('send', $this->translator->translate('messages.addLocationForm.addLocation'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $user): void {
            // Ověření aktuálního hesla pro přidání nové provozovny
            if (!password_verify($data->currentPassword, $user['password'])) {
                $form->addError($this->translator->translate('messages.signUpForm.incorrect_password'));
                return;
            }

            // Přidání nové adresy
            $addressId = $this->chiefModel->addAddress([
                'street' => $data->street,
                'number_of_street' => $data->number_of_street,
                'city' => $data->city,
                'zip_code' => $data->zip_code,
                'region' => $data->region,
                'state' => $data->state,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
            ]);

            // Přidání nové provozovny pro existujícího šéfa
            $this->chiefModel->addChiefLocation(
                $user['id'],
                $data->location_name,
                $data->location_description,
                $data->location_image,
                $addressId
            );

            $onSuccess();
        };

        return $form;
    }
}
