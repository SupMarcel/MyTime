<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use Contributte\Translation\Translator;

final class SignUpFormFactory
{
    use Nette\SmartObject;

    private FormFactory $factory;
    private Model\UserFacade $userFacade;
    private Translator $translator;

    public function __construct(FormFactory $factory, Model\UserFacade $userFacade, Translator $translator)
    {
        $this->factory = $factory;
        $this->userFacade = $userFacade;
        $this->translator = $translator;
    }

    public function createAdminForm(callable $onSuccess, ?ActiveRow $existingUser = null): Form
    {
        $form = $this->factory->create();

        // Popis role
        $form->addGroup($this->translator->translate('messages.signUpForm.adminDescription'));

        $form->addText('username', $this->translator->translate('messages.signUpForm.username'))
            ->setDefaultValue($existingUser ? $existingUser->username : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_username'))
            ->setDisabled($existingUser ? true : false);

        $form->addEmail('email', $this->translator->translate('messages.signUpForm.email'))
            ->setDefaultValue($existingUser ? $existingUser->email : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_email'))
            ->setDisabled($existingUser ? true : false);

        $form->addText('phone', $this->translator->translate('messages.signUpForm.phone'))
            ->setDefaultValue($existingUser ? $existingUser->phone : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_phone'))
            ->setDisabled($existingUser ? true : false);

        if (!$existingUser) {
            $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
                ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), $this->userFacade::PasswordMinLength))
                ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
                ->addRule($form::MIN_LENGTH, null, $this->userFacade::PasswordMinLength);
        }

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            try {
                if ($existingUser) {
                    $this->userFacade->addRole($existingUser->id, 'administrator');
                } else {
                    $this->userFacade->addAdmin($data->username, $data->email, $data->phone, $data->password);
                }
                $onSuccess();
            } catch (Model\DuplicateNameException $e) {
                $form['username']->addError($this->translator->translate('messages.signUpForm.usernameTaken'));
            }
        };

