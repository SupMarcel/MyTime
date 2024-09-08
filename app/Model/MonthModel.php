<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

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

    public function addMonthsForYear(ActiveRow $year): array
    {
        // Získání potřebných informací z objektu YearModel
        $yearNumber = $year->{YearModel::COLUMN_YEAR_NUMBER};
        $yearId = $year->{YearModel::COLUMN_ID};
        $isLeapYear = $year->{YearModel::COLUMN_LEAP_YEAR};

        // Vytvoření dat pro měsíce
        $monthsData = [
            ['number_show' => '01', 'verbal_show' => 'January', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('01', $yearNumber)],
            ['number_show' => '02', 'verbal_show' => 'February', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('02', $yearNumber)],
            ['number_show' => '03', 'verbal_show' => 'March', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('03', $yearNumber)],
            ['number_show' => '04', 'verbal_show' => 'April', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('04', $yearNumber)],
            ['number_show' => '05', 'verbal_show' => 'May', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('05', $yearNumber)],
            ['number_show' => '06', 'verbal_show' => 'June', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('06', $yearNumber)],
            ['number_show' => '07', 'verbal_show' => 'July', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('07', $yearNumber)],
            ['number_show' => '08', 'verbal_show' => 'August', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('08', $yearNumber)],
            ['number_show' => '09', 'verbal_show' => 'September', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('09', $yearNumber)],
            ['number_show' => '10', 'verbal_show' => 'October', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('10', $yearNumber)],
            ['number_show' => '11', 'verbal_show' => 'November', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('11', $yearNumber)],
            ['number_show' => '12', 'verbal_show' => 'December', 'year' => $yearNumber, 'year_id' => $yearId, 'leap_year' => $isLeapYear, 'days_in_month' => $this->getDaysInMonth('12', $yearNumber)],
        ];

        // Vrácení pole objektů přidaných měsíců
        return $this->addMultipleAndReturnObjects($monthsData);
    }

    /**
     * Vrátí název měsíce na základě jeho ID.
     *
     * @param int $monthId
     * @return string
     */
    public function getMonthNameById(int $monthId): string
    {
        $month = $this->database->table(self::TABLE_NAME)
            ->select(self::COLUMN_VERBAL_SHOW)
            ->get($monthId);

        if (!$month) {
            throw new \Exception("Month with ID $monthId not found.");
        }

        return $month->{self::COLUMN_VERBAL_SHOW};
    }

    /**
     * Privátní metoda pro zjištění počtu dnů v měsíci.
     *
     * @param string $monthNumber
     * @param int $year
     * @return int
     */
    private function getDaysInMonth(string $monthNumber, int $year): int
    {
        // Použití $this->yearModel->isLeapYear pro kontrolu přestupného roku
        $isLeapYear = $this->yearModel->getLeapYears($year);

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


}

