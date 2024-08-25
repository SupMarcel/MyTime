<?php

namespace App\Service;

use App\Model\BaseModelWithTranslator;
use App\Model\YearModel;
use App\Model\MonthModel;
use App\Model\WeekModel;
use App\Model\DayModel;
use App\Model\TimeSlotModel;
use Nette\Database\Explorer;
use Nette\Localization\ITranslator;

class CalendarDataService extends BaseModelWithTranslator
{
    protected YearModel $yearModel;
    protected MonthModel $monthModel;
    protected WeekModel $weekModel;
    protected DayModel $dayModel;
    protected TimeSlotModel $timeSlotModel;

    public function __construct(
        YearModel $yearModel,
        MonthModel $monthModel,
        WeekModel $weekModel,
        DayModel $dayModel,
        TimeSlotModel $timeSlotModel,
        Explorer $database,
        ITranslator $translator
    ) {
        parent::__construct($database, $translator);
        $this->yearModel = $yearModel;
        $this->monthModel = $monthModel;
        $this->weekModel = $weekModel;
        $this->dayModel = $dayModel;
        $this->timeSlotModel = $timeSlotModel;
    }

    /**
     * Kontroluje a případně přidává data do kalendáře (roky, měsíce, týdny, dny a časové sloty).
     */
    public function checkAndAddCalendarData(): void
    {
        // Získání nebo přidání let a jejich objektů
        $years = $this->yearModel->checkAndAddYears();

        foreach ($years as $year) {
            // Přidání měsíců pro daný rok a získání přidaných objektů měsíců
            $months = $this->monthModel->addMonthsForYear($year->{YearModel::COLUMN_YEAR_NUMBER}, $year->{YearModel::COLUMN_ID});

            // Generování dnů, týdnů a jejich vztahů
            $dayIds = $this->dayModel->generateDaysAndWeeksForMonths($months);

            // Přidání časových slotů pro všechny dny
            foreach ($dayIds as $dayId) {
                $this->timeSlotModel->createSlotsForDayRaw($dayId);
            }
        }
    }

    /**
     * Vymaže veškerá data z kalendářových tabulek v databázi.
     */
    public function clearCalendarData(): void
    {
        $this->timeSlotModel->deleteAll(); // Vymazání všech slotů
        $this->dayModel->deleteAll();       // Vymazání všech dnů
        $this->weekModel->deleteAll();      // Vymazání všech týdnů
        $this->database->table('months_weeks')->delete(); // Vymazání všech záznamů v tabulce months_weeks
        $this->monthModel->deleteAll();     // Vymazání všech měsíců
        $this->yearModel->deleteAll();      // Vymazání všech let
    }
}
