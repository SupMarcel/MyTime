<?php

namespace App\Module\Worker\Control;

use App\Service\StatusCalendar;
use Nette\Application\UI\Control;
use Nette\Security\User;
use App\Model\OrderModel;
use App\Model\TimeSlotModel;

class SlotControl extends Control
{
    private $slotId;
    private StatusCalendar $statusCalendar;
    private User $user;
    private OrderModel $orderModel;
    private TimeSlotModel $timeSlotModel;

    public function __construct(
            StatusCalendar $statusCalendar,
            User $user, 
            OrderModel $orderModel,
            TimeSlotModel $timeSlotModel
            )
    {
        $this->statusCalendar = $statusCalendar;
        $this->user = $user;
        $this->orderModel = $orderModel;
        $this->timeSlotModel = $timeSlotModel;
    }

    public function setSlotId(int $slotId): void
    {
        $this->slotId = $slotId;
    }

    public function render(): void
    {
        $userId = $this->user->getId(); // Získání ID uživatele
        $slot = $this->statusCalendar->getSingleTimeSlotWithStatus($this->slotId, $userId);
        $this->template->slot = $slot;
        $this->template->slotId = $slot["id"];
        $this->template->render(__DIR__ . '/templates/SlotControl.latte');
    }

    public function handleProcessSlot(int $slotId = null, int $status = null): void
    {
        $userId = $this->user->getId(); // Získání ID uživatele
        $slotId = $slotId ?? $this->getPresenter()->getParameter("slotId");
        $status = $status ?? $this->getPresenter()->getParameter("slotStatus");
        if ($status == 3) {
            $this->orderModel->addFreeOrder($slotId, $userId);
            $this->flashMessage('Slot byl přidán jako volný.');
        } elseif ($status == 1) {
            $this->orderModel->deleteFreeOrder($slotId, $userId);
            $this->flashMessage('Volný slot byl smazán.');
        }
      //  $slot = $this->timeSlotModel->getById($slotId);
      //  $dayId = $slot->day_id ;
       // $this->redrawControl('SlotSnippet');
       // $this->getPresenter()->handleGetTimeSlots($dayId);
        if ($this->getPresenter()->isAjax()) {
            bdump("ajax calling");
            $userId = $this->user->getId(); // Získání ID uživatele
            $slot = $this->statusCalendar->getSingleTimeSlotWithStatus($slotId, $userId);
            
            $this->template->slot = $slot;
            $this->template->slotId = $slot["id"];
            $this->template->setFile(__DIR__ . '/templates/SlotControl.latte');
            $this->redrawControl("timeSlotsSnippet");
            bdump($this->redrawControl("timeSlotsSnippet"));
        } else {
            bdump("getting time slots");
            $this->getPresenter()->redirect('this', [
    "dayId" => $this->timeSlotModel->getById($slotId)->{TimeSlotModel::COLUMN_DAY_ID}, 
    "do" => "getTimeSlots"
]);
        }
    }
}


