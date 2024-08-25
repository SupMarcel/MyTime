<?php

declare(strict_types=1);

namespace App\Module\Worker\Presenters;

use Nette\Application\UI\Presenter;
use App\Service\CalendarDataService;

final class WorkerCalendarPresenter extends Presenter
{
    private CalendarDataService $calendarDataService;

    public function __construct(CalendarDataService $calendarDataService)
    {
        $this->calendarDataService = $calendarDataService;
    }

    // Metoda pro přidání kalendářních dat
    public function actionAddData(): void
    {
        $this->calendarDataService->checkAndAddCalendarData();
        $this->flashMessage('Calendar data successfully added.', 'success');
        $this->redirect(':Common:Homepage:default');
    }

    // Metoda pro vymazání kalendářních dat
    public function actionClearData(): void
    {
        $this->calendarDataService->clearCalendarData();
        $this->flashMessage('Calendar data successfully cleared.', 'success');
        $this->redirect(':Common:Homepage:default');
    }
}
