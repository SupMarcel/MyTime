<?php

namespace App\Model;

use Nette\Database\Explorer;
use App\Model\WeekModel;   // Přidání WeekModel
use App\Model\YearModel;   // Přidání YearModel
use App\Model\MonthModel;  // Přidání MonthModel

class DayModel extends BaseModel
{
    const TABLE_NAME = 'days';
    const COLUMN_ID = 'id';
    const COLUMN_YEAR_NUMBER = 'year_number';
    const COLUMN_MONTH_NUMBER_SHOW = 'month_number_show';
    const COLUMN_MONTH_VERBAL_SHOW = 'month_verbal_show';
    const COLUMN_WEEK_NUMBER_SHOW = 'week_number_show';
    const COLUMN_DAY_FROM_WEEK = 'day_from_week';
    const COLUMN_DAY_FROM_WEEK_SHORT = 'day_from_week_short';
    const COLUMN_NUMBER_SHOW = 'number_show';
    const COLUMN_LEAP_YEAR = 'leap_year';
    const COLUMN_YEAR_ID = 'year_id';
    const COLUMN_MONTH_ID = 'month_id';
    const COLUMN_WEEK_ID = 'week_id';

    protected \DateTime $currentDate;
    protected \DateTime $endDate;

    private WeekModel $weekModel;
    private YearModel $yearModel;
    private MonthModel $monthModel;

    public function __construct(Explorer $database, WeekModel $weekModel, YearModel $yearModel, MonthModel $monthModel)
    {
        parent::__construct($database);
        $this->weekModel = $weekModel;
        $this->yearModel = $yearModel;
        $this->monthModel = $monthModel;
        $this->initializeDates();
    }

    private function initializeDates(): void
    {
        $this->currentDate = new \DateTime();
        $this->endDate = (clone $this->currentDate)->modify('+13 days');
    }

    /**
     * Získá všechna unikátní `month_id` v období mezi `currentDate` a `endDate`.
     *
     * @return array Seřazené pole `month_id`.
     */
    public function getSortedMonthIds(): array
    {        
        $months = $this->database->table(self::TABLE_NAME)
        ->select('DISTINCT ' . self::COLUMN_MONTH_ID)
        ->where('DATE(CONCAT(' . self::COLUMN_YEAR_NUMBER . ', "-", ' . self::COLUMN_MONTH_ID . ', "-", ' . self::COLUMN_NUMBER_SHOW . ')) BETWEEN ? AND ?',
               $this->currentDate->format('Y-m-d'), $this->endDate->format('Y-m-d'))
        ->order(self::COLUMN_MONTH_ID . ' ASC')
        ->fetchPairs(null, self::COLUMN_MONTH_ID);

            return array_values($months);
    }

    /**
     * Získá všechna unikátní `week_id` pro daný `month_id` v období mezi `currentDate` a `endDate`.
     *
     * @param int $monthId
     * @return array Seřazené pole `week_id`.
     */
    public function getSortedWeekIdsForMonth(int $monthId): array
    {
        $weeks = $this->database->table(self::TABLE_NAME)
            ->select('DISTINCT ' . self::COLUMN_WEEK_ID)
            ->where(self::COLUMN_MONTH_ID, $monthId)
            ->where('DATE(CONCAT(' . self::COLUMN_YEAR_NUMBER . ', "-", ' . self::COLUMN_MONTH_ID . ', "-", ' . self::COLUMN_NUMBER_SHOW . ')) BETWEEN ? AND ?',
                    $this->currentDate->format('Y-m-d'), $this->endDate->format('Y-m-d'))
            ->order(self::COLUMN_WEEK_ID . ' ASC')
            ->fetchPairs(null, self::COLUMN_WEEK_ID);

        return array_values($weeks);
    }

