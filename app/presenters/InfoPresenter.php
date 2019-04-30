<?php

namespace App\Presenters;

use App\Model,
	Nette,
	Nette\Application\UI\Form,
	Nette\Utils\Strings,
	Nette\Utils\Image;

/**
 * Homepage presenter.
 */
class InfoPresenter extends BasePresenter
{

	private $id;
	
	/** @var \App\Model\AccountancyService @inject */
	public $accountancyService;

	public function __construct()
	{
		parent::__construct();
	}


	public function renderCookiePolicy()
	{
		
	}
	
	public function renderAccountancy()
	{
		$this->template->accountancy1 = $this->accountancyService->getServicesByCategory(1);
		$this->template->accountancy2 = $this->accountancyService->getServicesByCategory(2);
	}

	protected function createComponentTaxForm($name)
	{
		$form = new Form;
		$form->addRadioList("accountancyServices", "", array("x" => "Žádný") + $this->accountancyService->findAll()->fetchPairs("id", "name"))->setRequired("Vyberte prosím balíček");
		$form->addRadioList("vat", "", array(1 => "Ano", 0 => "Ne"))->setDefaultValue(0);
		$form->addText("number_cars")->setDefaultValue(0);
		$form->addText("number_employees")->setDefaultValue(0);
		return $form;
	}

}
