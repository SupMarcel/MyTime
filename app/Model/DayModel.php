<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Localization\ITranslator;

class DayModel extends BaseModelWithTranslator
{
    // Konstanty pro názvy sloupců tabulky 'days'
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

    // Konstanty pro správu datového rozsahu
    const DAYS_RANGE = 13; // Počet dní od aktuálního data

    private YearModel $yearModel;
    private MonthModel $monthModel;
    private WeekModel $weekModel;

    protected \DateTime $currentDate;
    protected \DateTime $endDate;

    public function __construct(Explorer $database, ITranslator $translator, YearModel $yearModel, MonthModel $monthModel, WeekModel $weekModel)
    {
        parent::__construct($database, $translator);
        $this->yearModel = $yearModel;
        $this->monthModel = $monthModel;
        $this->weekModel = $weekModel;
        $this->initializeDates(); // Inicializace dat
    }

    // Inicializace třídních proměnných pro aktuální datum a koncové datum
    private function initializeDates(): void
    {
        $this->currentDate = new \DateTime(); // Nastaví aktuální datum
        $this->endDate = (clone $this->currentDate)->modify('+' . self::DAYS_RANGE . ' days'); // Nastaví datum o určitou hodnotu dní v budoucnu
    }

    /**
    * Získá všechny dny od aktuálního data do endDate (včetně).
    *
    * @return Nette\Database\Table\ActiveRow[] Pole objektů představujících dny.
    */
    public function getDaysFromCurrentToEnd(): array
    {
        // Vyhledání všech dní, které spadají do zadaného rozmezí dat
        $days = $this->database->table(self::TABLE_NAME)
            ->where('DATE(CONCAT(' . self::COLUMN_YEAR_NUMBER . ', "-", ' . self::COLUMN_MONTH_NUMBER_SHOW . ', "-", ' . self::COLUMN_NUMBER_SHOW . ')) BETWEEN ? AND ?', $this->currentDate->format('Y-m-d'), $this->endDate->format('Y-m-d'))
            ->fetchAll();

        return $days;
    }

    /**
    * Generuje dny a týdny pro dané měsíce a přiřazuje je k odpovídajícím měsícům v databázi.
    *
    * Tato metoda iteruje přes všechny dny v zadaných měsících, generuje týdny podle potřeby,
    * přiřazuje dny k těmto týdnům a vytváří relace mezi měsíci a týdny. Na závěr metoda vrací
    * pole ID nově přidaných dnů.
    *
    * @param array $months Pole objektů měsíců.
    * @return array Pole ID nově přidaných dnů.
    */
    public function generateDaysAndWeeksForMonths(array $months): array
    {
        $daysData = [];
        $weekModel = $this->weekModel;

        $currentWeekNumber = 1; // Udržuje číslo týdne napříč všemi měsíci
        $weekId = null;
        $lastDayName = null; // Sledování názvu posledního dne

        foreach ($months as $month) {
            $yearId = $month->{MonthModel::COLUMN_YEAR_ID};
            $year = $month->{MonthModel::COLUMN_YEAR};
            $monthId = $month->{MonthModel::COLUMN_ID};
            $daysInMonth = $month->{MonthModel::COLUMN_DAYS_IN_MONTH};

            for ($dayNumber = 1; $dayNumber <= $daysInMonth; $dayNumber++) {
                $date = \DateTime::createFromFormat('Y-m-d', "$year-{$month->{MonthModel::COLUMN_NUMBER_SHOW}}-" . str_pad($dayNumber, 2, '0', STR_PAD_LEFT));
                $dayName = $date->format('l');

                // Pokud je to pondělí nebo první den v roce, vytvoříme nový týden
                if ($dayName == 'Monday' || ($dayNumber == 1 && $lastDayName === null)) {
                    $weekNumberShow = str_pad($currentWeekNumber, 2, '0', STR_PAD_LEFT);
                    $weekId = $weekModel->addWeekAndMonthRelation($year, $yearId, $monthId, $weekNumberShow);
                    $currentWeekNumber++;
                }

                $daysData[] = [
                    self::COLUMN_YEAR_NUMBER => $year,
                    self::COLUMN_MONTH_NUMBER_SHOW => $month->{MonthModel::COLUMN_NUMBER_SHOW},
                    self::COLUMN_MONTH_VERBAL_SHOW => $month->{MonthModel::COLUMN_VERBAL_SHOW},
                    self::COLUMN_WEEK_NUMBER_SHOW => $weekNumberShow,
                    self::COLUMN_DAY_FROM_WEEK => $dayName,
                    self::COLUMN_DAY_FROM_WEEK_SHORT => substr($dayName, 0, 2),
                    self::COLUMN_NUMBER_SHOW => str_pad($dayNumber, 2, '0', STR_PAD_LEFT),
                    self::COLUMN_LEAP_YEAR => $month->{MonthModel::COLUMN_LEAP_YEAR},
                    self::COLUMN_YEAR_ID => $yearId,
                    self::COLUMN_MONTH_ID => $monthId,
                    self::COLUMN_WEEK_ID => $weekId,
                ];

                $lastDayName = $dayName; // Aktualizujeme poslední název dne
            }
        }

        return $this->addMultipleAndReturnIds($daysData);
    }
}