        return $form;
    }
    
            public function createWorkerForm(callable $onSuccess, ?ActiveRow $existingUser = null): Form
    {
        $form = $this->factory->create();

        // Popis role
        $form->addGroup($this->translator->translate('messages.signUpForm.workerDescription'));

        $form->addText('username', $this->translator->translate('messages.signUpForm.username'))
            ->setDefaultValue($existingUser ? $existingUser->username : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_username'))
            ->setDisabled($existingUser ? true : false);

        $form->addEmail('email', $this->translator->translate('messages.signUpForm.email'))
            ->setDefaultValue($existingUser ? $existingUser->email : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_email'))
            ->setDisabled($existingUser ? true : false);

        $form->addText('phone', $this->translator->translate('messages.signUpForm.phone'))
            ->setDefaultValue($existingUser ? $existingUser->phone : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_phone'))
            ->setDisabled($existingUser ? true : false);

        if (!$existingUser) {
            $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
                ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), $this->userFacade::PasswordMinLength))
                ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
                ->addRule($form::MIN_LENGTH, null, $this->userFacade::PasswordMinLength);
        }

        // Změna na `addUpload` pro obrázek
        $form->addUpload('image', $this->translator->translate('messages.signUpForm.image'))
            ->setRequired(false);

        $form->addTextArea('description', $this->translator->translate('messages.signUpForm.description'))
            ->setRequired(false);

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            try {
                if ($existingUser) {
                    $this->userFacade->addRole($existingUser->id, 'worker');
                } else {
                    // Zpracování nahraného obrázku
                    $imagePath = $this->saveImage($data->image);
                    // Doplnění chybějících argumentů
                    $this->userFacade->addWorker(
                        $data->username,
                        $data->email,
                        $data->password,
                        $imagePath,
                        $data->description,
                        null, // Nebo specifický locationId, pokud je k dispozici
                        'worker' // Specifikace role
                    );
                }
                $onSuccess();
            } catch (Model\DuplicateNameException $e) {
                $form['username']->addError($this->translator->translate('messages.signUpForm.usernameTaken'));
            }
        };

        return $form;
    }

    private function saveImage(Nette\Http\FileUpload $fileUpload): string
    {
        if ($fileUpload->isOk() && $fileUpload->isImage()) {
            $filePath = 'uploads/' . $fileUpload->getSanitizedName();
            $fileUpload->move($filePath);
            return $filePath;
        }
        return '';
    }



        public function createChiefForm(callable $onSuccess, ?ActiveRow $existingUser = null): Form
    {
        $form = $this->factory->create();

        // Popis role
        $form->addGroup($this->translator->translate('messages.signUpForm.chiefDescription'));

        $form->addText('username', $this->translator->translate('messages.signUpForm.username'))
            ->setDefaultValue($existingUser ? $existingUser->username : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_username'))
            ->setDisabled($existingUser ? true : false);

        $form->addEmail('email', $this->translator->translate('messages.signUpForm.email'))
            ->setDefaultValue($existingUser ? $existingUser->email : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_email'))
            ->setDisabled($existingUser ? true : false);

        if (!$existingUser) {
            $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
                ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), $this->userFacade::PasswordMinLength))
                ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
                ->addRule($form::MIN_LENGTH, null, $this->userFacade::PasswordMinLength);
        }

        // Fields specific to chiefs
        $form->addUpload('location_image', $this->translator->translate('messages.signUpForm.locationImage'))
            ->setHtmlId('chief-location_image')
            ->setRequired(false);

        $form->addText('location_name', $this->translator->translate('messages.signUpForm.locationName'))
            ->setHtmlId('chief-location_name')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_location_name'));

        $form->addTextArea('location_description', $this->translator->translate('messages.signUpForm.locationDescription'))
            ->setHtmlId('chief-location_description')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_location_description'));

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            try {
                if ($existingUser) {
                    $locationId = $this->userFacade->createLocation($data->location_name, $data->location_description, '');
                    $this->userFacade->addWorker($existingUser->username, $existingUser->email, '', '', '', $locationId);
                } else {
                    $locationImage = $this->saveLocationImage($data->location_image);
                    $locationId = $this->userFacade->createLocation($data->location_name, $data->location_description, $locationImage);
                    $this->userFacade->addWorker($data->username, $data->email, $data->password, '', '', $locationId);
                }
                $onSuccess();
            } catch (Model\DuplicateNameException $e) {
                $form['username']->addError($this->translator->translate('messages.signUpForm.usernameTaken'));
            }
        };

        return $form;
    }

     public function createClientForm(callable $onSuccess, ?ActiveRow $existingUser = null): Form
    {
        $form = $this->factory->create();
        
        // Popis role
        $form->addGroup($this->translator->translate('messages.signUpForm.clientDescription'));

        $form->addText('username', $this->translator->translate('messages.signUpForm.username'))
            ->setDefaultValue($existingUser ? $existingUser->username : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_username'))
            ->setDisabled($existingUser ? true : false);

        $form->addEmail('email', $this->translator->translate('messages.signUpForm.email'))
            ->setDefaultValue($existingUser ? $existingUser->email : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_email'))
            ->setDisabled($existingUser ? true : false);

        $form->addText('phone', $this->translator->translate('messages.signUpForm.phone'))
            ->setDefaultValue($existingUser ? $existingUser->phone : '')
            ->setRequired($this->translator->translate('messages.signUpForm.enter_phone'))
            ->setDisabled($existingUser ? true : false);

        if (!$existingUser) {
            $form->addPassword('password', $this->translator->translate('messages.signUpForm.password'))
                ->setOption('description', sprintf($this->translator->translate('messages.signUpForm.passwordDescription'), $this->userFacade::PasswordMinLength))
                ->setRequired($this->translator->translate('messages.signUpForm.enter_password'))
                ->addRule($form::MIN_LENGTH, null, $this->userFacade::PasswordMinLength);
        }

        $form->addSubmit('send', $this->translator->translate('messages.signUpForm.signUp'));

        $form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess, $existingUser): void {
            try {
                if ($existingUser) {
                    $this->userFacade->addRole($existingUser->id, 'client');
                } else {
                    $this->userFacade->add($data->username, $data->email, $data->password, 'client');
                }
                $onSuccess();
            } catch (Model\DuplicateNameException $e) {
                $form['username']->addError($this->translator->translate('messages.signUpForm.usernameTaken'));
            }
        };

        return $form;
    }

    private function saveLocationImage(Nette\Http\FileUpload $fileUpload): string
    {
        if ($fileUpload->isOk() && $fileUpload->isImage()) {
            $filePath = 'uploads/' . $fileUpload->getSanitizedName();
            $fileUpload->move($filePath);
            return $filePath;
        }
        return '';
    }
}
