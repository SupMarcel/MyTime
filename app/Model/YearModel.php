<?php

declare(strict_types=1);

namespace App\Model;

final class YearModel extends BaseModel
{
    // Konstanty pro tabulku `years`
    public const TABLE_NAME = 'years';
    public const COLUMN_ID = 'id';
    public const COLUMN_YEAR_NUMBER = 'year_number';
    public const COLUMN_LEAP_YEAR = 'leap_year';
    
    // Pole přestupných roků
    private $leapYears = [2024, 2028, 2032, 2036, 2040];

    /**
     * Přidá nový rok do tabulky `years`.
     *
     * @param int $yearNumber
     * @param int $leapYear
     * @return ActiveRow
     */
    public function addYear(int $yearNumber): ActiveRow
    {
        // Zjistit, zda je rok přestupný
        $isLeapYear = in_array($yearNumber, $this->leapYears) ? 1 : 0;

        // Přidat nový záznam do tabulky years
        return $this->add([
            'year_number' => $yearNumber,
            'leap_year' => $isLeapYear,
        ]);
    }
    
     // Přidání dvou let
    public function addYears(int $currentYear, int $nextYear): void
    {
        // Přidat aktuální rok
        $this->addYear($currentYear);

        // Přidat následující rok
        $this->addYear($nextYear);
    }

    // Privátní metoda pro výpočet aktuálního a následujícího roku
    private function getYears(): array
    {
        $currentYear = (int)date('Y');
        $nextYear = $currentYear + 1;

        return [$currentYear, $nextYear];
    }

    // Volání metody addYears s výpočtem aktuálního a následujícího roku
    public function addCurrentAndNextYear(): void
    {
        [$currentYear, $nextYear] = $this->getYears();
        $this->addYears($currentYear, $nextYear);
    }
    
    // Přidání pouze následujícího roku
    public function addNextYear(): void
    {
        $nextYear = (int)date('Y') + 1;
        $this->addYear($nextYear);
    }
    
    public function checkAndAddYears(): array
    {
        $currentYear = (int)date('Y');
        $nextYear = $currentYear + 1;

        // Pokud je tabulka prázdná
        if ($this->isEmpty()) {
            $this->addYears($currentYear, $nextYear);
            return [$currentYear, $nextYear];
        }

        // Získejte poslední záznam v tabulce
        $lastYear = $this->getAll()->order(self::COLUMN_YEAR_NUMBER . ' DESC')->fetch();

        if ($lastYear->year_number < $currentYear) {
            // Pokud poslední rok v tabulce je menší než aktuální rok
            $this->addYears($currentYear, $nextYear);
            return [$currentYear, $nextYear];
        } elseif ($lastYear->year_number == $currentYear) {
            // Pokud poslední rok v tabulce je aktuální rok
            $this->addYear($nextYear);
            return [$nextYear];
        } elseif ($lastYear->year_number == $nextYear) {
            // Pokud poslední rok v tabulce je následující rok
            $secondLastYear = $this->getAll()->order(self::COLUMN_YEAR_NUMBER . ' DESC')->limit(1, 1)->fetch();
            
            if ($secondLastYear && $secondLastYear->year_number != $currentYear) {
                // Pokud předposlední záznam není aktuální rok
                if (!$this->findBy([self::COLUMN_YEAR_NUMBER => $currentYear])) {
                    $this->addYear($currentYear);
                    return [$currentYear];
                }
            }
        }

        // Pokud žádná z podmínek neplatí, neprovádí se žádná akce
        return [];
    }

    /**
     * Aktualizuje záznam v tabulce `years`.
     *
     * @param int $id
     * @param int $yearNumber
     * @param int $leapYear
     * @return void
     */
    public function updateYear(int $id, int $yearNumber, int $leapYear): void
    {
        $data = [
            self::COLUMN_YEAR_NUMBER => $yearNumber,
            self::COLUMN_LEAP_YEAR => $leapYear,
        ];

        $this->update($id, $data);
    }
}
