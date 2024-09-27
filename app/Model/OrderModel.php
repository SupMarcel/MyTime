<?php

namespace App\Model;

use Nette\Database\Explorer;
use App\Model\WorkerModel;
use App\Model\TimeSlotModel;
use App\Model\StatusModel;

class OrderModel extends BaseModel
{
    const TABLE_NAME = 'orders';
    const COLUMN_ID = 'id';
    const COLUMN_WORKER_ID = 'worker_id';
    const COLUMN_TIME_SLOT_ID = 'time_slot_id';
    const COLUMN_STATUS_ID = 'status_id';
    const COLUMN_CLIENT_ID = 'client_id';
    const COLUMN_LOCATION_ID = 'location_id';

    private WorkerModel $workerModel;
    private TimeSlotModel $timeSlotModel;

    public function __construct(Explorer $database,
                                WorkerModel $workerModel,
                                TimeSlotModel $timeSlotModel)
    {
        parent::__construct($database);
        $this->workerModel = $workerModel;
        $this->timeSlotModel = $timeSlotModel;
    }

    /**
     * Přidá nový order s parametry slot_id a worker_id.
     * Status bude nastaven na "free" a client_id na NULL.
     *
     * @param int $slotId
     * @param int $workerId
     * @return int ID nově přidaného orderu
     */
    public function addFreeOrder(int $slotId, int $workerId, ?int $locationId = null): int
    {
        // Pokud není locationId zadáno, získáme ho z pracovníka
        if (empty($locationId)) {
            $location = $this->workerModel->getSingleWorkerLocation($workerId);
            $locationId = $location ? $location->{LocationModel::COLUMN_ID} : null;
        }

        if ($locationId === null) {
            throw new \Exception('Location could not be determined.');
        }
        bdump($slotId);
        return $this->addAndReturnId([
            self::COLUMN_WORKER_ID => $workerId,
            self::COLUMN_TIME_SLOT_ID => $slotId,
            self::COLUMN_LOCATION_ID => $locationId,
            self::COLUMN_STATUS_ID => StatusModel::FREEID,
            self::COLUMN_CLIENT_ID => null,
        ]);
    }

    /**
     * Smaže order s parametry slot_id a worker_id, pokud má status "free" a client_id je NULL.
     *
     * @param int $slotId
     * @param int $workerId
     * @return void
     */
    public function deleteFreeOrder(int $slotId, int $workerId): void
    {
        $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_TIME_SLOT_ID, $slotId)
            ->where(self::COLUMN_WORKER_ID, $workerId)
            ->where(self::COLUMN_STATUS_ID, StatusModel::FREEID) // Použití konstanty FREEID ze StatusModel
            ->where(self::COLUMN_CLIENT_ID, null)
            ->delete();
    }
    
        /**
     * Funkce zjišťuje, zda kombinace worker_id a time_slot_id existuje v tabulce orders.
     * @param int $slotId
     * @param int $workerId
     * @return bool
     */
    public function doesWorkerSlotExist(int $slotId, int $workerId): bool
    {
        return (bool)$this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_WORKER_ID, $workerId)
            ->where(self::COLUMN_TIME_SLOT_ID, $slotId)
            ->fetch();
    }

    /**
     * Funkce vrací status_id pro daného worker_id a slot_id, pokud existuje.
     * @param int $slotId
     * @param int $workerId
     * @return int|null Vrací status_id nebo null, pokud kombinace neexistuje.
     */
    public function getStatusIdForWorkerSlot(int $slotId, int $workerId): int
    {
        $order = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_WORKER_ID, $workerId)
            ->where(self::COLUMN_TIME_SLOT_ID, $slotId)
            ->fetch();
        return intval(!empty($order) ? $order->{self::COLUMN_STATUS_ID} : StatusModel::UNAVAILABLEID);
    }

    
    
    // Privátní funkce pro získání statusu podle typu slotů (den, týden, měsíc)
    private function getStatusForWorkerAndTimeSlots(int $workerId, array $timeSlots): int
    {
        if (empty($timeSlots)) {
            return StatusModel::UNAVAILABLEID;
        }

        $statuses = [];

        foreach ($timeSlots as $timeSlot) {
            $order = $this->database->table(self::TABLE_NAME)
                ->where(self::COLUMN_WORKER_ID, $workerId)
                ->where(self::COLUMN_TIME_SLOT_ID, $timeSlot->{TimeSlotModel::COLUMN_ID})
                ->fetch();

            if ($order) {
                $statuses[] = $order->{self::COLUMN_STATUS_ID};
            }
        }

        if (empty($statuses)) {
            return StatusModel::UNAVAILABLEID;
        }

        $allFree = true;
        $allOrdered = true;

        foreach ($statuses as $statusId) {
            if ($statusId !== StatusModel::FREEID) {
                $allFree = false;
            }
            if ($statusId !== StatusModel::ORDEREDID) {
                $allOrdered = false;
            }
        }

        if ($allFree) {
            return StatusModel::FREEID;
        }

        if ($allOrdered) {
            return StatusModel::ORDEREDID;
        }

        return StatusModel::MIXID;
    }

    // Funkce pro zjištění statusu pro den
    public function getStatusForWorkerAndDay(int $workerId, int $dayId): int
    {
        $timeSlots = $this->timeSlotModel->getTimeSlotsForDay($dayId);
        return $this->getStatusForWorkerAndTimeSlots($workerId, $timeSlots);
    }

    // Funkce pro zjištění statusu pro týden
    public function getStatusForWorkerAndWeek(int $workerId, int $weekId): int
    {
        $timeSlots = $this->timeSlotModel->getTimeSlotsForWeek($weekId);
        return $this->getStatusForWorkerAndTimeSlots($workerId, $timeSlots);
    }

    // Funkce pro zjištění statusu pro měsíc
    public function getStatusForWorkerAndMonth(int $workerId, int $monthId): int
    {
        $timeSlots = $this->timeSlotModel->getTimeSlotsForMonth($monthId);
        return $this->getStatusForWorkerAndTimeSlots($workerId, $timeSlots);
    }
    
    public function deleteOldFreeOrders(int $workerId): void
    {
        $currentDateTime = new \DateTime();

    // Dotaz pomocí explicitního SQL JOIN mezi orders a time_slots
    $ordersToDelete = $this->database->query('
        SELECT orders.*
        FROM orders
        JOIN time_slots ON orders.time_slot_id = time_slots.id
        WHERE orders.worker_id = ? 
          AND orders.status_id = ? 
          AND time_slots.end_time < ?', 
        $workerId, 
        StatusModel::FREEID, 
        $currentDateTime
    );

    // Zkontrolujeme, zda byly nalezeny nějaké záznamy k mazání
    foreach ($ordersToDelete as $order) {
        // Smažeme jednotlivé záznamy z tabulky
        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $order->id)->delete();
    }
    }
}

