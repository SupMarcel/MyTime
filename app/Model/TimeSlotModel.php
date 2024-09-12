<?php

namespace App\Model;

use Nette\Database\Explorer;
use App\Model\DayModel;

class TimeSlotModel extends BaseModel
{
    const TABLE_NAME = 'time_slots';
    const COLUMN_ID = 'id';
    const COLUMN_START_TIME = 'start_time';
    const COLUMN_END_TIME = 'end_time';
    const COLUMN_YEAR = 'year';
    const COLUMN_WEEK = 'week';
    const COLUMN_DAY = 'day';
    const COLUMN_MONTH = 'month';
    const COLUMN_DAY_OF_WEEK = 'day_of_week';
    const COLUMN_SHOW = 'show';
    const COLUMN_LEAP_YEAR = 'leap_year';
    const COLUMN_YEAR_ID = 'year_id';
    const COLUMN_MONTH_ID = 'month_id';
    const COLUMN_WEEK_ID = 'week_id';
    const COLUMN_DAY_ID = 'day_id';

    private DayModel $dayModel;
    private float $slotLength; // Délka slotu jako podíl dne

    public function __construct(Explorer $database, DayModel $dayModel)
    {
        parent::__construct($database);
        $this->dayModel = $dayModel;
        $this->slotLength = 1 / 48; // Představuje půl hodiny, tj. 1/48 dne
    }

    /**
     * Vytvoří časové sloty pro daný den a uloží je do databáze.
     */
    public function createSlotsForDay(int $dayId): array
    {
        $slotsData = $this->prepareSlotsDataForDay($dayId);
        return $this->addMultipleAndReturnIds($slotsData);
    }

    /**
     * Vytvoří časové sloty pro daný den bez vracení výsledku.
     */
    public function createSlotsForDayRaw(int $dayId): void
    {
        $slotsData = $this->prepareSlotsDataForDay($dayId);
        $this->database->table(self::TABLE_NAME)->insert($slotsData);
    }

    /**
     * Vytvoří časové sloty pro daný týden a uloží je do databáze.
     */
    public function createSlotsForWeek(int $weekId): array
    {
        $dayIds = $this->dayModel->getDaysByWeekId($weekId);
        $allSlotsIds = [];

        foreach ($dayIds as $dayId) {
            $slotsIds = $this->createSlotsForDay($dayId);
            $allSlotsIds = array_merge($allSlotsIds, $slotsIds);
        }

        return $allSlotsIds;
    }

    /**
     * Nastaví délku slotu.
     */
    public function setSlotLength(float $slotLength): void
    {
        $this->slotLength = $slotLength;
    }

    /**
     * Přidá všechny časové sloty pro daný rok, měsíc a týden.
     */
    public function createSlotsForYearMonthWeek(int $yearId, int $monthId, int $weekId): array
    {
        $year = $this->yearModel->getById($yearId)->year_number;
        $month = $this->monthModel->getById($monthId)->number_show;

        $dayIds = $this->dayModel->getDaysForMonthAndYear($month, $year);
        $allSlotsIds = [];

        foreach ($dayIds as $dayId) {
            $slotsIds = $this->createSlotsForDay($dayId);
            $allSlotsIds = array_merge($allSlotsIds, $slotsIds);
        }

        return $allSlotsIds;
    }

    /**
     * Připraví data slotů pro daný den.
     */
    private function prepareSlotsDataForDay(int $dayId): array
    {
        $dayData = $this->dayModel->getById($dayId);
        if (!$dayData) {
            throw new \Exception("Day not found in database.");
        }

        $slotsData = [];
        $startOfDay = new \DateTime($dayData->year_number . '-' . $dayData->month_number_show . '-' . $dayData->number_show . ' 00:00:00');
        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        while ($startOfDay < $endOfDay) {
            $endTime = clone $startOfDay;
            $endTime->modify('+' . ($this->slotLength * 24 * 60) . ' minutes');

            $slotsData[] = [
                self::COLUMN_START_TIME => $startOfDay->format('Y-m-d H:i:s'),
                self::COLUMN_END_TIME => $endTime->format('Y-m-d H:i:s'),
                self::COLUMN_YEAR => $dayData->year_number,
                self::COLUMN_WEEK => $dayData->week_number_show,
                self::COLUMN_DAY => $dayData->number_show,
                self::COLUMN_MONTH => $dayData->month_number_show,
                self::COLUMN_DAY_OF_WEEK => $dayData->day_from_week,
                self::COLUMN_SHOW => $startOfDay->format('H:i') . ' - ' . $endTime->format('H:i'),
                self::COLUMN_LEAP_YEAR => $dayData->leap_year,
                self::COLUMN_YEAR_ID => $dayData->year_id,
                self::COLUMN_MONTH_ID => $dayData->month_id,
                self::COLUMN_WEEK_ID => $dayData->week_id,
                self::COLUMN_DAY_ID => $dayId,
            ];

            $startOfDay = $endTime;
        }

        return $slotsData;
    }
    
    public function getTimeSlotsForDay(int $dayId): array
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_DAY_ID, $dayId)
            ->fetchAll(); // Vrací pole objektů
    }
    
     // Nová metoda pro získání časových slotů podle weekId
    public function getTimeSlotsForWeek(int $weekId): array
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_WEEK_ID, $weekId)
            ->fetchAll(); // Vrací pole objektů
    }

    // Nová metoda pro získání časových slotů podle monthId
    public function getTimeSlotsForMonth(int $monthId): array
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_MONTH_ID, $monthId)
            ->fetchAll(); // Vrací pole objektů
    }
}
