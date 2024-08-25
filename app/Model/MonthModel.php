<?php

namespace App\Model;

use Nette\Database\Explorer;

class MonthModel extends BaseModel
{
    const TABLE_NAME = 'months';

    const COLUMN_ID = 'id';
    const COLUMN_NUMBER_SHOW = 'number_show';
    const COLUMN_VERBAL_SHOW = 'verbal_show';
    const COLUMN_YEAR = 'year';
    const COLUMN_YEAR_ID = 'year_id';
    const COLUMN_LEAP_YEAR = 'leap_year';
    const COLUMN_DAYS_IN_MONTH = 'days_in_month';

    private YearModel $yearModel;

    public function __construct(Explorer $database, YearModel $yearModel)
    {
        parent::__construct($database);
        $this->yearModel = $yearModel;
    }

    /**
     * Přidání měsíců pro daný rok a vrácení pole objektů přidaných měsíců
     * 
     * @param int $year
     * @param int $yearId
     * @return array
     */
    public function addMonthsForYear(int $year, int $yearId): array
    {
        $monthsData = [
            ['number_show' => '01', 'verbal_show' => 'January', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('01', $year)],
            ['number_show' => '02', 'verbal_show' => 'February', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('02', $year)],
            ['number_show' => '03', 'verbal_show' => 'March', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('03', $year)],
            ['number_show' => '04', 'verbal_show' => 'April', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('04', $year)],
            ['number_show' => '05', 'verbal_show' => 'May', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('05', $year)],
            ['number_show' => '06', 'verbal_show' => 'June', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('06', $year)],
            ['number_show' => '07', 'verbal_show' => 'July', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('07', $year)],
            ['number_show' => '08', 'verbal_show' => 'August', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('08', $year)],
            ['number_show' => '09', 'verbal_show' => 'September', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('09', $year)],
            ['number_show' => '10', 'verbal_show' => 'October', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('10', $year)],
            ['number_show' => '11', 'verbal_show' => 'November', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('11', $year)],
            ['number_show' => '12', 'verbal_show' => 'December', 'year' => $year, 'year_id' => $yearId, 'leap_year' => $this->isLeapYear($year), 'days_in_month' => $this->getDaysInMonth('12', $year)],
        ];

        return $this->addMultipleAndReturnObjects($monthsData);
    }

    /**
     * Privátní metoda pro zjištění počtu dnů v měsíci
     * 
     * @param string $monthNumber
     * @param int $year
     * @return int
     */
    private function getDaysInMonth(string $monthNumber, int $year): int
    {
        $isLeapYear = $this->isLeapYear($year);

        switch ($monthNumber) {
            case '02':
                return $isLeapYear ? 29 : 28;
            case '04':
            case '06':
            case '09':
            case '11':
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Privátní metoda pro kontrolu, zda je rok přestupný
     * 
     * @param int $year
     * @return bool
     */
    private function isLeapYear(int $year): bool
    {
        return in_array($year, $this->yearModel->getLeapYears());
    }
}
