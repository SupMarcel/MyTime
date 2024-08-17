<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

class YearsModel extends BaseModel
{
    const TABLE_NAME = 'years';

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

    // Přidání nového roku do tabulky
    public function addYear(int $yearNumber): int
    {
        $leapYear = in_array($yearNumber, $this->leapYears) ? 1 : 0;
        return $this->addAndReturnId([
            'year_number' => $yearNumber,
            'leap_year' => $leapYear,
        ]);
    }

    // Přidání aktuálního a následujícího roku, vrací pole s ID nově přidaných záznamů
    public function addYears(int $startYear, int $endYear): array
    {
        $yearsData = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $leapYear = in_array($year, $this->leapYears) ? 1 : 0;
            $yearsData[] = [
                'year_number' => $year,
                'leap_year' => $leapYear,
            ];
        }
        return $this->addMultipleAndReturnIds($yearsData);
    }

    // Přidání následujícího roku, vrací ID přidaného záznamu
    public function addNextYear(): int
    {
        return $this->addYear($this->nextYear);
    }

    // Metoda pro kontrolu a případné přidání aktuálního a následujícího roku
    public function checkAndAddYears(): array
    {
        if ($this->isEmpty()) {
            return $this->addYears($this->currentYear, $this->nextYear);
        } else {
            $lastYearInDb = $this->getAll()->order('year_number DESC')->fetch()->year_number;

            if ($lastYearInDb < $this->currentYear) {
                return $this->addYears($this->currentYear, $this->nextYear);
            } elseif ($lastYearInDb === $this->currentYear) {
                return [$this->addNextYear()];
            } elseif ($lastYearInDb === $this->nextYear) {
                $secondLastYear = $this->getAll()->order('year_number DESC')->limit(1, 1)->fetch()->year_number;

                if ($secondLastYear !== $this->currentYear) {
                    if (!$this->findBy(['year_number' => $this->currentYear])) {
                        return [$this->addYear($this->currentYear)];
                    }
                }
            }
        }

        return [];
    }
}

