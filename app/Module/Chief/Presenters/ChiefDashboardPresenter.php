<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use Nette;


final class ChiefDashboardPresenter extends Nette\Application\UI\Presenter
{
	use RequireLoggedUser;
}
