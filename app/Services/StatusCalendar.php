<?php

namespace App\Service;

use App\Model\DayModel;
use App\Model\WorkerModel;
use App\Model\OrderModel;
use App\Model\MonthModel;
use App\Model\UserModel;
use App\Model\LocationModel;
use App\Model\TimeSlotModel;
use App\Model\StatusModel; // Přidání StatusModelu
use App\Model\BaseModelWithTranslator;
use Nette\Database\Explorer;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

class StatusCalendar extends BaseModelWithTranslator
{
    private DayModel $dayModel;
    private WorkerModel $workerModel;
    private OrderModel $orderModel;
    private MonthModel $monthModel;
    private UserModel $userModel;
    private LocationModel $locationModel;
    private TimeSlotModel $timeSlotModel;
    private StatusModel $statusModel; // Přidání StatusModelu

    public function __construct(
        Explorer $database,
        ITranslator $translator,
        DayModel $dayModel,
        WorkerModel $workerModel,
        OrderModel $orderModel,
        MonthModel $monthModel,
        UserModel $userModel,
        LocationModel $locationModel,
        TimeSlotModel $timeSlotModel,
        StatusModel $statusModel  // Přidání StatusModelu do konstruktoru
    ) {
        parent::__construct($database, $translator);
        $this->dayModel = $dayModel;
        $this->workerModel = $workerModel;
        $this->orderModel = $orderModel;
        $this->monthModel = $monthModel;
        $this->userModel = $userModel;
        $this->locationModel = $locationModel;
        $this->timeSlotModel = $timeSlotModel;
        $this->statusModel = $statusModel;  // Uložení StatusModelu do vlastnosti třídy
    }

    /**
     * Privátní metoda, která pro daného pracovníka vrací pole měsíců se statusem.
     * 
     * @param int $workerId
     * @return array
     */
    private function getMonthsAndStatusesForWorker(int $workerId): array
    {
        // Získáme všechny měsíce ve sledovaném období z DayModelu
        $months = $this->dayModel->getSortedMonthIds();

        // Výsledné pole pro všechny měsíce
        $result = [];

        // Iterace přes všechny měsíce
        foreach ($months as $monthId) {
            // Získáme status pro daný měsíc a pracovníka pomocí metody z OrderModelu
            $status = $this->orderModel->getStatusForWorkerAndMonth($workerId, $monthId);

            // Přidáme do výsledného pole klíče "id" a "status"
            $result[] = [
                'id' => $monthId,
                'status' => $status,
            ];
        }

        return $result;
    }
    
       private function getWeeksAndStatusesForWorker(int $workerId, int $monthId): array
    {
        // Získáme všechny týdny pro daný month_id z DayModelu
        $weeks = $this->dayModel->getSortedWeekIdsForMonth($monthId);

        // Výsledné pole pro všechny týdny
        $result = [];

        // Iterace přes všechny týdny
        foreach ($weeks as $weekId) {
            // Získáme status pro daný týden a pracovníka pomocí metody z OrderModelu
            $status = $this->orderModel->getStatusForWorkerAndWeek($workerId, $weekId);

            // Přidáme do výsledného pole klíče "id" a "status"
            $result[] = [
                'id' => $weekId,
                'status' => $status,
            ];
        }

        return $result;
    }
    
        private function getDaysAndStatusesForWorker(int $workerId, int $monthId, int $weekId): array
    {
        // Získáme všechny dny pro daný month_id a week_id z DayModelu
        $days = $this->dayModel->getSortedDayIdsForMonthAndWeek($monthId, $weekId);

        // Výsledné pole pro všechny dny
        $result = [];

        // Iterace přes všechny dny
        foreach ($days as $dayId) {
            // Získáme status pro daný den a pracovníka pomocí metody z OrderModelu
            $status = $this->orderModel->getStatusForWorkerAndDay($workerId, $dayId);

            // Přidáme do výsledného pole klíče "id" a "status"
            $result[] = [
                'id' => $dayId,
                'status' => $status,
            ];
        }

        return $result;
    }
    
