<?php

declare(strict_types=1);

namespace App\Module\Worker\Presenters;

use Nette\Application\UI\Presenter;
use App\Model\TimeSlotModel;
use App\Model\UserModel;
use Nette\Localization\ITranslator;

final class WorkerCalendarPresenter extends Presenter
{
    private TimeSlotModel $timeSlotModel;
    private UserModel $userModel;
    private ITranslator $translator;

    public function __construct(TimeSlotModel $timeSlotModel, UserModel $userModel, ITranslator $translator)
    {
        $this->timeSlotModel = $timeSlotModel;
        $this->userModel = $userModel;
        $this->translator = $translator;
    }
    
    private function getCalendarWeek(\DateTime $date): int
    {
        return (int)$date->format('W'); // 'W' formát vrací číslo týdne
    }

    
    public function renderDefault(): void
    {
        // Získejte ID uživatele
        $workerId = $this->getUser()->getId();

        // Generování a organizace časových slotů (14 dní dopředu)
        $organizedSlots = $this->timeSlotModel->generateAndOrganizeSlots($workerId);

        // Načtěte informace o uživateli
        $worker = $this->userModel->getById($workerId);

        // Překlady pomocí translatoru
        $title = $this->translator->translate('messages.WorkerCalendarPresenter.title');
        $from = $this->translator->translate('messages.WorkerCalendarPresenter.from');
        $to = $this->translator->translate('messages.WorkerCalendarPresenter.to');
        $month = $this->translator->translate('messages.WorkerCalendarPresenter.month.august');

        // Formátování data
        $startDate = new \DateTime('2024-08-14');
        $endDate = new \DateTime('2024-08-27');
        $formattedStartDate = $startDate->format('j.') . " $month";
        $formattedEndDate = $endDate->format('j.') . " $month";
        $this->template->formattedYear = $startDate->format('Y');

        // Příprava formátovaných dnů a týdnů
        $formattedDays = [];
        $weekNumbers = [];

        foreach ($organizedSlots as $calendarTitle => $weeks) {
            foreach ($weeks as $week => $days) {
                $firstDayOfWeek = \DateTime::createFromFormat('Ymd', explode('_', array_key_first($days))[1]);
                $calendarWeekNumber = $this->getCalendarWeek($firstDayOfWeek);
                $weekNumbers[$week] = $calendarWeekNumber;  // Přiřazení správného čísla kalendářního týdne
                foreach ($days as $day => $slots) {
                    $dayParts = explode('_', $day);
                    $dayDate = \DateTime::createFromFormat('Ymd', $dayParts[1]);
                    $dayName = $this->translator->translate('messages.days.' . strtolower(substr($dayParts[0], 0, 2)));
                    $formattedDay = $dayName . ' ' . $dayDate->format('d.m.');
                    $formattedDays[$day] = $formattedDay;
                }
            }
        }

        // Předání proměnných do šablony
        $this->template->formattedDays = $formattedDays;
        $this->template->weekNumbers = $weekNumbers;
        $this->template->title = $title;
        $this->template->workerName = $worker->username;
        $this->template->from = $from;
        $this->template->to = $to;
        $this->template->formattedStartDate = $formattedStartDate;
        $this->template->formattedEndDate = $formattedEndDate;
        $this->template->slotsJson = json_encode($organizedSlots);
        $this->template->organizedSlots = $organizedSlots;
        $this->template->translator = $this->translator;  // Předání translatoru do šablony
    }



}
