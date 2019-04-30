<?php

namespace App\Components\Form\Step8;
use Nette\Application\UI\Form;
use App\Components\Control;
use App\Model;

/**
 * Step8Form
 */
class Step8Form extends Control
{
	public $onSuccess = array();
	public $onCancel = array();
	
	/** @var Model\Banks */
	protected $banks;
	
	public function __construct(Model\Banks $banks)
	{
		parent::__construct();
		$this->banks = $banks;
	}
	
	protected function createComponentForm($name)
	{
		$form = new Form;
		$banks = $this->banks->findAll()->order("position");
		$items = array();
		foreach ($banks as $bank) {
			$items[$bank->id] = $bank->name; 
		}
		$form->addRadioList('bank_number', "Kód banky", $items)
			->setRequired("Je nutné vybrat si banku.");
		$form->addHidden("compliance",0)->setDefaultValue(0);
		
		$form->addSubmit('next', 'další')
			->onClick[] = [$this, "formSucceeded"];
		$form->addSubmit('cancel', 'zpátky')
			->setValidationScope(FALSE)
			->onClick[] = [$this, "formCancelled"];
		$form->addProtection();
		return $form;
	}
	
	public function formSucceeded($data)
	{
		$this->onSuccess($data);
	}
	
	public function formCancelled($data)
	{
		$this->onCancel($data);
	}
	
	public function render()
	{
		$this->template->setFile(__DIR__ . "/default.latte");
		$this->template->render();
	}
}


interface IStep8FormFactory
{
	/**
	 * @return Step8Form
	 */
	public function create();
}