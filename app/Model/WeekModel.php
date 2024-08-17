<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Localization\ITranslator;

class WeekModel extends BaseModel
{
    const TABLE_NAME = 'weeks';
    const COLUMN_ID = 'id';

    private YearModel $yearModel;
    private ITranslator $translator;

    public function __construct(Explorer $database, YearModel $yearModel, ITranslator $translator)
    {
        parent::__construct($database);
        $this->yearModel = $yearModel;
        $this->translator = $translator;
    }

    // Metoda pro přidání týdnů pro daný rok
    public function addWeeksForYear(int $year, int $yearId): array
    {
        $weeksData = [];
        for ($weekNumber = 1; $weekNumber <= 52; $weekNumber++) {
            $weeksData[] = [
                'number_show' => str_pad($weekNumber, 2, '0', STR_PAD_LEFT),
                'year' => $year,
                'year_id' => $yearId,
                'leap_year' => in_array($year, $this->yearModel->getLeapYears()) ? 1 : 0,
            ];
        }

        return $this->addMultipleAndReturnIds($weeksData);
    }

    // Metoda pro přidání relací měsíců a týdnů
    public function addMonthWeekRelations(array $monthIds, array $weekIds): void
    {
        $relationsData = [];
        foreach ($monthIds as $monthId) {
            foreach ($weekIds as $weekId) {
                $relationsData[] = [
                    'month_id' => $monthId,
                    'week_id' => $weekId,
                ];
            }
        }
        $this->database->table('months_weeks')->insert($relationsData);
    }

    // Metoda pro získání ID tří po sobě jdoucích týdnů (včetně aktuálního)
    public function getThreeWeekIds(): array
    {
        $currentWeekNumber = (int) date('W');
        $currentYear = (int) date('Y');

        $currentWeek = $this->database->table(static::TABLE_NAME)
            ->where('number_show', str_pad($currentWeekNumber, 2, '0', STR_PAD_LEFT))
            ->where('year', $currentYear)
            ->fetch();

        if (!$currentWeek) {
            throw new \Exception("Current week not found in database.");
        }

        $nextWeek = $this->database->table(static::TABLE_NAME)
            ->where('number_show', str_pad($currentWeekNumber + 1, 2, '0', STR_PAD_LEFT))
            ->where('year', $currentYear)
            ->fetch();

        $weekAfterNext = $this->database->table(static::TABLE_NAME)
            ->where('number_show', str_pad($currentWeekNumber + 2, 2, '0', STR_PAD_LEFT))
            ->where('year', $currentYear)
            ->fetch();

        if (!$nextWeek) {
            $nextWeek = $this->database->table(static::TABLE_NAME)
                ->where('number_show', str_pad(1, 2, '0', STR_PAD_LEFT))
                ->where('year', $currentYear + 1)
                ->fetch();
        }

        if (!$weekAfterNext) {
            $weekAfterNext = $this->database->table(static::TABLE_NAME)
                ->where('number_show', str_pad(2, 2, '0', STR_PAD_LEFT))
                ->where('year', $currentYear + 1)
                ->fetch();
        }

        return [
            $currentWeek->id,
            $nextWeek ? $nextWeek->id : null,
            $weekAfterNext ? $weekAfterNext->id : null,
        ];
    }

    // Nová metoda pro získání přeložených názvů tří po sobě jdoucích týdnů
    public function getTranslatedThreeWeeks(): array
    {
        $weekIds = $this->getThreeWeekIds();
        $translatedWeeks = [];

        foreach ($weekIds as $weekId) {
            $weekRecord = $this->getById($weekId);
            if ($weekRecord) {
                $weekNumber = $weekRecord->number_show;
                $translatedWeeks[] = $this->translator->translate('messages.week') . " " . $weekNumber . ".";
            }
        }

        return $translatedWeeks;
    }
}


