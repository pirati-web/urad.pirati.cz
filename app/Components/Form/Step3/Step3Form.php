<?php

namespace App\Components\Form\Step3;

use Nette\Application\UI\Form;
use App\Components\Control;
use App\Model;

/**
 * Step3Form
 */
class Step3Form extends Control
{
	public $onSuccess = array();
	public $onCancel = array();
	
	/** @var Model\City */
	protected $city;
	
	/** @var Model\SeatService */
	protected $seatService;
	
	/** @var Model\SeatServiceTarif */
	protected $seatServiceTarif;
	
	/** @var Model\PostService */
	protected $postService;
	
	/** @var Model\PhoneService */
	protected $phoneService;
	
	public function __construct(Model\City $city,
	 							Model\SeatService $seatService,
	  							Model\SeatServiceTarif $tarif,
								Model\PostService $postService,
	    						Model\PhoneService $phoneService)
	{
		parent::__construct();
		$this->city = $city;
		$this->seatService = $seatService;
		$this->seatServiceTarif = $tarif;
		$this->postService = $postService;
		$this->phoneService = $phoneService;
	}

	protected function createComponentForm($name)
	{
		$form = new Form;
		$form->addRadioList("have_seat","",array(0 => "chci sidlo", 1 => "mam sidlo"))->setDefaultValue(1);
		$form->addText('street', "Ulice*")
			->addConditionOn($form["have_seat"], Form::EQUAL, 1)
				->setRequired("Zadejte ulici");
		$form->addText('street_number', 'Číslo domu*')
			->addConditionOn($form["have_seat"], Form::EQUAL, 1)
				->setRequired("Zadjte číslo domu");
		$form->addText('city_part', "Část obce");
		$form->addText('city', "Město*")
			->addConditionOn($form["have_seat"], Form::EQUAL, 1)
				->setRequired("Zadejte město");
		$form->addText('zip_code', 'PSČ*')
			->addConditionOn($form["have_seat"], Form::EQUAL, 1)
				->setRequired("Zadejte PSČ")
				->addRule(Form::PATTERN, 'PSČ musí mít 5 číslic', '([0-9]\s*){5}');
		$form->addText('state', 'Stát*')
			->addConditionOn($form["have_seat"], Form::EQUAL, 1)
				->setRequired("Zadejte stát");
		$form->addText('owner_name', 'Jména majitelů*')
			->addConditionOn($form["have_seat"], Form::EQUAL, 1)
				->setRequired("Zadejte jména majitelů");
		
		
		$services = $this->seatService->findAll()->where("active = 1");
		$toSelect = array();
		foreach ($services as $service) {
			$toSelect[$service->id] = \Nette\Utils\Html::el()->setHtml($service->name . " <b>" . $service->price_12 . " Kč / měsíc</b>");
		}

		$form->addRadioList("seatService", "", $toSelect)
			->setDefaultValue(2)
			->addConditionOn($form["have_seat"], Form::EQUAL, 0)
				->setRequired("Vyberte prosím adresu sídla");
		
		$tarifs = $this->seatServiceTarif->findAll()->where("active = 1");
		$toSelect = array();
		foreach ($tarifs as $tarif) {
			$toSelect[$tarif->id] = $tarif->months . " měsíců";
			if ($tarif->discount) {
				$toSelect[$tarif->id] .= " (".$tarif->discount." % sleva)";
			}
		}
		
		$form->addRadioList("seatServiceTarif", "", $toSelect)
			->setDefaultValue(18)
			->addConditionOn($form["have_seat"], Form::EQUAL, 0)
				->setRequired("Vyberte prosím tarif");
		
		$posts = $this->postService->findAll()->where("active = 1")->fetchAssoc("id");
		$toSelect = array();
		foreach ($posts as $post) {
			$toSelect[$post["id"]] = $post["name"] . " ". $post["price"] . " kč / měsíc";
		}
		
		$form->addRadioList("postService", "", $toSelect)
			->setDefaultValue(1)
			->addConditionOn($form["have_seat"], Form::EQUAL, 0)
				->setRequired("Vyberte prosím poštovní služby");
		
		$phones = $this->phoneService->findAll()->where("active = 1")->fetchAssoc("id");
		$toSelect = array();
		foreach ($phones as $phone) {
			$toSelect[$phone["id"]] = $phone["name"] . " ". $phone["price"] . " kč / měsíc";
		}
		
		$form->addRadioList("phoneService", "", $toSelect)
			->setDefaultValue(1)
			->addConditionOn($form["have_seat"], Form::EQUAL, 0)
				->setRequired("Vyberte prosím telefonní službu");
		
		
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
		$this->onSuccess($button);
	}

	public function formCancelled($data)
	{
		$this->onCancel($data);
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/default.latte");
		$this->template->city = $this->city->findAll();
		$this->template->phoneServiceContent = $this->phoneService->findAll()->where("active = 1")->fetchAssoc("id");
		$this->template->postServiceContent = $this->postService->findAll()->where("active = 1")->fetchAssoc("id");
		$this->template->seatServiceContent = $this->seatService->findAll()->where("active = 1")->fetchAssoc("id");
		$this->template->tarifServiceContent = $this->seatServiceTarif->findAll()->where("active = 1")->fetchAssoc("id");
		$this->template->render();
	}

}

interface IStep3FormFactory
{

	/**
	 * @return Step3Form
	 */
	public function create();
}
