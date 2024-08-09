<?php

declare(strict_types=1);

namespace App\Module\Common\Presenters;

use Nette;


final class BaseDashboardPresenter extends Nette\Application\UI\Presenter
{
	use BaseRequireLoggedUser;
}
