<?php
namespace App\Service;

use App\Model\BaseModelWithTranslator;
use App\Model\LocationModel;
use App\Model\DayModel;
use App\Model\WeekModel;
use App\Model\MonthModel;
use App\Model\UserModel;
use App\Model\WorkerModel;
use Nette\Localization\ITranslator;

class RawWorkerCalendar extends BaseModelWithTranslator
{
    private DayModel $dayModel;
    private WeekModel $weekModel;
    private MonthModel $monthModel;
    private UserModel $userModel;
    private WorkerModel $workerModel;
    protected ITranslator $translator;

    public function __construct(
        DayModel $dayModel,
        WeekModel $weekModel,
        MonthModel $monthModel,
        UserModel $userModel,
        WorkerModel $workerModel,
        ITranslator $translator
    ) {
        $this->dayModel = $dayModel;
        $this->weekModel = $weekModel;
        $this->monthModel = $monthModel;
        $this->userModel = $userModel;
        $this->workerModel = $workerModel;
        $this->translator = $translator;
    }
    
    public function getDaysMap(): array
    {
        return [
            'mo' => $this->translator->translate('messages.days.mo'),
            'tu' => $this->translator->translate('messages.days.tu'),
            'we' => $this->translator->translate('messages.days.we'),
            'th' => $this->translator->translate('messages.days.th'),
            'fr' => $this->translator->translate('messages.days.fr'),
            'sa' => $this->translator->translate('messages.days.sa'),
            'su' => $this->translator->translate('messages.days.su'),
        ];
    }

    public function generateCalendarStructure(int $userId): array
    {
        $calendar = [];

        // Získání uživatele a jeho lokace
        $user = $this->userModel->getById($userId);
        $location = $this->workerModel->getWorkerLocation($userId);

        if (!$user || !$location) {
            throw new \Exception("User or location not found.");
        }

        // Přeložení titulku pro kalendář (rozložený na dvě části)
        $calendar['calendar_title_main'] = sprintf($this->translator->translate('messages.calendar.header.main'), $user->{UserModel::COLUMN_USERNAME}, $location->{LocationModel::COLUMN_NAME});
        $calendar['calendar_title_dates'] = sprintf($this->translator->translate('messages.calendar.header.dates'), 
            (new \DateTime())->format('d.m.Y'),
            (clone (new \DateTime()))->modify('+13 days')->format('d.m.Y')
        );

        // Inicializace klíče 'months'
        $calendar['months'] = [];

        // Získání všech relevantních měsíců ve sledovaném období
        $months = $this->dayModel->getSortedMonthIds();
        // Iterace přes měsíce
        foreach ($months as $month) {
            // Přeložení názvu měsíce
            $monthName = $this->translator->translate('messages.dayModel.month.' . strtolower($this->monthModel->getMonthNameById($month)));

            // Inicializace pole pro konkrétní měsíc
            $calendar['months'][$monthName] = [];

            // Iterace přes týdny pro daný měsíc
            $weeks = $this->dayModel->getSortedWeekIdsForMonth($month);
            foreach ($weeks as $week) {
                // Přeložení názvu týdne
                $weekName = $this->translator->translate('messages.dayModel.week') . ' ' . $week;

                // Inicializace pole pro konkrétní týden v měsíci
                $calendar['months'][$monthName][$weekName] = [];

                // Získání všech relevantních dní pro daný týden a měsíc
                $days = $this->dayModel->getSortedDayIdsForMonthAndWeek($month, $week);

                // Iterace přes dny v týdnu a přidání do struktury kalendáře
                foreach ($days as $day) {
                    // Získání přeloženého formátu dne
                    $dayRow = $this->dayModel->getById($day);
                    $dayFormatted = $this->translator->translate('messages.dayModel.day.' . strtolower($dayRow->{DayModel::COLUMN_DAY_FROM_WEEK_SHORT})) . ' ' . $dayRow->{DayModel::COLUMN_NUMBER_SHOW} . '.' . str_pad($dayRow->{DayModel::COLUMN_MONTH_NUMBER_SHOW}, 2, '0', STR_PAD_LEFT) . '.';

                    // Přidání ID dne jako klíče a přeloženého dne jako hodnoty
                    $calendar['months'][$monthName][$weekName][$dayRow->{DayModel::COLUMN_ID}] = $dayFormatted;
                }
            }
        }

        return $calendar;
    }

}
