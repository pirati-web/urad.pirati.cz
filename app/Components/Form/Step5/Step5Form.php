<?php

/** ref */

namespace App\Components\Form\Step5;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Step5Form
 */
class Step5Form extends \App\Components\Form\StepForm
{	
	public $onSuccess = array();
	public $onCancel = array();
	
	/** @var \Company */
	protected $company;
	
	/** @var \Nette\Http\Session */
	protected $session;
	
	public function __construct($company, Model\Facade\CompanyProccessFacade $facade, \Nette\Http\Session $session)
	{
		parent::__construct($facade);
		$this->company = $company;
		$this->session = $session;
	}

	protected function createComponentForm($name)
	{
		$form = new Form;
		$form->addHidden('fractions');
		$form->addHidden('notar');
		$form->addCheckbox("messFractions");
		if ($this->company->getType() == "simple") {
			$form["messFractions"]->setDisabled();
		}
		$form->addHidden('errorCheck')
				->addRule(Form::EQUAL, "Věnujte pozornost chybám.", 0)
				->setRequired(false);
		$form->addText("value", "vklad")
				->addRule(Form::NUMERIC, "Je potřeba vyplnit číselnou hodnotu vkladu.")
				->addRule(Form::RANGE, "Základní kapitál musí být alespoň 1Kč za každého společníka.", array(($this->company->getPersons() ? count($this->company->getPersons()) : 1), NULL))
				->setRequired("Je potřeba vyplnit hodnotu vkladu");
		$form->addSubmit('next', 'další')
				->onClick[] = [$this, "formSucceeded"];
		$form->addSubmit('cancel', 'zpátky')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		$form->addProtection();
		return $form;
	}

	public function formSucceeded(\Nette\Forms\Controls\SubmitButton $button)
	{
		$values = $button->getForm()->getValues();
		$this->proccessFacade->saveStep5Data($values);
		$this->onSuccess($button);
	}

	public function formCancelled($data)
	{
		$this->onCancel($data);
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/default.latte");
		$data = $this->proccessFacade->getStep5Data();
		$this->template->data = $data;
		$this["form"]->setDefaults($data);
		$this->template->company = $this->company;
		$section = $this->session->getSection("users");
		if (!$section) {
			$this->template->users = 1;
		} else {
			$userCount = $section->userCount;
			$users = array();
			for ($i = 1; $i < $userCount; $i++) {
				$section = $this->session->getSection("4-" . $i);
				$u = array();
				foreach ($section as $key => $val) {
					$u[$key] = $val;
				}
				$users[] = $u;
			}
			$this->template->users = $this->company->getPersons(); 
		}
		$this->template->persons = $this->company->getPersons();
		$this->template->render();
	}
}

interface IStep5FormFactory
{

	/**
	 * @return Step5Form
	 */
	public function create($company);
}