    /**
     * Získá všechna unikátní `id` pro daný `month_id` a `week_id` v období mezi `currentDate` a `endDate`.
     *
     * @param int $monthId
     * @param int $weekId
     * @return array Seřazené pole `id`.
     */
    public function getSortedDayIdsForMonthAndWeek(int $monthId, int $weekId): array
    {
        $days = $this->database->table(self::TABLE_NAME)
            ->select(self::COLUMN_ID)
            ->where(self::COLUMN_MONTH_ID, $monthId)
            ->where(self::COLUMN_WEEK_ID, $weekId)
            ->where('DATE(CONCAT(' . self::COLUMN_YEAR_NUMBER . ', "-", ' . self::COLUMN_MONTH_ID . ', "-", ' . self::COLUMN_NUMBER_SHOW . ')) BETWEEN ? AND ?',
                    $this->currentDate->format('Y-m-d'), $this->endDate->format('Y-m-d'))
            ->order(self::COLUMN_ID . ' ASC')
            ->fetchPairs(null, self::COLUMN_ID);

        return array_values($days);
    }
    
    
    public function generateDaysAndWeeksForMonths(array $months): array {
    // Inicializace pole, které bude obsahovat data pro každý den
    $daysData = [];

    // Ukládáme instanci modelu pro týdny
    $weekModel = $this->weekModel;

    // Udržujeme si aktuální číslo týdne, které se bude zvyšovat každý týden
    $currentWeekNumber = 1;

    // Proměnná, která bude obsahovat ID aktuálního týdne
    $weekId = null;

    // Proměnná, která bude sledovat poslední název dne (pro rozhodování o týdnech)
    $lastDayName = null;

    // Iterujeme přes všechny měsíce, které byly předány jako parametry
    foreach ($months as $month) {
        // Načítáme informace o aktuálním měsíci, jako je rok, ID roku, ID měsíce a počet dnů v měsíci
        $yearId = $month->{MonthModel::COLUMN_YEAR_ID};
        $year = $month->{MonthModel::COLUMN_YEAR};
        $monthId = $month->{MonthModel::COLUMN_ID};
        $daysInMonth = $month->{MonthModel::COLUMN_DAYS_IN_MONTH};

        // Iterujeme přes každý den v aktuálním měsíci
        for ($dayNumber = 1; $dayNumber <= $daysInMonth; $dayNumber++) {
            // Vytvoříme datum pro daný den v měsíci, např. 2024-03-01
            $date = \DateTime::createFromFormat('Y-m-d', "$year-{$month->{MonthModel::COLUMN_NUMBER_SHOW}}-" . str_pad($dayNumber, 2, '0', STR_PAD_LEFT));

            // Získáme název dne (např. "Monday", "Tuesday", atd.)
            $dayName = $date->format('l');

            // Podmínka pro vytvoření nového týdne:
            // a) Pokud je to pondělí, začíná nový týden
            // b) Pokud je to první den měsíce a dosud jsme nezpracovali žádný den (první den v roce)
            if ($dayName == 'Monday' || ($dayNumber == 1 && $lastDayName === null)) {
                // Vygenerujeme číselné označení týdne, např. "01", "02" atd.
                $weekNumberShow = str_pad($currentWeekNumber, 2, '0', STR_PAD_LEFT);

                // Přidáme vztah mezi týdnem a měsícem a získáme ID týdne
                $weekId = $weekModel->addWeekAndMonthRelation($year, $yearId, $monthId, $weekNumberShow);

                // Zvyšujeme číslo týdne pro další iterace
                $currentWeekNumber++;
            }

            // Přidáváme informace o aktuálním dni do pole $daysData
            $daysData[] = [
                // Číslo roku
                self::COLUMN_YEAR_NUMBER => $year,
                // Číselné zobrazení měsíce (např. "03" pro březen)
                self::COLUMN_MONTH_NUMBER_SHOW => $month->{MonthModel::COLUMN_NUMBER_SHOW},
                // Slovní zobrazení měsíce (např. "March")
                self::COLUMN_MONTH_VERBAL_SHOW => $month->{MonthModel::COLUMN_VERBAL_SHOW},
                // Číslo týdne (např. "01", "02")
                self::COLUMN_WEEK_NUMBER_SHOW => $weekNumberShow,
                // Název dne v týdnu (např. "Monday", "Tuesday")
                self::COLUMN_DAY_FROM_WEEK => $dayName,
                // Zkrácený název dne v týdnu (např. "Mo" pro pondělí)
                self::COLUMN_DAY_FROM_WEEK_SHORT => substr($dayName, 0, 2),
                // Číslo dne v měsíci, např. "01", "02"
                self::COLUMN_NUMBER_SHOW => str_pad($dayNumber, 2, '0', STR_PAD_LEFT),
                // Příznak, zda je rok přestupný
                self::COLUMN_LEAP_YEAR => $month->{MonthModel::COLUMN_LEAP_YEAR},
                // ID roku, ID měsíce a ID týdne, ke kterému daný den patří
                self::COLUMN_YEAR_ID => $yearId,
                self::COLUMN_MONTH_ID => $monthId,
                self::COLUMN_WEEK_ID => $weekId,
            ];

            // Ukládáme aktuální název dne pro další iteraci (pro rozhodnutí o týdnu)
            $lastDayName = $dayName;
        }
    }

    // Vracíme pole obsahující ID všech přidaných dní
    return $this->addMultipleAndReturnIds($daysData);
}

    
    public function getNumberOfCalendarColumns(): int
    {
        // Získání všech různých month_id ve sledovaném období
        $monthIds = $this->getSortedMonthIds();

        $totalColumns = 0;

        // Iterace přes všechny měsíce a spočítání počtu týdnů
        foreach ($monthIds as $monthId) {
            // Získání všech různých week_id pro daný měsíc ve sledovaném období
            $weekIds = $this->getSortedWeekIdsForMonth($monthId);

            // Přičtení počtu týdnů k celkovému počtu sloupců
            $totalColumns += count($weekIds);
        }

        return $totalColumns;
    }
    
    /**
    * Vrátí ID dne na základě zadaného data (nebo aktuálního dne, pokud není zadáno).
    *
    * @param \DateTime|null $date Datum, pro které chceme najít ID dne (nepovinné, pokud není zadáno, použije se aktuální datum).
    * @return int|null ID dne nebo null, pokud den nebyl nalezen.
    */
    public function getDayIdByDate(?\DateTime $date = null): ?int
    {
        // Pokud nebylo zadáno datum, použijeme aktuální datum
        if ($date === null) {
            $date = $this->currentDate;
        }

        // Vyhledání ID dne na základě roku, měsíce a čísla dne v měsíci
        $dayRecord = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_YEAR_NUMBER, $date->format('Y'))
            ->where(self::COLUMN_MONTH_NUMBER_SHOW, $date->format('m'))
            ->where(self::COLUMN_NUMBER_SHOW, $date->format('d'))
            ->fetch();

        // Pokud byl den nalezen, vrátíme jeho ID, jinak vracíme null
        return $dayRecord ? $dayRecord->{self::COLUMN_ID} : null;
    }



}
