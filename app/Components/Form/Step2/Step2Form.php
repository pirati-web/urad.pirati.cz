<?php

namespace App\Components\Form\Step2;

use Nette\Application\UI\Form;
use App\Model;

/**
 * Step2Form
 */
class Step2Form extends \App\Components\Form\StepForm
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
		$it = $this->fieldsCategory->findAll();
		$items = array();
		foreach ($it as $i) {
			$items[$i->id] = $i->field_name;
		}
		$form->addCheckboxList("cat", '', $items)
				->addRule(Form::FILLED, 'Vyberte aspoň jednu možnost');
		
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
		$this->proccessFacade->saveStep2Data($values);
		$this->onSuccess($button);
	}

	public function formCancelled($data)
	{
		$this->onCancel($data);
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/default.latte");
		$this["form"]->setDefaults($this->proccessFacade->getStep2Data());
		$this->template->category = $this->fieldsCategory->getGroups(); 
		$this->template->types = $this->fieldsCategory->getTypes(); 
		unset($this->template->types[5]);
		$groupView = $this->fieldsCategory->findAll();
		$typeView = $this->fieldsCategory->getTypeView();
		$templIt = array();
		$tempTypes = array();
		foreach ($groupView as $i) {
			$templIt[$i->id] = $i->groups;
		}
		foreach ($typeView as $t) {
			$tempTypes[$t->id] = array("type_id" => $t->type_id,
				"desc" => $t->desc, "type_name" => $t->name);
		}
		$this->template->itemTypes = $tempTypes;
		$this->template->it = $templIt;
		$this->template->render();
	}

}

interface IStep2FormFactory
{

	/**
	 * @return Step2Form
	 */
	public function create();
}
