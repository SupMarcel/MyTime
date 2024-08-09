<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use Nette;


final class WorkerDashboardPresenter extends Nette\Application\UI\Presenter
{
	use RequireLoggedUser;
}
