<?php

declare(strict_types=1);

namespace App\Module\Worker\Presenters;

use Nette;
use App\Service\RawWorkerCalendar;
use App\Model\DayModel;
use App\Service\StatusCalendar;
use App\Model\OrderModel;
use App\Model\TimeSlotModel;
use App\Module\Worker\Control\ISlotControlFactory;

final class MyCalendarPresenter extends Nette\Application\UI\Presenter
{
    private RawWorkerCalendar $rawWorkerCalendar;
    private DayModel $dayModel;
    private StatusCalendar $statusCalendar;
    private OrderModel $orderModel;
    private TimeSlotModel $timeSlotModel;
    private ISlotControlFactory $slotControlFactory;

    public function __construct(
        RawWorkerCalendar $rawWorkerCalendar,
        DayModel $dayModel,
        StatusCalendar $statusCalendar,
        OrderModel $orderModel,
        TimeSlotModel $timeSlotModel,
        ISlotControlFactory $slotControlFactory    
    ) {
        parent::__construct();
        $this->rawWorkerCalendar = $rawWorkerCalendar;
        $this->dayModel = $dayModel;
        $this->statusCalendar = $statusCalendar;
        $this->orderModel = $orderModel;
        $this->timeSlotModel = $timeSlotModel;
        $this->slotControlFactory = $slotControlFactory;
    }
    
    
    public function handleGetTimeSlots(int $dayId = null): void
    {   
        // Získání ID aktuálního uživatele
        $userId = $this->getUser()->getId();
        if ($this->isAjax()){
               $dayId = intval($this->getParameter("dayId"));
        }
        // Získání TimeSlotů pro daný den
        $timeSlots = $this->statusCalendar->getTimeSlotsWithStatusesForDay($dayId, $userId);
        // Předání TimeSlotů do šablony
        $this->template->timeSlots = $timeSlots;
        if ($this->isAjax()) {
         //  $this->redrawControl('slotContainer');
            $this->redrawControl("timeSlotsSnippet");
        }else{
           // $this->redirect('this', array("dayId" => $this->timeSlotModel->getById($slotId)->{TimeSlotModel::COLUMN_DAY_ID}, "do" => "getTimeSlots"));
        }
    }
    
    // Nová funkce pro zpracování slotu
    public function handleProcessSlot(int $slotId = null, int $status = null): void
    {
        $userId = $this->getUser()->getId();
        if ($this->isAjax()){
               $slotId = intval($this->getParameter("slotId"));
               $status = intval($this->getParameter("slotStatus"));
        }  
        if ($status == 3) {
            // Pokud je status "unavailable", přidáme nový volný order
            $this->orderModel->addFreeOrder($slotId, $userId);
            $this->flashMessage('Slot byl přidán jako volný.');
        } elseif ($status == 1) {
            // Pokud je status "free", smažeme volný order
            $this->orderModel->deleteFreeOrder($slotId, $userId);
            $this->flashMessage('Volný slot byl smazán.');
        }

        if ($this->isAjax()) {
            $slot = $this->statusCalendar->getSingleTimeSlotWithStatus($slotId, $userId);
            $this->template->slot = $slot;
            $this->template->slotId = $slotId;
            $this->redrawControl('slot-'.$slotId);
        } else {
            $this->redirect('this', array("dayId" => $this->timeSlotModel->getById($slotId)->{TimeSlotModel::COLUMN_DAY_ID}, "do" => "getTimeSlots"));
        }
    }
    
    public function handleUpdateSlotSnippet(int $slotId = null): void
    {
        $userId = $this->getUser()->getId();
        $slot = $this->statusCalendar->getSingleTimeSlotWithStatus($slotId, $userId);

        // Předání dat do šablony pro překreslení snippetu konkrétního slotu
        $this->template->slot = $slot;
        $this->template->slotId = $slotId;

        if ($this->isAjax()) {
            $this->redrawControl('slot-' . $slotId); // Překreslení pouze konkrétního slotu
        }
    }
    
    protected function createComponentSlot(): \Nette\Application\UI\Multiplier
    {
        return new \Nette\Application\UI\Multiplier(function ($slotId) {
            $slotControl = $this->slotControlFactory->create();
            $slotControl->setSlotId((int) $slotId); // Nastavení ID slotu
            return $slotControl;
        });
    }

    
    public function renderDefault(): void
    {
        if ($this->getPresenter()->isAjax() && strpos($this->getPresenter()->getParameter('do'), 'processSlot') !== false) {
            return;
        }

        bdump("rendering default");
        $userId = $this->getUser()->getId();

        // Získání struktury kalendáře
        $calendarStructure = $this->statusCalendar->generateCalendarStructure($userId);
        $columnCount = $this->dayModel->getNumberOfCalendarColumns();
        $rowData = array_fill(1, $columnCount, array_fill(1, 9, ''));
        // Předání dat do šablony
        $this->template->calendarStructure = $calendarStructure;
        $this->template->rowData = $rowData;
        $days = $this->rawWorkerCalendar->getDaysMap();
        
        $this->template->selectedDayId = $this->getParameter("dayId");
        $this->template->daysMap = $this->rawWorkerCalendar->getDaysMap(); // Překlad pro dny v týdnu
    
        if (empty($this->getParameter("dayId"))) {
            $this->template->selectedDayId = $this->dayModel->getDayIdByDate();
            $this->handleGetTimeSlots($this->dayModel->getDayIdByDate());
        }
    }
}