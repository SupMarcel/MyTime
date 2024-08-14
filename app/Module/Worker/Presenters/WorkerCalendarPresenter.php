<?php

declare(strict_types=1);

namespace App\Module\Worker\Presenters;

use Nette\Application\UI\Presenter;
use App\Model\TimeSlotModel;

final class WorkerCalendarPresenter extends Presenter
{
    private TimeSlotModel $timeSlotModel;

    public function __construct(TimeSlotModel $timeSlotModel)
    {
        $this->timeSlotModel = $timeSlotModel;
    }

    public function renderDefault(): void
    {
        // Generování a organizace časových slotů (14 dní dopředu)
        $organizedSlots = $this->timeSlotModel->generateAndOrganizeSlots($this->getUser()->getId());
        
        // V Presenter nebo Control, kde je předáváte data do šablony:
        $this->template->slotsJson = json_encode($organizedSlots);


        // Předání do šablony
        $this->template->organizedSlots = $organizedSlots;
    }
}


