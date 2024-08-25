<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class YearModel extends BaseModel
{
    const TABLE_NAME = 'years';
    const COLUMN_ID = 'id';
    const COLUMN_YEAR_NUMBER = 'year_number';
    const COLUMN_LEAP_YEAR = 'leap_year';

    // Přestupné roky na následujících 20 let
    private array $leapYears = [
        2024, 2028, 2032, 2036, 2040, 2044,
    ];

    // Třídní proměnné pro aktuální a následující rok
    private int $currentYear;
    private int $nextYear;

    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->initializeYears();
    }

    // Inicializace třídních proměnných
    private function initializeYears(): void
    {
        $this->currentYear = (int)date('Y');
        $this->nextYear = $this->currentYear + 1;
    }

    // Metoda pro získání přestupných roků
    public function getLeapYears(): array
    {
        return $this->leapYears;
    }

    // Přidání nového roku do tabulky a vrácení objektu
    public function addYear(int $yearNumber): ActiveRow
    {
        $leapYear = in_array($yearNumber, $this->leapYears) ? 1 : 0;
        return $this->add([
            self::COLUMN_YEAR_NUMBER => $yearNumber,
            self::COLUMN_LEAP_YEAR => $leapYear,
        ]);
    }

    // Přidání aktuálního a následujícího roku, vrací pole objektů
    public function addYears(int $startYear, int $endYear): array
    {
        $yearsData = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $leapYear = in_array($year, $this->leapYears) ? 1 : 0;
            $yearsData[] = [
                self::COLUMN_YEAR_NUMBER => $year,
                self::COLUMN_LEAP_YEAR => $leapYear,
            ];
        }
        return $this->addMultipleAndReturnObjects($yearsData);
    }

    // Přidání následujícího roku, vrací objekt přidaného záznamu
    public function addNextYear(): ActiveRow
    {
        return $this->addYear($this->nextYear);
    }

    // Metoda pro kontrolu a případné přidání aktuálního a následujícího roku
    public function checkAndAddYears(): array
    {
        if ($this->isEmpty()) {
            return $this->addYears($this->currentYear, $this->nextYear);
        } else {
            $lastYearInDb = $this->getAll()->order(self::COLUMN_YEAR_NUMBER . ' DESC')->fetch()->{self::COLUMN_YEAR_NUMBER};

            if ($lastYearInDb < $this->currentYear) {
                return $this->addYears($this->currentYear, $this->nextYear);
            } elseif ($lastYearInDb === $this->currentYear) {
                return [$this->addNextYear()];
            } elseif ($lastYearInDb === $this->nextYear) {
                $secondLastYear = $this->getAll()->order(self::COLUMN_YEAR_NUMBER . ' DESC')->limit(1, 1)->fetch()->{self::COLUMN_YEAR_NUMBER};

                if ($secondLastYear !== $this->currentYear) {
                    if (!$this->findBy([self::COLUMN_YEAR_NUMBER => $this->currentYear])) {
                        return [$this->addYear($this->currentYear)];
                    }
                }
            }
        }

        return [];
    }

    // Nová metoda pro kontrolu, zda jsou v tabulce aktuální a příští rok
    public function hasCurrentAndNextYear(): bool
    {
        $years = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_YEAR_NUMBER, [$this->currentYear, $this->nextYear])
            ->count();

        return $years === 2;
    }
}
