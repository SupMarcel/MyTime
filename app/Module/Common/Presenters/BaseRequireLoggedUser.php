<?php

declare(strict_types=1);

namespace App\Module\Common\Presenters;


trait BaseRequireLoggedUser
{
	public function injectRequireLoggedUser(): void
	{
		$this->onStartup[] = function () {
			if (!$this->getUser()->isLoggedIn()) {
				$this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
			}
		};
	}
}
