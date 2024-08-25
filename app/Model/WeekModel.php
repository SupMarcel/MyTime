<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Localization\ITranslator;
use Nette\Database\Table\ActiveRow;

class WeekModel extends BaseModelWithTranslator {

    // Konstanty pro názvy sloupců tabulky 'weeks'
    const TABLE_NAME = 'weeks';
    const COLUMN_ID = 'id';
    const COLUMN_NUMBER_SHOW = 'number_show';
    const COLUMN_YEAR = 'year';
    const COLUMN_YEAR_ID = 'year_id';
    const COLUMN_LEAP_YEAR = 'leap_year';

    private YearModel $yearModel;

    public function __construct(Explorer $database, ITranslator $translator, YearModel $yearModel) {
        parent::__construct($database, $translator);
        $this->yearModel = $yearModel;
    }

    /**
     * Přidá týden a vztah mezi měsícem a týdnem, pokud neexistuje.
     *
     * @param int $year Rok.
     * @param int $yearId ID roku v databázi.
     * @param int $monthId ID měsíce v databázi.
     * @param int $weekNumber Číslo týdne v roce.
     * @return int ID přidaného týdne.
     * @throws \Exception
     */
    public function addWeekAndMonthRelation(int $year, int $yearId, int $monthId, int $weekNumber): int {
        $weekId = $this->createOrRetrieveWeek($year, $yearId, $weekNumber);

        // Přidání vztahu mezi měsícem a týdnem, pokud neexistuje
        $this->checkOrInsertMonthWeekRelation($monthId, $weekId);

        return $weekId;
    }

    /**
     * Zkontroluje existenci týdne na základě roku, ID roku a čísla týdne a pokud neexistuje, vytvoří jej.
     *
     * @param int $year Rok.
     * @param int $yearId ID roku v databázi.
     * @param int $weekNumber Číslo týdne v roce.
     * @return int ID týdne.
     * @throws \Exception
     */
    private function createOrRetrieveWeek(int $year, int $yearId, int $weekNumber): int {
        // Zkontrolujeme, zda týden již existuje
        $existingWeek = $this->database->table(self::TABLE_NAME)
                ->where(self::COLUMN_YEAR, $year)
                ->where(self::COLUMN_YEAR_ID, $yearId)
                ->where(self::COLUMN_NUMBER_SHOW, str_pad($weekNumber, 2, '0', STR_PAD_LEFT))
                ->fetch();

        // Pokud týden existuje, vrátíme jeho ID
        if ($existingWeek) {
            return $existingWeek->id;
        }

        // Pokud týden neexistuje, vytvoříme jej
        $weekData = [
            self::COLUMN_NUMBER_SHOW => str_pad($weekNumber, 2, '0', STR_PAD_LEFT),
            self::COLUMN_YEAR => $year,
            self::COLUMN_YEAR_ID => $yearId,
            self::COLUMN_LEAP_YEAR => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0,
        ];

        // Vložíme nový záznam do tabulky weeks a vrátíme jeho ID
        $weekId = $this->addAndReturnId($weekData);

        // Ověření, zda bylo ID úspěšně vráceno
        if ($weekId <= 0) {
            throw new \Exception("Failed to create or retrieve week ID.");
        }

        return $weekId;
    }

    /**
     * Zkontroluje existenci relace mezi měsícem a týdnem, a pokud neexistuje, vloží ji.
     *
     * @param int $monthId ID měsíce v databázi.
     * @param int $weekId ID týdne v databázi.
     */
    private function checkOrInsertMonthWeekRelation(int $monthId, int $weekId): void {
        // Zkontrolujeme, zda relace mezi měsícem a týdnem již existuje
        $exists = $this->database->table('months_weeks')
                ->where('month_id', $monthId)
                ->where('week_id', $weekId)
                ->fetch();

        // Pokud relace neexistuje, vložíme ji
        if (!$exists) {
            $this->database->table('months_weeks')->insert([
                'month_id' => $monthId,
                'week_id' => $weekId,
            ]);
        }
    }

    

    /**
     * Vrátí ID tří po sobě jdoucích týdnů, včetně aktuálního.
     *
     * @return array Pole ID tří týdnů.
     * @throws \Exception
     */
    public function getThreeWeekIds(): array {
        $currentWeekNumber = (int) date('W');
        $currentYear = (int) date('Y');

        $currentWeek = $this->getWeekByNumberAndYear($currentWeekNumber, $currentYear);

        $nextWeek = $this->getNextWeek($currentWeekNumber, $currentYear);

        $weekAfterNext = $this->getNextWeek($currentWeekNumber + 1, $currentYear);

        return [
            $currentWeek->id,
            $nextWeek ? $nextWeek->id : null,
            $weekAfterNext ? $weekAfterNext->id : null,
        ];
    }

    /**
     * Získá týden na základě čísla týdne a roku.
     *
     * @param int $weekNumber Číslo týdne.
     * @param int $year Rok.
     * @return ActiveRow|null Týden.
     * @throws \Exception
     */
    private function getWeekByNumberAndYear(int $weekNumber, int $year): ?ActiveRow {
        $week = $this->database->table(static::TABLE_NAME)
                ->where(self::COLUMN_NUMBER_SHOW, str_pad($weekNumber, 2, '0', STR_PAD_LEFT))
                ->where(self::COLUMN_YEAR, $year)
                ->fetch();

        if (!$week) {
            throw new \Exception("Week not found for week number $weekNumber and year $year.");
        }

        return $week;
    }

    /**
     * Získá následující týden na základě aktuálního čísla týdne a roku.
     * Pokud aktuální rok skončí, začne nový rok.
     *
     * @param int $weekNumber Číslo aktuálního týdne.
     * @param int $year Rok.
     * @return ActiveRow|null Následující týden.
     * @throws \Exception
     */
    private function getNextWeek(int $weekNumber, int $year): ?ActiveRow {
        $nextWeek = $this->getWeekByNumberAndYear($weekNumber + 1, $year);

        if (!$nextWeek) {
            $nextWeek = $this->getWeekByNumberAndYear(1, $year + 1);
        }

        return $nextWeek;
    }

    /**
     * Vrátí přeložené názvy tří po sobě jdoucích týdnů, včetně aktuálního.
     *
     * @return array Pole přeložených názvů týdnů.
     */
    public function getTranslatedThreeWeeks(): array {
        $weekIds = $this->getThreeWeekIds();
        $translatedWeeks = [];

        foreach ($weekIds as $weekId) {
            $weekRecord = $this->getById($weekId);
            if ($weekRecord) {
                $weekNumber = $weekRecord->{self::COLUMN_NUMBER_SHOW};
                $translatedWeeks[] = $this->translate('messages.week') . " " . $weekNumber . ".";
            }
        }

        return $translatedWeeks;
    }
}
