<?php

namespace App\Components\Form;
use App\Model\Facade\CompanyProccessFacade;

/**
 * Control
 */
abstract class StepForm extends \App\Components\Control
{
	/** @var CompanyProccessFacade */
	protected $proccessFacade;
	
	public function __construct(CompanyProccessFacade $facade)
	{
		parent::__construct();
		$this->proccessFacade = $facade;
	}
}
