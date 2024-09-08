<?php

declare(strict_types=1);

namespace App\Module\Worker\Presenters;

use Nette;
use App\Service\RawWorkerCalendar;
use App\Model\DayModel;
use App\Model\TimeSlotModel;

final class WorkerDashboardPresenter extends Nette\Application\UI\Presenter
{
    private RawWorkerCalendar $rawWorkerCalendar;
    private DayModel $dayModel;
    private TimeSlotModel $timeSlotModel;

    public function __construct(
        RawWorkerCalendar $rawWorkerCalendar,
        DayModel $dayModel,
        TimeSlotModel $timeSlotModel
    ) {
        parent::__construct();
        $this->rawWorkerCalendar = $rawWorkerCalendar;
        $this->dayModel = $dayModel;
        $this->timeSlotModel = $timeSlotModel;  // Injektujeme TimeSlotModel
    }
    
    
    public function handleGetTimeSlots(int $dayId): void
    {   
       // Získání TimeSlotů pro daný den
        $timeSlots = $this->timeSlotModel->getTimeSlotsForDay($dayId);
        // Předání TimeSlotů do šablony
        $this->template->timeSlots = $timeSlots;
        
        
        bdump($timeSlots);

        // Volitelně: Přesměrování zpět na aktuální stránku nebo vykreslení šablony
        if ($this->isAjax()) {
            $this->redrawControl('timeSlotsSnippet'); // Zajistí vykreslení části stránky s TimeSloty, pokud jde o AJAX
        }
    }
    
    public function renderDefault(): void
    {
        $userId = $this->getUser()->getId();

        // Získání struktury kalendáře
        $calendarStructure = $this->rawWorkerCalendar->generateCalendarStructure($userId);

        // Spočítání počtu sloupců
        $columnCount = $this->dayModel->getNumberOfCalendarColumns();
        $rowData = array_fill(1, $columnCount, array_fill(1, 9, ''));

        // Předání dat do šablony
        $this->template->calendarStructure = $calendarStructure;
        $this->template->rowData = $rowData;
        $days = $this->rawWorkerCalendar->getDaysMap();
        
        $this->template->daysMap = $this->rawWorkerCalendar->getDaysMap(); // Překlad pro dny v týdnu
    }
}