    public function generateCalendarStructure(int $userId, ?int $locationId = null): array
    {
        // Kontrola, jestli je uživatel worker
        if (!$this->workerModel->isUserWorker($userId)) {
            // Přeložená zpráva pro uživatele, který není worker
            throw new \Exception($this->translator->translate('messages.errors.StatusCalendar.not_a_worker'));
        }

        $calendar = [];

        // Získání uživatele a případně jeho lokace, pokud není zadaná
        $user = $this->userModel->getById($userId);

        // Pokud nebyl zadán locationId, zjistíme lokaci
        if (empty($locationId)) {
            $location = $this->workerModel->getSingleWorkerLocation($userId);
            $locationId = $location ? $location->{LocationModel::COLUMN_ID} : null;
        }

        // Kontrola, zda existuje uživatel a lokace
        if (!$user || !$locationId) {
            throw new \Exception("User or location not found.");
        }

        // Přeložení titulku pro kalendář (rozložený na dvě části)
        $calendar['calendar_title_main'] = sprintf(
            $this->translator->translate('messages.calendar.header.main'),
            $user->{UserModel::COLUMN_USERNAME},
            $this->locationModel->getLocationById($locationId)->{LocationModel::COLUMN_NAME}
        );
        $calendar['calendar_title_dates'] = sprintf(
            $this->translator->translate('messages.calendar.header.dates'),
            (new \DateTime())->format('d.m.Y'),
            (clone (new \DateTime()))->modify('+13 days')->format('d.m.Y')
        );

        // Inicializace klíče 'months'
        $calendar['months'] = [];

        // Získání všech relevantních měsíců ve sledovaném období pro daného pracovníka
        $months = $this->getMonthsAndStatusesForWorker($userId);

        // Iterace přes měsíce
        foreach ($months as $monthData) {
            $monthId = $monthData['id'];
            $monthStatus = $monthData['status'];

            // Přeložení názvu měsíce
            $monthName = $this->translator->translate('messages.dayModel.month.' . strtolower($this->monthModel->getMonthNameById($monthId)));

            // Inicializace pole pro konkrétní měsíc se statusem
            $calendar['months'][$monthName] = ['status' => $monthStatus, 'weeks' => []];

            // Iterace přes týdny pro daný měsíc
            $weeks = $this->getWeeksAndStatusesForWorker($userId, $monthId);
            foreach ($weeks as $weekData) {
                $weekId = $weekData['id'];
                $weekStatus = $weekData['status'];

                // Přeložení názvu týdne
                $weekName = $this->translator->translate('messages.dayModel.week') . ' ' . $weekId;

                // Inicializace pole pro konkrétní týden se statusem
                $calendar['months'][$monthName]['weeks'][$weekName] = ['status' => $weekStatus, 'days' => []];

                // Získání všech relevantních dní pro daný týden a měsíc
                $days = $this->getDaysAndStatusesForWorker($userId, $monthId, $weekId);
                // Iterace přes dny v týdnu a přidání do struktury kalendáře
                foreach ($days as $dayData) {
                    $dayId = $dayData['id'];
                    $dayStatus = $dayData['status'];

                    // Získání přeloženého formátu dne
                    $dayRow = $this->dayModel->getById($dayId);
                    $dayFormatted = $this->translator->translate('messages.dayModel.day.' . strtolower($dayRow->{DayModel::COLUMN_DAY_FROM_WEEK_SHORT}))
                        . ' ' . $dayRow->{DayModel::COLUMN_NUMBER_SHOW} . '.'
                        . str_pad($dayRow->{DayModel::COLUMN_MONTH_NUMBER_SHOW}, 2, '0', STR_PAD_LEFT) . '.';
                    $calendar['months'][$monthName]['weeks'][$weekName]['days'][$dayId] = [
                        'formatted' => $dayFormatted,
                        'status' => $dayStatus
                    ];
                }
            }
        }
        
        
        return $calendar;
    }
    
    public function getTimeSlotsWithStatusesForDay(int $dayId, int $userId): ArrayHash
    {
        // Získáme pole slotů s 'id' a 'show' pro daný den
        $timeSlots = $this->timeSlotModel->getSimplifiedTimeSlotsForDay($dayId);
        $this->orderModel->deleteOldFreeOrders($userId);
        // Iterujeme přes všechny získané sloty
        foreach ($timeSlots as &$slot) {
            // Získáme status pro daný slot a uživatele pomocí metody getStatusOrUnavailable
            $slot['status'] = $this->getStatusOrUnavailable($slot['id'], $userId);
        }

        // Vrátíme pole slotů se statusem
        $timeSlots = ArrayHash::from($timeSlots);
        return $timeSlots;
    }
    
    private function getStatusOrUnavailable(int $slotId, int $workerId): int
    {
        if ($this->orderModel->doesWorkerSlotExist($slotId, $workerId)) {
            return $this->orderModel->getStatusIdForWorkerSlot($slotId, $workerId) ?? $this->statusModel::UNAVAILABLEID;
        }

        return $this->statusModel::UNAVAILABLEID;
    }
    
    public function getSingleTimeSlotWithStatus(int $slotId, int $userId): array
    {
        // Získáme časový slot a status pro konkrétní slotId
        $slot = $this->timeSlotModel->getSimplifiedTimeSlot($slotId);
        $slot['status'] = $this->orderModel->getStatusIdForWorkerSlot($slot['id'], $userId);

        return $slot;
    }


}
