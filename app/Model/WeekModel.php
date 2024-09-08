<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class WeekModel extends BaseModel
{
    // Konstanty pro názvy sloupců tabulky 'weeks'
    const TABLE_NAME = 'weeks';
    const COLUMN_ID = 'id';
    const COLUMN_NUMBER_SHOW = 'number_show';
    const COLUMN_YEAR = 'year';
    const COLUMN_YEAR_ID = 'year_id';
    const COLUMN_LEAP_YEAR = 'leap_year';

    public function __construct(Explorer $database)
    {
        parent::__construct($database);
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
    public function addWeekAndMonthRelation(int $year, int $yearId, int $monthId, int $weekNumber): int
    {
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
    private function createOrRetrieveWeek(int $year, int $yearId, int $weekNumber): int
    {
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
            self::COLUMN_LEAP_YEAR => $this->isLeapYear($year) ? 1 : 0,
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
    private function checkOrInsertMonthWeekRelation(int $monthId, int $weekId): void
    {
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
     * Kontrola, zda je rok přestupný.
     *
     * @param int $year
     * @return bool
     */
    private function isLeapYear(int $year): bool
    {
        return ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0;
    }
}

