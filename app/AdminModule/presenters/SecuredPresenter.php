<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

abstract class SecuredPresenter extends Nette\Application\UI\Presenter
{

	protected function startup()
	{
		parent::startup();		
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	protected function beforeRender()
	{
		parent::beforeRender();
	}

}
