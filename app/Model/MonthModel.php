<?php

namespace App\Model;

use Nette\Database\Explorer;

class MonthModel extends BaseModel
{
    const TABLE_NAME = 'months';

    // Konstanty pro názvy sloupců
    const COLUMN_ID = 'id';
    const COLUMN_NUMBER_SHOW = 'number_show';
    const COLUMN_VERBAL_SHOW = 'verbal_show';
    const COLUMN_YEAR = 'year';
    const COLUMN_YEAR_ID = 'year_id';
    const COLUMN_LEAP_YEAR = 'leap_year';

    private YearModel $yearModel;

    public function __construct(Explorer $database, YearModel $yearModel)
    {
        parent::__construct($database);
        $this->yearModel = $yearModel;
    }

   public function addMonthsForYear(int $year, int $yearId): array
    {
        $monthsData = [
            ['number_show' => '01', 'verbal_show' => 'January', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '02', 'verbal_show' => 'February', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '03', 'verbal_show' => 'March', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '04', 'verbal_show' => 'April', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '05', 'verbal_show' => 'May', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '06', 'verbal_show' => 'June', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '07', 'verbal_show' => 'July', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '08', 'verbal_show' => 'August', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '09', 'verbal_show' => 'September', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '10', 'verbal_show' => 'October', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '11', 'verbal_show' => 'November', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
            ['number_show' => '12', 'verbal_show' => 'December', 'year' => $year, 'year_id' => $yearId, 'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0],
        ];

        return $this->addMultipleAndReturnIds($monthsData);
    }

}
