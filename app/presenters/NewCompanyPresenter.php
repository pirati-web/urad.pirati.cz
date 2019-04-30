<?php

namespace App\Presenters;

use App\Model,
	Nette,
	Nette\Application\UI\Form,
	Nette\Utils\Strings,
	Nette\Mail\Message,
	Nette\Mail\SendmailMailer,
	Nette\Utils\Image;
use Joseki\Application\Responses\PdfResponse;
use Company;
use App\Components\Form as StepForm;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class NewCompanyPresenter extends BasePresenter
{

	/** @var Model\Fields */
	private $fields;

	/** @var Model\FieldsCategory */
	private $fieldsCategory;

	/** @var Model\FieldsCompany */
	private $fieldsCompany;

	/** @var Model\CompanySeat */
	private $companySeat;

	/** @var Model\CompanyPerson */
	private $companyPerson;

	/** @var Model\CompanyDocs */
	private $companyDocs;

	/** @var Model\Company */
	private $companies;

	/** @var Model\Banks */
	private $banks;

	/** @var Model\Person */
	private $person;

	/** @var Model\Taxes */
	private $taxes;

	/** @var Model\Seat */
	private $seat;

	/** @var Model\Acting */
	private $acting;

	/** @var Model\City */
	private $city;

	/** @var Model\Payments */
	private $payments;

	/** @var Model\Users */
	private $users;

	/** @var Model\Person */
	private $persons;

	/** @var Model\Invoice */
	private $invoice;

	/** @var Model\UserManager */
	private $um;

	/** @var Model\Facade\CompanyFacade @inject */
	public $companyFacade;

	/** @var Model\AccountancyService @inject */
	public $accountancyService;

	/** @var Model\Helper\PaymentHelper @inject */
	public $paymentHelper;

	/** @var StepForm\Step8\IStep8FormFactory @inject */
	public $step8FormFactory;

	/** @var StepForm\Step3\IStep3FormFactory @inject */
	public $step3FormFactory;
	
	/** @var StepForm\Step2\IStep2FormFactory @inject */
	public $step2FormFactory;
	
	/** @var StepForm\Step5\IStep5FormFactory @inject */
	public $step5FormFactory;
	
	/** @var StepForm\Step1\IStep1FormFactory @inject */
	public $step1FormFactory;

	/** @var Model\SeatService @inject */
	public $seatService;

	/** @var Model\SeatServiceTarif @inject */
	public $seatTarif;

	/** @var Model\PhoneService @inject */
	public $phoneService;

	/** @var Model\PostService @inject */
	public $postService;
	
	/** @var Model\Facade\DiscountFacade @inject */
	public $discountFacade;

	/** @var DocsStorage */
	private $docsStorage;
	private $id;
	private $defaultVals;
	private $formNames;
	private $formSymbols;

	/** @var Company */
	protected $company;
	
	/** @var Model\Facade\CompanyProccessFacade @inject */
	public $proccessFacade;
	
	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	public function __construct(Model\Fields $fields, Model\FieldsCategory $fieldsCategory, Model\Banks $banks, Model\FieldsCompany $fieldsCompany, Model\Seat $seat, Model\CompanySeat $companySeat, Model\City $city, Model\Payments $payments, Model\Users $users, Model\Person $persons, Model\UserManager $um, Model\Taxes $taxes, Model\Company $companies, Model\CompanyPerson $companyPerson, Model\Acting $acting, Model\Invoice $invoice, Model\CompanyDocs $companyDocs, Model\Person $person)
	{
		parent::__construct();
		$this->fields = $fields;
		$this->fieldsCategory = $fieldsCategory;
		$this->banks = $banks;
		$this->person = $person;
		$this->fieldsCompany = $fieldsCompany;
		$this->seat = $seat;
		$this->companies = $companies;
		$this->companySeat = $companySeat;
		$this->companyPerson = $companyPerson;
		$this->city = $city;
		$this->payments = $payments;
		$this->users = $users;
		$this->persons = $persons;
		$this->um = $um;
		$this->taxes = $taxes;
		$this->acting = $acting;
		$this->invoice = $invoice;
		$this->companyDocs = $companyDocs;
	}

	protected function startup()
	{
		parent::startup();
		if ($this->disableApp) {
			$this->flashMessage("Omlouváme se, ale 10 lidí si již firmu založilo. Zvládneme založit firmu pouze 10 lidem, protože někdo musí skutečně oběhat úřady za Vás, s čímž se pojí náklady. A více bohužel nyní nezvládneme. Prosím napište nám na urad@pirati.cz, pokud si přesto přejete firmu založit.");
			$this->redirect("Homepage:");
		}
	}

	public function actionFinish()
	{
		if (!$this->session->getSection("finish")->finish) {
			//$this->redirect("NewCompany:");
			$this->redirect("Homepage:");
		}
		$this->session->getSection("finish")->remove();
	}
	
	public function actionDefault()
	{
		$this->readFromSession();
	}

	public function injectDocs(\DocsStorage $storage)
	{
		$this->docsStorage = $storage;
	}

	public function beforeRender()
	{
		//$this->readFromSession();
	}

	private function readFromSession()
	{
		// get all varaible from session and create object of company
		//$this->session->destroy();

		$section = $this->session->getSection("1");
		$this->company = new Company($section->company . " " . trim($section->name_suf), $this->fieldsCategory);
		if ($this->session->getSection("companyType")->type) {
			$this->company->setType($this->session->getSection("companyType")->type);
		}
		$generalSection = $this->session->getSection("general");
		$newUserID = $generalSection->userId;
		$this->company->setUser($newUserID);

		$persons = array();
		$generalSection = $this->session->getSection('users');
		$personNumber = 4;
		for ($i = 1; $i < $generalSection->userCount; $i++) {
			if ($this->session->getSection($personNumber . '-' . $i)->choice) {
				switch ($this->session->getSection($personNumber . '-' . $i)->choice) {
					case 2:
						$sessionPerson = $this->session->getSection($personNumber . '-' . $i);
						$person = new \Person($sessionPerson);
						$persons[] = $person;
						break;
					case 1:
						$sessionPerson = $this->session->getSection($personNumber . '-' . $i);
						$person = new \ExistingCompany($sessionPerson);
						$persons[] = $person;
						break;
					case 3:
						$sessionPerson = $this->session->getSection($personNumber . '-' . $i);
						$person = new \ForeignSubject($sessionPerson);
						$persons[] = $person;
						break;
				}
			}
		}
		$this->company->setPersons($persons);

		$sharesSection = $this->session->getSection('5');
		$this->company->setShares($sharesSection->fractions);
		$this->company->setCapital($sharesSection->value);
		$this->company->setPayments("notar", $sharesSection->notar);

		$actingPersons = array();
		$generalSection = $this->session->getSection('actingPersons');
	
		$namePrefix = "6-acting-";
		for ($i = 1; $i < $generalSection->userCount; $i++) {
			switch ($this->session->getSection($namePrefix . $i)->choice) {
				case 2:
					$sessionPerson = $this->session->getSection($namePrefix . $i);
					$person = new \Person($sessionPerson);
					$actingPersons[] = $person;
					break;
				case 1:
					$sessionPerson = $this->session->getSection($namePrefix . $i);
					$person = new \ExistingCompany($sessionPerson);
					$actingPersons[] = $person;
					break;
				case 3:
					$sessionPerson = $this->session->getSection($namePrefix . $i);
					$person = new \ForeignSubject($sessionPerson);
					$actingPersons[] = $person;
					break;
			}
		}

		$this->company->setTempActingPersons($actingPersons);

		$actingSection = $this->session->getSection('6');
		$actionName = $this->acting->findById($actingSection->actions);
		$this->company->setActingIndexes($actingSection->actingPersons);
		$this->company->setActions(array("acting_id" => $actingSection->actions,
			"name" => ($actionName) ? $actionName->desc : "",
			"desc" => $actingSection->other_option));
		if ($actingSection->actingPersons) {
			$persons = $actingSection->actingPersons;
			$realActingPersons = array();
			foreach ($persons as $p) {
				if (intval($p) < count($this->company->getPersons())) {
					$per = $this->company->getPersons();
					$realActingPersons[] = $per[$p];
				} else {
					$per = $this->company->getTempActingPersons();
					$realActingPersons[] = $per[$p - count($this->company->getPersons())];
				}
			}
			$this->company->setActingPersons($realActingPersons);
		}

		$bankSection = $this->session->getSection("7");
		if ($bankSection->taxes) {
			$taxes = array();
			foreach ($bankSection->taxes as $index => $tax_id) {
				$dan = $this->taxes->findById($tax_id);
				if ($dan->id == 2) {
					$taxes[$dan->id] = array("source" => $dan,
						"tax_reason" => $bankSection->tax_reason);
				} else {
					$taxes[$dan->id] = array("source" => $dan);
				}
			}
			$this->company->setTaxes($taxes);
		}

		if ($bankSection->accountancyServices && $bankSection->accountancyServices != "x") {
			$accountancyItem = $this->accountancyService->find($bankSection->accountancyServices);
			if ($accountancyItem) {
				$accountancy = new \AccountancyService($accountancyItem, $bankSection->vat, $bankSection->number_cars, $bankSection->number_employees);
			} else {
				$accountancy = NULL;
			}
		} else {
			$accountancy = NULL;
		}
		$this->company->setAccountancy($accountancy);

		$taxSection = $this->session->getSection("8");
		if ($taxSection->bank_number) {
			$banka = $this->banks->findById($taxSection->bank_number);
			$this->company->setBank(array("id" => $taxSection->bank_number, "name" => $banka->name, "source" => $banka));
		}
		if ($taxSection->compliance == 1) {
			$this->company->setCompliance(TRUE);
		}

		$paymentSection = $this->session->getSection("9");
		if ($paymentSection->street) {
			$person = new \Person($paymentSection);
			$this->company->setPaymentInfo($person);
		}

		$placeSection = $this->session->getSection("3");
		if (isset($placeSection->have_seat)) {
			if (!$placeSection->have_seat) {
				if (isset($placeSection->seatService) && $placeSection->seatService) {
					$seatService = $this->seatService->find($placeSection->seatService);
					$seatServiceTarif = $this->seatTarif->find($placeSection->seatServiceTarif);
					$postService = $this->postService->find($placeSection->postService);
					$phoneService = $this->phoneService->find($placeSection->phoneService);
					$this->company->setSeat(new \SeatService($seatService, $seatServiceTarif));
					$this->company->setPostService(new \PostService($postService, $seatServiceTarif));
					$this->company->setPhoneService(new \PhoneService($phoneService, $seatServiceTarif));
				} 
			} else {
				if (isset($placeSection->street)) {
					$place = new \Place($placeSection);
					$this->company->setSeat($place);
				}
			}
		}

		$areas = array();
		$areaSection = $this->session->getSection("2");
		if (!empty($areaSection)) {
			foreach ($areaSection as $areaCat) {
				if (!$areaCat) {
					Debugger::log($_SESSION,"areaSection");
					continue;
				}
				foreach ($areaCat as $index => $value) {
					$areas[$value] = $this->fields->findById($value)->name;
				}
			}
		}
		$this->company->setAreas($areas);
	}

	/**
	 * Main funciton for rendering the whole precedure of registration of a
	 * new company step by step.
	 * 
	 * @param int $id - indicates the step of the process we are in
	 */
	public function renderDefault()
	{
		$this->template->backlink = $this->storeRequest();
		$this->formNames = array(1 => "Název společnosti", 2 => "Předmět podnikání",
			3 => "Sídlo", 4 => "Společníci", 5 => "Základní kapitál", 6 => "Jednatel a způsob jednání",
			7 => "Daně", 8 => "Bankovní účet", 9 => "Kontaktní údaje",
			10 => "Shrnutí a náklady");
		$this->formSymbols = array(1 => "flag", 2 => "briefcase", 3 => "home",
			4 => "leaf", 5 => "stats", 6 => "user", 7 => "duplicate", 8 => "credit-card",
			9 => "king", 10 => "ok");
		$this->template->formSymbols = $this->formSymbols;
		$this->template->formNames = $this->formNames;
		$section = $this->session->getSection("company");
		if ($section["name"]) {
			if ($this->company == null) {
				$this->company = new Company($section["name"]);
			}
		}
		$section = $this->session->getSection('general');
		$section->stepCounter = ($section->stepCounter) ? $section->stepCounter : 1;
		$section->stepCounter = ($section->stepCounter > 10) ? 10 : $section->stepCounter;
		$section->actingPersonCounter = ($section->actingPersonCounter) ? $section->actingPersonCounter : 1;
		$id = $section->stepCounter;
		$this->template->formToRender = intval($id);
		$this->template->categories = "";
		$this->template->items = array();
		$section = $this->session->getSection("" . $section->stepCounter);
		$this->defaultVals = array("state" => "Česká republika");
		if ($section) {
			foreach ($section as $key => $val) {
				$this->defaultVals[$key] = $val;
			}
		}
		$section = $this->session->getSection('orderForm');
		$this->template->formName = $this->formNames[$id];
		$this->template->userDetails = $this->session->getSection("userForm");
		$section = $this->session->getSection("users");
		if (!$section) {
			$section->userCount = 1;
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
		$this->template->city = $this->city->findAll();

		$payments = $this->payments->findAll()->where("step<>?", 1);
		$this->template->possiblePayments = array();
		foreach ($payments as $row) {
			$this->template->possiblePayments[$row->id] = array("desc" => $row->desc,
				"value" => $row->value,
				"step" => $row->step);
		}

		if ($id == 9) {
			$userForm = $this->session->getSection("9");
			$this["newUserForm"]->setDefaults($userForm);
		} else if ($id == 8) {
			$userForm = $this->session->getSection("8");
			$this["bankAccountForm"]["form"]->setDefaults($userForm);
		} else if ($id == 3) {
			$userForm = $this->session->getSection("3");
			$this["seatForm"]["form"]->setDefaults($userForm);
		}

		$this->template->foreignPeopleCost = $this->payments->findById(10);
		$this->template->complianceCost = $this->payments->findById(23);

		$this->template->jednatele = $this->company->getActingPersons();
		$this->template->message = "";
		$this->template->messType = "";
		$this->template->company = $this->company;
		$this->template->pData = array();
		$counter = 0;
		foreach ($this->company->getPersons() as $p) {
			$this->template->pData[$counter++] = $p->getFullName();
		}
		$this->template->accountancy1 = $this->accountancyService->getServicesByCategory(1);
		$this->template->accountancy2 = $this->accountancyService->getServicesByCategory(2);
		$this->template->accountancyConsultation = isset($this->session->getSection("7")->accountancyConsultation) && $this->session->getSection("7")->accountancyConsultation;
		$this->template->charges = $this->createChargeList($this->template->formToRender);
	}
	
	protected function createChargeList($formToRender)
	{
		$charge = new \ChargeList;
		$company = $this->company;
		
		$payments = $this->payments->findAll()->where("step=?", 1);
		foreach ($payments as $payment) {
			$payment = $payment->toArray();
			if ($payment["id"] == Model\Payments::NOTARY_FEE) {
				$payment["w_vat"] = $this->paymentHelper->getNotaryFees($this->company->getCapital(),$this->company->getType());
				$payment["value"] = $payment["w_vat"] * 1.21;
			}
			else if ($payment["id"] == Model\Payments::COURT_FEE) {
				if ($this->company->getType() == "simple") {
					$payment["value"] = 0;
					$payment["w_vat"] = 0;
				}
			}
			else if ($payment["id"] == Model\Payments::ADMINISTRATIVE_FEE) {
				if ($code = $this->discountFacade->getCode()) {
					$voucher = $this->discountFacade->getVoucher($code);
					$desc["rightColumn"] = $payment["desc"] . ". <b>Uplatněn kód: ".$voucher["name"]."</b>";
					$desc["invoice"] = $payment["desc"];
					$payment["desc"] = $desc;
					$payment["w_vat"] = $payment["w_vat"] - $voucher["value"];
					$payment["value"] = $payment["w_vat"] * 1.21;
				} else {
					$desc["rightColumn"] = $payment["desc"];
					$desc["invoice"] = $payment["desc"];
					$payment["desc"] = $desc;
				}
			}
			else if ($payment["id"] == Model\Payments::TRADE_LICENCE_FEE) {
				$continue = TRUE;
				if (!$this->company->getAreas()) {
					$continue = FALSE;
				} else {
					foreach ($this->company->getAreas() as $k => $_) {
						$type = $this->fieldsCategory->getFiledsTypes()->where("fields_id = ?",$k)->fetch();
						if ($type) {
							if ($type->type_id != 5) { // pokud alespon jedna zivnost neni ostatni, poplatek se nebude uctovat
								$continue = FALSE;
								break;
							}
						}
					}
				}
				if ($continue) {
					continue;
				}
			}
			$charge->add(\ChargeList::CATEGORY_REGULAR, $payment["desc"], $payment["value"], $payment["w_vat"], $payment["vat"], $payment["info"]);
		}
		
		if ($formToRender > 2) {
			$specialAreas = array();
			if (array_key_exists(90, $this->company->getAreas())) {
				$specialAreas[] = $this->payments->findById(7);
			}
			if (array_key_exists(182, $this->company->getAreas())) {
				$specialAreas[] = $this->payments->findById(8);
			}
			if (array_key_exists(183, $this->company->getAreas())) {
				$specialAreas[] = $this->payments->findById(9);
			}
			
			foreach ($specialAreas as $p2) {
				$charge->add(\ChargeList::CATEGORY_REGULAR, $p2->desc, $p2->value, $p2->w_vat, $p2->vat, $p2->info);
			}
		}
		
		// priplatky sidlo
		if ($formToRender > 3) {
			if ($company->getSeat() && !$company->getSeat()->hasOwnSeat()) {
				$charge->add(
					\ChargeList::CATEGORY_ADDITIONAL, 
					array(
						"rightColumn" => "Sídlo: ".$company->getSeat()->getName(), 
						"invoice" => "Sídlo: ".$company->getSeat()->getName()." na ".$company->getSeat()->getMonths()." měsíců", 
					),
					$company->getSeat()->getPriceWithVat(), 
					$company->getSeat()->getPrice(),
					$company->getSeat()->getVat(),
					"",
					"za ".$company->getSeat()->getMonths()." měsíců"
				);
			}
			if ($company->getPostService() && $company->getPostService()->getPrice()) {
				$charge->add(
					\ChargeList::CATEGORY_ADDITIONAL, 
					array(
						"rightColumn" => "Pošta: " . Strings::truncate($company->getPostService()->getName(),35),
						"invoice" => "Pošta: " . $company->getPostService()->getName()." na ".$company->getSeat()->getMonths()." měsíců"
					),
					$company->getPostService()->getPriceWithVat(), 
					$company->getPostService()->getPrice(), 
					$company->getPostService()->getVat(), 
					$company->getPostService()->getInfo(),
					"za ".$company->getSeat()->getMonths()." měsíců"
				);
			}
			if ($company->getPhoneService() && $company->getPhoneService()->getPrice()) {
				$charge->add(
					\ChargeList::CATEGORY_ADDITIONAL, 
					array(
						"rightColumn" => "Telefon: ".$company->getPhoneService()->getName(), 
						"invoice" => "Telefon: ".$company->getPhoneService()->getName()." na ".$company->getSeat()->getMonths()." měsíců", 
					),
					$company->getPhoneService()->getPriceWithVat(), 
					$company->getPhoneService()->getPrice(), 
					$company->getPhoneService()->getVat(), 
					$company->getPhoneService()->getInfo(),
					"za ".$company->getSeat()->getMonths()." měsíců"
				);
			}
		}
		
		// priplatky zahranicni spolecnici
		if ($formToRender > 3) {
			$p = $this->payments->findById(10);
			foreach ($company->getPersons() as $_) {
				if ($_ instanceof \ForeignSubject) {
					$charge->add(
						\ChargeList::CATEGORY_ADDITIONAL, 
						$p->desc, 
						$p->value, 
						$p->w_vat, 
						$p->vat, 
						$p->info
					);
				}
			}
		}
		
		// priplatky zahranicni jednatele
		if ($formToRender > 6) {
			$p = $this->payments->findById(11);
			foreach ($this->company->getActingPersons() as $_) {
				if ($_ instanceof \ForeignSubject) {
					$charge->add(
						\ChargeList::CATEGORY_ADDITIONAL, 
						$p->desc, 
						$p->value, 
						$p->w_vat, 
						$p->vat, 
						$p->info
					);
				}
			}
		}

		// priplatky jiny zpusob jednani
		if ($formToRender > 6 && $company->getActions()["acting_id"]==4) {
			$p = $this->payments->findById(12);
			$charge->add(
				\ChargeList::CATEGORY_ADDITIONAL, 
				$p->desc, 
				$p->value, 
				$p->w_vat, 
				$p->vat, 
				$p->info
			);
		}
		
		// priplatky ucetnictvi
		if ($formToRender > 7) {
			foreach ($company->getTaxes() as $tax) {
				if (!$tax["source"]->payment_id) {
					continue;
				}
				$p2 = $tax["source"]->ref("payments","payment_id");
				$charge->add(
					\ChargeList::CATEGORY_ADDITIONAL, 
					$p2->desc, 
					$p2->value, 
					$p2->w_vat, 
					$p2->vat, 
					$p2->info
				);
			}			
		}
		
		// priplatky banky
		$bank = $company->getBank();
		if ($formToRender > 8 && $bank && $bank["source"]->payments_id) {
			$p = $bank["source"]->payments;
			$charge->add(
				\ChargeList::CATEGORY_ADDITIONAL, 
				$p->desc, 
				$p->value, 
				$p->w_vat, 
				$p->vat, 
				$p->info
			);
		}
		
		// priplatky compliance
		$compliance = $company->getCompliance();
		if ($formToRender > 8 && $compliance) {
			$p = $this->payments->findById(23);
			$charge->add(
				\ChargeList::CATEGORY_ADDITIONAL, 
				$p->desc, 
				$p->value, 
				$p->w_vat, 
				$p->vat, 
				$p->info
			);
		}

		return $charge;
	}

	protected function createComponentStep1Form($name)
	{
		$control = $this->step1FormFactory->create();
		$control->onSuccess[] = [$this, "nextStep"];
		$control->onCancel[] = [$this, "formCancelled"];
		return $control;
	}

	public function handleDeleteSpolecnik($id)
	{
		\Tracy\Debugger::barDump($id);
		$generalSection = $this->session->getSection('users');
		$personNumber = 4;
		for ($i = $id; $i < $generalSection->userCount - 1; $i++) {
			$toChange = $this->session->getSection($personNumber . '-' . $i);
			$temp = $this->session->getSection($personNumber . '-' . ($i + 1));
			foreach ($temp as $key => $val) {
				$toChange[$key] = $val;
			}
		}
		$this->session->getSection($personNumber . '-' . ($generalSection->userCount - 1))->remove();
		$generalSection->userCount = $generalSection->userCount - 1;
		$section = $this->session->getSection('general');

		// remove conflicting values
		$this->session->getSection('6')->remove();
		$this->session->getSection('5')->remove();
		if ($generalSection->userCount == 1) {
			$section->stepCounter = 4;
			$this->flashMessage("Nejsou žádní společníci, je třeba nějaké zadat.");
		} else {
			if ($section->stepCounter > 4) {
				$section->stepCounter = 5;
				$this->flashMessage("Je třeba upravit podíly společníků.");
			}
		}
		$this->redirect("NewCompany:"); //,$section->step_counter);            
	}

	public function handleSearch(\Nette\Forms\Controls\SubmitButton $button)
	{
		\Tracy\Debugger::barDump($button);
		$values = $button->getForm()->getValues();
		\Tracy\Debugger::barDump($values);
		try {
			$company = $this->parseJustice($values["company"]);
		} catch (\App\Presenters\JusticeLimitException $e) {
			$this->flashMessage("Byl vyčerpán limit dotazů pro dnešní den");
			$this->redirect("Homepage:");
		}
		\Tracy\Debugger::barDump($company);
		$this->template->available = 1;
		if ($this->isAjax()) {
			$this->template->companyName = $company;
			$this->redrawControl("name");
			$this->redrawControl("new_name");
		}
	}

	public function userFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		
		// This should be switching base on button
		\Tracy\Debugger::barDump($button);
		\Tracy\Debugger::barDump($values);

		$section = $this->session->getSection("general");
		if (!$this->company) {
			$this->readFromSession();
		}

		switch ($section->stepCounter) {
			case 4:
				$next = true;
				$this->userFormAddUser($button, $next);
				break;
			case 6:
				$this->saveActingPersons($button);
				$values = $button->getForm()->httpData;
				break;
		}
		$section = $this->session->getSection("" . $section->stepCounter);
		foreach ($values as $key => $val) {
			$section[$key] = $val;
		}
		$section = $this->session->getSection("general");
		$section->stepCounter += ($section->stepCounter < 10) ? 1 : 0;
		$this->redirect("NewCompany:");
	}
	
	public function nextStep()
	{
		$this->proccessFacade->nextStep();
		$this->redirect("NewCompany:");
	}

	private function saveActingPersons($button)
	{
		\Tracy\Debugger::barDump($button);
		$values = $button->getForm()->getHttpData();
		$section = $this->session->getSection("general");
		$section = $this->session->getSection("" . $section->stepCounter);
		foreach ($values as $key => $val) {
			if (strcmp($key, "actingPersons") == 0 ||
					strcmp($key, "actions") == 0 ||
					strcmp($key, "other_option") == 0) {
				$section[$key] = $val;
			}
		}
		$section = $this->session->getSection("general");
		$section->stepCounter += ($section->stepCounter < 10) ? 1 : 0;
		$this->redirect("NewCompany:");
	}

	private function localSessionDestroy()
	{
		$us = $this->session->getSection("users");
		$userCount = $us->userCount;
		for ($i = 0; $userCount && $i <= $userCount; $i++) {
			$this->session->getSection("4-" . $i)->remove();
		}
		$us = $this->session->getSection("actingPersons");
		$userCount = $us->userCount;
		for ($i = 0; $userCount && $i <= $userCount; $i++) {
			$this->session->getSection("6-" . $i)->remove();
		}

		for ($i = 0; $i <= 10; $i++) {
			$this->session->getSection("" . $i)->remove();
		}
		$this->session->getSection("users")->remove();
		$this->session->getSection("actingPersons")->remove();
		$this->session->getSection("general")->remove();
		$this->session->getSection("company")->remove();
		$this->session->getSection("companyType")->remove();
	}

	/**
	 * The method after 10. step saving the whole session to DB
	 */
	public function saveSessionToDB()
	{
		try {
			$this->readFromSession();
			$registeredUser = $this->session->getSection("9");
		
			$this->companyFacade->createCompany($this->user, $this->company, $registeredUser, $this->createChargeList(100));

			$specialDocs = Model\Service\DocumentService::hasSpecialDocs($this->company);
			
			if (!$this->user->isLoggedIn()) {
				$clientMail = $this->session->getSection("9")->mail;
			} else {
				$clientMail = $this->user->getIdentity()->data["mail"];
			}

			/** ********* Mail klientovi ********** */
			$template = $this->createTemplate()->setFile($this->context->parameters['appDir'] . '/templates/Email/email.latte');
			$template->nadpis = 'Vaše dokumenty';
			$template->text = 'Dobrý den,<br><br><p>děkujeme, že jste si pro rozjezd svého podnikání vybrali software of Pirátů! Nejen, že budete mít firmu rychle a bez obíhání úřadů. Podporou Pirátů pomáháte podnikatelům mít jednodušší život, a tedy pomáháte i České republice posunout se o úroveň výš.</p><p>V příloze najdete všechny dokumenty nutné k založení Vaší firmy. Pro další postup se řiďte pokyny v přiloženém souboru “pokyny.pdf”. Máte-li pouze neregulované živnosti, Vaši firmu náš prověřený partner založí do pěti dnů od přijetí platby a dokumentů.</p><br><br>O stavu registrace Vaší společnosti Vás budeme průběžně informovat.<br><br>S pozdravem,<br>Vaši Piráti';
			$mail = new Message;
			$mail->setFrom('1. Pirátská <urad@pirati.cz>')
					->addTo($clientMail)
					//->addBcc('steve@strebl.com')
					->setSubject('Vaše dokumenty')
					->setHTMLBody($template, FALSE);
			if ($this->company->getSeat()->hasOwnSeat()) {
				$docs = $this->companyDocs->findAll()->where("company_id=? AND public=1 AND doc_type != ? AND doc_type != ?", $this->company->getId(), Model\Service\DocumentService::$docsEnum["faktura"], Model\Service\DocumentService::$docsEnum["spol_smlouva_notar"]);
			} else {
				$docs = $this->companyDocs->findAll()->where("company_id=? AND public=1 AND doc_type != ? AND doc_type != ? AND doc_type != ?", $this->company->getId(), Model\Service\DocumentService::$docsEnum["faktura"], Model\Service\DocumentService::$docsEnum["spol_smlouva_notar"], Model\Service\DocumentService::$docsEnum["souhlas_sidlo"]);
			}
			foreach ($docs as $doc) {
				$mail->addAttachment($doc->name . ".pdf", $this->docsStorage->get($doc->filename . ".pdf", $doc->name));
			}
			if ($specialDocs) {
				$mail->addAttachment("prohlaseni_odpovedneho_zastupce.pdf", $this->docsStorage->get("/../docs/prohlaseni_odpovedneho_zastupce.pdf"), "prohlaseni_odpovedneho_zastupce.pdf");
			}
			//$mailer = new SendmailMailer;
			$this->mailer->send($mail);

			/** ********* Mail adminovi ********** */
			$areasRaw = $this->fields->findAll()->where("id IN ?", array_keys($this->company->getAreas()))->order("crm_id")->fetchAll();
			$bank = $this->company->getBank();
			$taxes = $this->company->getTaxes();
			$a = "";

			$areas = array();
			foreach ($areasRaw as $area) {
				$areas[$area->related("fieldstypes", "fields_id")->fetch()->type_id][] = array($area->crm_id, $area->name);
			}

			$a .= "<h2>Základní kapitál</h2>" . \number_format($this->company->getCapital(), 2, ",", ".") . "<br><br>";
			$a .= "<h2>Živnosti</h2>";

			if (array_key_exists(1, $areas)) {
				$a .= "<br><b>řemeslná:</b><br><br><table>";
				$a .= "<tr><th>Název</th></tr>";
				foreach ($areas[1] as $key => $area) {
					$a .= "<tr><td>$area[1]</td></tr>";
				}
				$a .= "</table><hr><br>";
			}
			if (array_key_exists(2, $areas)) {
				$a .= "<br><b>volná:</b><br><br><table>";
				$a .= "<tr><th>Číslo</th><th>Název</th></tr>";
				foreach ($areas[2] as $area) {
					$a .= "<tr><td>$area[0]</td><td>$area[1]</td></tr>";
				}
				$a .= "</table><hr><br>";
			}
			if (array_key_exists(3, $areas)) {
				$a .= "<br><b>vázaná:</b><br><br><table>";
				$a .= "<tr><th>Název</th></tr>";
				foreach ($areas[3] as $area) {
					$a .= "<tr><td>$area[1]</td></tr>";
				}
				$a .= "</table><hr><br>";
			}
			if (array_key_exists(4, $areas)) {
				$a .= "<br><b>koncesovaná:</b><br><br><table>";
				$a .= "<tr><th>Název</th></tr>";
				foreach ($areas[4] as $area) {
					$a .= "<tr><td>$area[1]</td></tr>";
				}
				$a .= "</table><hr><br>";
			}
			if (array_key_exists(5, $areas)) {
				$a .= "<br><b>ostatní:</b><br><br><table>";
				$a .= "<tr><th>Název</th></tr>";
				foreach ($areas[5] as $area) {
					$a .= "<tr><td>$area[1]</td></tr>";
				}
				$a .= "</table><hr><br>";
			}

			$a .= "<h2>Banka</h2>" . $bank["name"] . "<br><br>";

			$a .= "<h2>Daně</h2><table>";
			foreach ($taxes as $key => $area) {
				$a .= "<tr><td>" . $area["source"]->name . ($area["source"]->id == Model\Taxes::VAT ? " - \"" . $area["tax_reason"] . "\"" : "") . "</td></tr>";
			}
			$a .= "</table><hr><br>";
			
			if ($this->company->getSeat() && !$this->company->getSeat()->hasOwnSeat()) {
				$a .= "<h2>Sídlo</h2>" . $this->company->getSeat()->getName()." na ".$this->company->getSeat()->getMonths()." měsíců" . "<hr><br>";
			}
			
			if ($this->company->getPostService() && $this->company->getPostService()->getPrice()) {
				$a .= "<h2>Pošta</h2>" . $this->company->getPostService()->getName()." na ".$this->company->getSeat()->getMonths()." měsíců" . "<hr><br>";
			}
			
			if ($this->company->getPhoneService() && $this->company->getPhoneService()->getPrice()) {
				$a .= "<h2>Telefon</h2>" . $this->company->getPhoneService()->getName()." na ".$this->company->getSeat()->getMonths()." měsíců" . "<hr><br>";
			}
			
			if (!$this->user->isLoggedIn()) {
				$a .= "<h2>Klient</h2>
					<b>Jméno:</b> " . $registeredUser->name . "<br>
					<b>Příjmení:</b> " . $registeredUser->surname . "<br>
					<b>E-mail:</b> " . $registeredUser->mail . "<br>
					<b>Telefon:</b> " . $registeredUser->tel . "<br>
					<b>Ulice:</b> " . $registeredUser->street . "<br>
					<b>Č.p:</b> " . $registeredUser->street_number . "<br>
					<b>Město:</b> " . $registeredUser->city . "<br>
					<b>PSČ:</b> " . $registeredUser->zip_code . "<br>"
				;
			} else {
				$a .= "<h2>Klient - přihlášený - ID: ".$this->user->getId()."</h2>
					<b>Jméno:</b> " . $this->user->getIdentity()->data["name"] . "<br>
					<b>Příjmení:</b> " . $this->user->getIdentity()->data["surname"] . "<br>
					<b>E-mail:</b> " . $this->user->getIdentity()->data["mail"] . "<br>"
				;
			}

			$template->text .= $a;

			$mail = new Message;
			$mail->setFrom('1. Pirátská <urad@pirati.cz>')
					->addTo('steve@strebl.com')
					->addTo('urad@pirati.cz')
					->addTo('podpora@zakladaci.cz')
					->setSubject('Založena nová společnost '.$this->company->getName())
					->setHTMLBody($template, FALSE);
			$docs = $this->companyDocs->findAll()->where("company_id=?", $this->company->getId());
			foreach ($docs as $doc) {
				if (strpos($doc->name, "_edit") == (strlen($doc->name)-strlen("_edit"))){
					// Adding editable document
					$mail->addAttachment($doc->name . ".docx", $this->docsStorage->get($doc->filename . ".docx", $doc->name));
				}else{
					$mail->addAttachment($doc->name . ".pdf", $this->docsStorage->get($doc->filename . ".pdf", $doc->name));
				}				
			}
			$this->mailer->send($mail);
			/* ***************************** */

			$this->localSessionDestroy();
			
			$this->flashMessage("Vaše žádost byla úspěšně vytvořena!");
			$this->flashMessage("Dokumenty vytvořeny a odeslány na Váš email.");

			// tohle by asi bylo dobré po proběhnutí všech úkonů, aby se to smazalo			
			$this->session->getSection("finish")->finish = TRUE;
		}
		catch (\Exception $e) {
			$this->proccessException($e);
			$this->flashMessage("Žádost se nepodařilo vytvořit");
			// TODO tohle by se melo jeste nejak vyresit jako ze prozkoumame to a budeme to resit
			// aby uzivatel byl v klidu a notifikovat spravce, aby se na to nekdo podival
			$this->redirect("this");
		}
		$this->redirect("finish");
	}

	public function userFormAddUser($button, $next=false)
	{
		$values = $button->getForm()->getValues();
		if ($this->session->getSection("general")->stepCounter < 6) {
			if ($values->choice && ($values->ico || $values->name || $values->foreign)) {
				$section = $this->session->getSection("users");
				$userCount = $section->userCount;
				if (!$userCount) {
					$userCount = 1;
				}

				for ($i = 1; $i <= $userCount; $i++) {
					if ($values->choice) {
						switch ($values->choice) {
							case 1:
								if ($this->session->getSection(4 . '-' . $i)->company_name == $values->company_name) {
									$this->flashMessage("Zadaný společník již existuje.");
									$this->redirect("NewCompany:");
								}
							break;
							case 2:
								if ($this->session->getSection(4 . '-' . $i)->person_id  == $values->person_id) {
									$this->flashMessage("Zadaný společník již existuje.");
									$this->redirect("NewCompany:");
								}
							break;
							case 3:
								if ($this->session->getSection(4 . '-' . $i)->foreign == trim($values->foreign)) {
									$this->flashMessage("Zadaný společník již existuje.");
									$this->redirect("NewCompany:");
								}
							break;
						}
					}
				}

				$section = $this->session->getSection("general");
				$section_name = $section->stepCounter . "-" . $userCount;
				$section = $this->session->getSection($section_name);
				foreach ($values as $key => $val) {
					$section[$key] = $val;
				}
				$section = $this->session->getSection("users");
				$section->userCount = $userCount + 1;
				if ( $button->getName() == "another" ) {
					$this->flashMessage("Byl úspěšně přidán nový společník.");
					$this->redirect("NewCompany:");
				}
			}
		} else {
			$section = $this->session->getSection("actingPersons");
			$userCount = $section->userCount;
			$section = $this->session->getSection("general");
			if (!$userCount) {
				$userCount = 1;
			}

			for ($i = 1; $i <= $userCount; $i++) {
				if ($values->choice) {
					switch ($values->choice) {
						case 1:
							if ($this->session->getSection(4 . '-' . $i)->company_name == $values->company_name) {
								$this->flashMessage("Zadaný jednatel již existuje.");
								$this->redirect("NewCompany:");
							}
							if ($this->session->getSection($section->stepCounter . '-acting-' . $i)->company_name == $values->company_name) {
								$this->flashMessage("Zadaný jednatel již existuje.");
								$this->redirect("NewCompany:");
							}
						break;
						case 2:
							if ($this->session->getSection(4 . '-' . $i)->person_id  == $values->person_id) {
								$this->flashMessage("Zadaný jednatel již existuje.");
								$this->redirect("NewCompany:");
							}
							if ($this->session->getSection($section->stepCounter . '-acting-' . $i)->person_id  == $values->person_id) {
								$this->flashMessage("Zadaný jednatel již existuje.");
								$this->redirect("NewCompany:");
							}
						break;
						case 3:
							if ($this->session->getSection($section->stepCounter . '-acting-' . $i)->foreign == trim($values->foreign)) {
								$this->flashMessage("Zadaný jednatel již existuje.");
								$this->redirect("NewCompany:");
							}
						break;
					}
				}
			}

			$section_name = $section->stepCounter . "-acting-" . $userCount;
			$section = $this->session->getSection($section_name);
			foreach ($values as $key => $val) {
				$section[$key] = $val;
			}
			$section = $this->session->getSection("actingPersons");
			$section->userCount = $userCount + 1;
			if ( $button->getName() == "another" ) {
				$this->flashMessage("Byl úspěšně přidán nový jednatel.");
				$this->redirect("NewCompany:");
			}
		}
		$this->session->getSection("5")->remove();
	}

	/**
	 * Validator for the date
	 * 
	 * @param type $item - the input of form
	 * @param type $arg - arguments passed by user
	 * @return type - true/false value
	 */
	function dateValidator($item, $arg)
	{
		return date_parse($item->value);
	}

	function bussinesFormValidator($item, $arg)
	{
		return true;
	}

	function verifyRC($rc)
	{
		$rc = $rc->value;
		// be liberal in what you receive
		if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) {
			return FALSE;
		}

		list(, $year, $month, $day, $ext, $c) = $matches;

		if ($c === '') {
			$year += $year < 54 ? 1900 : 1800;
		} else {
			// kontrolní číslice
			$mod = ($year . $month . $day . $ext) % 11;
			if ($mod === 10)
				$mod = 0;
			if ($mod !== (int) $c) {
				return FALSE;
			}

			$year += $year < 54 ? 2000 : 1900;
		}

		// k měsíci může být připočteno 20, 50 nebo 70
		if ($month > 70 && $year > 2003) {
			$month -= 70;
		} elseif ($month > 50) {
			$month -= 50;
		} elseif ($month > 20 && $year > 2003) {
			$month -= 20;
		}

		// kontrola data
		if (!checkdate($month, $day, $year)) {
			return FALSE;
		}

		return TRUE;
	}

	protected function createComponentUserForm()
	{
		$form = new Form();
		$form->addRadioList('choice', 'Jste', array(1 => "právnická osoba (ČR)",
					2 => "fyzická osoba (ČR)",
				))
				->setRequired("Prosím vyplňte")
				->addCondition(Form::EQUAL, 1)
				->toggle("company-name")
				->endCondition()
				->addCondition(Form::EQUAL, 2)
				->toggle("os")
				->endCondition()
				->addCondition(Form::EQUAL, 3)
				->toggle("other");
		$form->addTextArea("foreign", "zahraničí")
				->addConditionOn($form["choice"], Form::EQUAL, 3)
				->setRequired('Prosíme zadejte Váš požadavek.');
		$form->addText('company', 'právnická osoba');
		$form->addSubmit("search", "hledat")
						->setValidationScope(FALSE)
				->onClick[] = [$this, "handleSearch"];
		$form->addText('company_name', 'Název firmy')
				->addConditionOn($form["choice"], Form::EQUAL, 1)
				->setRequired('Prosíme zadejte jméno.');
		$form->addText('ico', 'IČO')
				->addConditionOn($form["choice"], Form::EQUAL, 1)
				->setRequired('Prosíme zadejte IČO');
		$form->addText('seat', 'Sídlo')
				->addConditionOn($form["choice"], Form::EQUAL, 1)
				->setRequired('Prosíme zadejte sídlo');
		$form->addText('name', 'jméno')
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->setRequired('Prosíme zadejte jméno.');
		$form->addText('surname', 'příjmení')
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->setRequired('Prosíme zadejte příjmení.');
		$form->addText("person_id", "Rodné číslo")
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->addRule(Form::PATTERN, "Rodné číslo musí mít 9 nebo 10 číslic (3 nebo 4 za lomítkem)", '([0-9]{6}/[0-9]{3,4})')
				->addRule([$this, 'verifyRC'], 'Musíte zadat platné rodné číslo')
				->setRequired("Musíte zadat rodné číslo");
		$form->addText('title_pre', 'Titul před jménem');
		$form->addText('title_suf', 'Titul za jménem');
		$form->addText('street', "Ulice")
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->setRequired("Prosím zadejte ulici");
		$form->addText('street_number', 'Číslo domu')
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->setRequired("Zadejte číslo domu");
		$form->addText('city', "Město")
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->setRequired("Prosím zadejte město.");
		$form->addText('zip_code', 'PSČ')
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->setRequired("Zadjte PSČ")
				->addRule(Form::PATTERN, 'PSČ musí mít 5 číslic', '([0-9]\s*){5}');
		$form->addText('state', 'Stát')
				->addConditionOn($form["choice"], Form::EQUAL, 2)
				->setRequired("Zadejte stát");
		$form->addSubmit('another', 'přidat společníka')
				->onClick[] = [$this, "userFormAddUser"];
		$form->addSubmit('next', 'další')
				->setValidationScope($this->session->getSection("users")->userCount <= 1)
				->onClick[] = [$this, "userFormSucceeded"];

		$form->addSubmit('cancel', 'zpátky')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		$_this = $this;
		$form->onError[] = function() use($_this) {
			$_this->redrawControl("userFormError");
		};
		$form->addProtection();
		if ($this->defaultVals && !$this->session->getSection("users")) {
			$form->setDefaults($this->defaultVals);
		}
		return $form;
	}

	protected function createComponentConfirmForm()
	{
		$form = new Form();
		$form->addCheckbox('conditions')
				->setRequired("Je nutné souhlasit s podmínkami.");
		$form->addSubmit('save', 'další')
				->onClick[] = [$this, "saveSessionToDB"];
		$form->addSubmit('cancel', 'zpátky')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		$form->addProtection();
		return $form;
	}

	protected function createComponentActingPersonForm()
	{
		$form = new Form();
		$form->addText('name', 'jméno')
				->setRequired('Prosíme zadejte jméno.');
		$form->addText('surname', 'příjmení')
				->setRequired('Prosíme zadejte příjmení.');
		$form->addText("person_id", "rodné číslo")
				->addRule(Form::PATTERN, "Rodné číslo musí mít 10 číslic (4 za lomítkem)", '([0-9]{6}/[0-9]{3,4})')
				->addRule(callback($this, 'verifyRC'), 'Musíte zadat platné rodné číslo');
		$form->addText("birth_place", "místo narození")
				->setRequired("Prosím zadejte místo narození");
		$form->addText("birth_place_state", "stát narození")
				->setRequired("Prosím zadejte stát narození");
		$form->addText("nationality", "státní příslušnost (např. česká)")
				->setRequired("Prosím zadejte státní příslušnot");
		$form->addText('title_pre', 'Titul před jménem');
		$form->addText('title_suf', 'Titul za jménem');
		$form->addText('street', "Ulice")
				->setRequired("Zadejte jméno ulice");
		$form->addText('street_number', 'Číslo domu')
				->setRequired("Zadejte číslo domu")
				->addRule(Form::PATTERN, 'Číslo musí být číslo', '([0-9])+');
		$form->addText('city_partition', "Část obce");
		$form->addText('city', "Město")
				->setRequired("Zadejte město");
		$form->addText('zip_code', 'PSČ')
				->setRequired("Zadejte psč")
				->addRule(Form::PATTERN, 'PSČ musí mít 5 číslic', '([0-9]\s*){5}');
		$form->addText('state', 'Stát')
				->setRequired("Zadejte stát");
		$form->addText('tel', 'Telefon')
				->setRequired("Zadejte telefon")
				->addRule(Form::PATTERN, "Telefonní číslo musí 9 číslic.", '([0-9]\s*){9}');
		$form->addText('mobile', 'Mobil')
				->addConditionOn($form["tel"], Form::BLANK)
				->setRequired("Zadejte mobil")
				->addRule(Form::PATTERN, "Telefonní číslo musí 9 číslic.", '([0-9]\s*){9}');
		$form->addSubmit('next', 'další')
				->onClick[] = [$this, "userFormSucceeded"];
		$form->addSubmit('cancel', 'zpátky')
						->setValidationScope(NULL)
				->onClick[] = [$this, "formCancelled"];
		$form->addProtection();

		if ($this->defaultVals) {
			$form->setDefaults($this->defaultVals);
		}
		return $form;
	}

	protected function createComponentReponsiblePerson()
	{
		
	}
	
	public function handleCheckUserPirati(Nette\Forms\Controls\TextInput $mail)
	{
		$mail = $mail->getValue();
		$e = explode("@", $mail);
		if (count($e) != 2) {
			return FALSE;
		}
		if ($e[1] == "pirati.cz") {
			return FALSE;
		}
		return TRUE;
	}

	private function endsWith($string, $endString) 
	{ 
    	$len = strlen($endString); 
    	if ($len == 0) { 
	        return true; 
		} 
		return (substr($string, -$len) === $endString); 
	} 


	public function handleCheckUser(Nette\Forms\Controls\TextInput $mail)
	{
		$mail = $mail->getValue();
		if ($this->endsWith($mail, "@pirati.cz")) {
			return FALSE;
		}		
		$cislo = $this->users->findAll()->where('username', $mail)->count("*");
		if ($cislo > 0) {
			$uziv = "Mailová adresa je již použita pro jiný účet";
			$uT = "error";
			return FALSE;
		} else {
			$uziv = "Mailovou adresu je možné použít";
			$uT = "success";
			return TRUE;
		}
	}

	protected function createComponentNextForm()
	{
		$form = new Form;
		$form->addSubmit('next', 'další')
				->onClick[] = [$this, "userFormSucceeded"];
		$form->addSubmit('cancel', 'zpátky')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		$form->addProtection();
		return $form;
	}

	protected function createComponentShareForm()
	{
		$control = $this->step5FormFactory->create($this->company);
		$control->onSuccess[] = [$this, "nextStep"];
		$control->onCancel[] = [$this, "formCancelled"];
		return $control;
	}

	protected function createComponentTaxForm()
	{
		$form = new Form;
		$itemsT = $this->taxes->findAll(); /* array(1=>"Daň z příjmu", 3=>"Silniční daň", 4=>"Daň z příjmu fyzických osob",
		  5=>"Daň vybíraná srážkou (srážková daň)",2=>"Daň z přidané hodnoty"); */
		$items = array();
		foreach ($itemsT as $i) {
			$items[$i->id] = $i->name;
		}
		$form->addCheckboxList("taxes2", "Daně:", $items)->setDisabled()
				->addCondition(Form::FILLED)
				->addRule(function(Nette\Forms\Controls\MultiChoiceControl $control) {
					$values = $control->getValue();
					if (!in_array(1, $values)) {
						return FALSE;
					}
					return TRUE;
				}, "Pokud Piráti firmu registrují k jakékoli dani, musí ji zaregistrovat i k dani z příjmů.");
		$form->addCheckboxList("taxes", "", $items)->setDefaultValue([]);
				
		$form->addCheckbox("accountancyConsultation")->setDefaultValue(1);
		$form->addHidden("dph");
		$form->addTextArea("tax_reason")
				->setOption("id", "tax-reason")
				->addConditionOn($form["dph"], Form::EQUAL, 1)
				->addRule(Form::FILLED, "Důvody registrace k DPH musí být vyplněny pro účely FÚ");
		$form->addSubmit('next', 'další')
				->onClick[] = [$this, "userFormSucceeded"];
		$form->addSubmit('cancel', 'zpátky')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		$form->addProtection();
		$this->defaultVals["dph"] = 0;
		if ($this->defaultVals) {
			$form->setDefaults($this->defaultVals);
		}
		return $form;
	}

	public function taxFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$section = $this->session->getSection("general");
		$section = $this->session->getSection("" . $section->stepCounter);
		foreach ($values as $key => $val) {
			$section[$key] = $val;
		}
		$section = $this->session->getSection("general");
		$section->stepCounter += ($section->stepCounter < 5) ? 1 : 0;
		$this->redirect("NewCompany:");
	}

	protected function createComponentNewUserForm()
	{
		$form = new Form;
		$form->addText('mail', 'Emailová adresa:')
				->setRequired("Prosím zadejte email.")
				->addRule(Form::EMAIL, 'Zadejte platný email.')
				->addRule([$this, 'handleCheckUser'], 'Toto uživatelské jméno je již registrováno a nelze ho použít.');
		$form->addText('name', 'Jméno')
				->setRequired('Prosím zadejte jméno.');
		$form->addText('surname', 'Příjmení')
				->setRequired('Prosím zadejte příjmení.');
		$form->addText('tel', 'Telefon')
				->setRequired("Zadejte telefon")
				->addRule(Form::PATTERN, 'Telefonní číslo musí mít 9 číslic', '([0-9]\s*){9}');
		$persons = array();
		$counter = 1;
		if (!$this->company) {
			$this->readFromSession();
		}
		$persons[$counter++] = "není v seznamu";
		foreach ($this->company->getPersons() as $person) {
			$persons[$counter++] = $person->getFullName();
		}
		$form->addText('street', "Ulice")
				->setRequired("Prosím zadejte ulici.");
		$form->addText('street_number', 'Číslo domu')
				->setRequired("Zadejte číslo domu");
		$form->addText('city', "Město")
				->setRequired("Prosím zadejte město.");
		$form->addText('zip_code', 'PSČ')
				->setRequired("Zadjte PSČ")
				->addRule(Form::PATTERN, 'PSČ musí mít 5 číslic', '([0-9]\s*){5}');
		$form->addTextArea('cond', 'Podmínky')
				->setDisabled(True)
				->setDefaultValue("Podmínky .");
		$form->addSubmit('send', 'Uložit')
				->onClick[] = [$this, "userFormSucceeded"]; 
		$form->addSubmit('back', 'Předchozí krok')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		$form->onSuccess[] = [$this, "userFormSucceeded"]; 

		$form->addProtection();
		return $form;
	}

	protected function createComponentBankAccountForm()
	{
		$control = $this->step8FormFactory->create();
		if ($this->defaultVals) {
			$control["form"]->setDefaults($this->defaultVals);
		}
		$control->onSuccess[] = [$this, "bankAccountFormSucceeded"];
		$control->onCancel[] = [$this, "formCancelled"];
		return $control;
	}

	public function bankAccountFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$section = $this->session->getSection("general");
		$section = $this->session->getSection("" . $section->stepCounter);
		foreach ($values as $key => $val) {
			$section[$key] = $val;
		}
		$section = $this->session->getSection("general");
		$section->stepCounter += ($section->stepCounter < 10) ? 1 : 0;
		$this->redirect("NewCompany:");
	}

	protected function createComponentRepresentantsActions()
	{
		$form = new Form;
		$persons = array();
		$counter = 0;
		if (!$this->company) {
			$this->readFromSession();
		}
		foreach ($this->company->getPersons() as $person) {
			$persons[$counter++] = $person->getFullName();
		}
		foreach ($this->company->getTempActingPersons() as $person) {
			$persons[$counter++] = $person->getFullName();
		}
		$form->addCheckboxList("actingPersons", "Jednatelé", $persons)
				->addRule(Form::FILLED, "Musíte vybrat jednatele");
		$options = array();
		foreach ($this->acting->findAll() as $a) {
			$options[$a->id] = $a->name;
		}
		unset($options[4]);
		$form->addRadioList("actions", "možnosti jednatelů", $options)
				->addRule(Form::FILLED, "Musíte vyplnit možnosti jednatelů.")
				->addCondition(Form::EQUAL, 4)
				->toggle("other_option")
				->endCondition();
		$form->addText("other_option", "vlastní specifikace")
				->addConditionOn($form["actions"], Form::EQUAL, 4)//"jiná možnost, vyplňte")
				->addRule(Form::FILLED, "Musí být vyplněno, pokud jste vybrali možnost jiné");
		$form->addSubmit('next', 'další')
				->onClick[] = [$this, "userFormSucceeded"];
		$form->addSubmit('cancel', 'zpátky')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		$form->addProtection();

		if ($this->defaultVals) {
			\Tracy\Debugger::barDump($this->defaultVals);
			$form->setDefaults($this->defaultVals);
		} else {
			//$form->setDefaults(array("actingPersons" => array(0)));
		}
		return $form;
	}

	protected function createComponentSeatForm()
	{
		$control = $this->step3FormFactory->create();
		if ($this->defaultVals) {
			$control["form"]->setDefaults($this->defaultVals);
		}
		$control->onSuccess[] = [$this, "userFormSucceeded"];
		$control->onCancel[] = [$this, "formCancelled"];
		return $control;
	}
	
	/**
	 * Formular pro vyber zivnosti
	 * @param type $name
	 * @return type
	 */
	protected function createComponentStep2Form($name)
	{
		$control = $this->step2FormFactory->create();
		$control->onSuccess[] = [$this, "nextStep"];
		$control->onCancel[] = [$this, "formCancelled"];
		return $control;
	}

	protected function createComponentFieldsOfBussinesForm()
	{
		
	}

	public function formCancelled()
	{
		$this->proccessFacade->stepBack();
		$this->redirect('default');
	}

	public function handleSetType($type)
	{
		$data = $this->proccessFacade->getStep5Data();
		if (isset($data["messFractions"]) && $data["messFractions"] && $type == "simple") {
			$this->flashMessage("Ve zjednodušené verzi lze mít pouze základní podíly, tedy podíly odpovídající poměrně vkladům společníků. Prosím vraťte se do kroku 5 a upravte údaj.");
			$this->redirect("this");
		} else {
			$this->session->getSection("companyType")->type = (string)$type;
			$this->flashMessage("Rozsah byl úspěšně nastaven");
			$this->redirect("this");
		}
	}
	
	protected function createComponentDiscountForm($name)
	{
		$form = new Form;
		$form->addText("code")->setRequired("Zadejte prosím slevový kód");
		$form->addSubmit("send","Potvrdit");
		$form->onSuccess[] = [$this, "setDiscountCode"];
		return $form;
	}
	
	public function setDiscountCode(Form $form) 
	{
		$values = $form->getValues(TRUE);
		try {
			$this->discountFacade->setCode($values["code"]);
		}
		catch (Model\Facade\InvalidCodeExcetion $e) {
			$this->template->errorCode = TRUE;
			return;
		}
		$this->flashMessage("Sleva byla uplatněna");
		$this->redirect("this");
	}
}
