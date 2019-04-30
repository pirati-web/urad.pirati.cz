<?php

namespace App\Components\Form\Step1;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Step1Form
 */
class Step1Form extends \App\Components\Form\StepForm
{	
	public $onSuccess = array();
	public $onCancel = array();
	
	/** @var Model\FieldsCategory */
	protected $fieldsCategory;
	
	public function __construct(Model\Facade\CompanyProccessFacade $facade, Model\FieldsCategory $fieldsCategory)
	{
		parent::__construct($facade);
		$this->fieldsCategory = $fieldsCategory;
	}

	protected function createComponentForm($name)
	{
		$form = new Form;
		$form->addText("company", "")
				->addRule(Form::FILLED, "Musíte vyplnit název nové firmy");
		$form->addSelect("name_suf", "", array("s.r.o." => "s.r.o.",
					"spol. s r.o." => "spol. s r.o.",
					"společnost s ručením omezeným" => "společnost s ručením omezeným"))
				->addRule(Form::FILLED, "Je nutno vyplnit");
		$form->addHidden("responsibility");
		$form->addSubmit("search", "hledat")
				->onClick[] = [$this, "handleSearch"];
		$form->addSubmit("next", "další krok")
				->onClick[] = [$this, "formSucceeded"];
		$form->addProtection();
		return $form;
	}

	public function formSucceeded(\Nette\Forms\Controls\SubmitButton $button)
	{
		$values = $button->getForm()->getValues();
		$this->proccessFacade->saveStep1Data($values);
		$this->onSuccess($button);
	}
	
	/**
	 * duplicita v newcompanypresenter
	 * @param \Nette\Forms\Controls\SubmitButton $button
	 */
	public function handleSearch(\Nette\Forms\Controls\SubmitButton $button)
	{
		$values = $button->getForm()->getValues();
		try {
			$company = $this->presenter->parseJustice($values["company"]);
		} catch (\App\Presenters\JusticeLimitException $e) {
			$this->presenter->flashMessage("Byl vyčerpán limit dotazů pro dnešní den");
			$this->presenter->redirect("Homepage:");
		}
		$this->template->available = 1;
		if ($this->presenter->isAjax()) {
			$this->template->companyName = $company;
			$this->redrawControl("name");
			$this->redrawControl("new_name");
		}
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/default.latte");
		$this["form"]->setDefaults($this->proccessFacade->getStep1Data());
		$this->template->render();
	}

}

interface IStep1FormFactory
{

	/**
	 * @return Step1Form
	 */
	public function create();
}
