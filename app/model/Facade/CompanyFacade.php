<?php

namespace App\Model\Facade;

use Nette;
use App\Model;

class CompanyFacade extends BaseFacade
{
	protected $salt = "asdjjlk76hjk89HJKH"; //docasne
	
	/** @var Model\Company */
	protected $companies;

	/** @var Model\Person */
	protected $persons;

	/** @var Model\CompanyPerson */
	protected $companyPerson;
	
	/** @var Model\CompanyProgress */
	protected $companyProgress;

	/** @var Model\Seat */
	protected $seat;

	/** @var Model\CompanySeat */
	protected $companySeat;

	/** @var Model\FieldsCompany */
	protected $fieldsCompany;

	/** @var Model\Acting */
	protected $acting;

	/** @var Model\Taxes */
	protected $taxes;

	/** @var Model\Service\DocumentService */
	protected $documentService;
	
	/** @var Model\CompanyAccountancyService */
	protected $companyAccountancy;
	
	/** @var Model\SeatService */
	protected $seatService;
	
	/** @var Model\PostService */
	protected $postService;
	
	/** @var Model\PhoneService */
	protected $phoneService;
	
	/** @var Model\UserManager */
	protected $um;
	
	/** @var Model\Users */
	protected $users;
	
	public $checkFlag;
	
	

	public function __construct(\App\Transaction $transaction, Model\Service\DocumentService $documentService, Model\Company $company, Model\Person $person, 
			Model\CompanyPerson $companyPerson, Model\CompanyProgress $companyProgress, Model\Seat $seat, Model\CompanySeat $companySeat,
			Model\FieldsCompany $filedsCompany, Model\Acting $acting, Model\Taxes $taxes, Model\CompanyAccountancyService $companyAccountancy,
			Model\UserManager $userManager, Model\Users $users, Model\SeatService $seatService, Model\PostService $postService, Model\PhoneService $phoneService)
	{
		parent::__construct($transaction);
		$this->documentService = $documentService;
		$this->companies = $company;
		$this->persons = $person;
		$this->companyPerson = $companyPerson;
		$this->seat = $seat;
		$this->companySeat = $companySeat;
		$this->companyProgress = $companyProgress;
		$this->fieldsCompany = $filedsCompany;
		$this->acting = $acting;
		$this->taxes = $taxes;
		$this->companyAccountancy = $companyAccountancy;
		$this->um = $userManager;
		$this->users = $users;
		$this->seatService = $seatService;
		$this->phoneService = $phoneService;
		$this->postService = $postService;
	}

	public function createCompany(\Nette\Security\User $user, \Company $company, $section9, \ChargeList $charges)
	{
		if ($section9->mail == "steve@strebl.com") {
			$this->documentService->setTestMode(TRUE);
		}
		try {
			$this->transaction->beginTransaction();
			if (!$user->isLoggedIn()) {
				$userId = $this->createUser($section9);
			} else {
				$userId = $user->getId();
			}

			$companyID = $this->saveCompany($userId, $company);
			if (!$user->isLoggedIn()) {
				$this->savePaymentInfo($companyID, $company, $userId);
			} else {
				$this->savePaymentInfoLoggedUser($companyID, $userId);
			}
			$this->saveFieldOfBussines($companyID, $company);
			$this->saveSeat(NULL, $company->getSeat(), $companyID);
			$this->savePersons($companyID, $company);
			$this->saveActingPersonsToDB($companyID, $company);
			$this->saveTaxes($companyID, $company);
			$this->saveAccountancy($companyID, $company);
			$this->saveSeatServices($companyID, $company);
			$company->setId($companyID);
			$this->createDocs($userId, $company, $charges);
			// this is with dummy super admin
			$this->createProgress($companyID);
			$this->transaction->commit();
		}
		catch (\Exception $e) {
			$this->transaction->rollBack();
			throw $e;
		}
	}
	
	public function createUser($values)
	{
		$pass = $values['password'];
		$name = $values['mail'];
		$row = $this->um->add($values, "gjtudie45". time()); // ucty budou nepristupne
		$id_of_new_user = $row->id;

		$smallValues = array("name" => $values["name"],
			"surname" => $values["surname"],
			"mail" => $values["mail"]
		);

		$smallValues['checkTimestamp'] = date("Y-m-d H-i-s");
		$smallValues['checkFlag'] = 'n' . $this->calculateHash($values['mail'] . time(), $this->salt, "md5");
		$this->checkFlag = $smallValues['checkFlag']; // docasne reseni
		unset($values['username']);
		unset($values['password']);
		unset($values['password2']);
		unset($values['existence']);
		unset($values['cond']);
		unset($values['conditions']);
		unset($values['persons']);

		$this->users->findById($id_of_new_user)->update($smallValues);
		return $id_of_new_user;
	}

	protected function createDocs($userId, \Company $company, \ChargeList $charges)
	{
		$this->documentService->createCestneProhlaseniJednatele($company);
		$this->documentService->createChecklist($company);
		$invNumber = $this->documentService->createZalohovaFaktura($company, $charges, $userId);
		$this->documentService->createFaktura($company, $invNumber, $charges);
		$this->documentService->createVyzva($company, $invNumber);
		$this->documentService->createPlnaMocJednatele($company);
		$this->documentService->createPlnaMocSpolecnika($company);
		$this->documentService->createProhlaseniSpravceVkladu($company);
		$this->documentService->createSouhlasSUmistenimSidla($company);
		$this->documentService->createSpolecenskaSmlouva($company);
		$this->documentService->createSpolecenskaSmlouvaEditovatelna($company);
	}

	protected function saveCompany($userId, \Company $company)
	{
		$userID = ($userId) ? $userId : $company->getUser();
		$companyName = $company->getName();
		$bank = $company->getBank();
		$otherServices = json_encode(array());
		$last_id = $this->companies->insert(array("user_id" => $userID,
			"name" => $companyName,
			"desc" => $otherServices,
			"value" => $company->getCapital(),
			"type" => $company->getType(),
			"bank" => $bank["id"]));
		return $last_id;
	}

	protected function savePaymentInfo($company_id, \Company $company, $userId)
	{
		$person = $company->getPaymentInfo();
		$person->setChoice(1);
		$seat_id = $this->saveSeat(NULL, $person->getSeat());
		$data = $person->dataAsSqlInput();
		$data["place"] = $seat_id;
		$data["user_id"] = $userId;
		$last_id = $this->persons->insert($data);
		$this->companyPerson->insert(array("company_id" => $company_id,
			"person_id" => $last_id,
			"acting" => 2
		));
	}
	
	protected function createProgress($company_id){
		$this->companyProgress->insert(array("company_id" => $company_id, "note" => "webform filled"));
	}

	protected function savePaymentInfoLoggedUser($company_id, $userId)
	{
		$person = $this->persons->findAll()->where("user_id = ?",$userId)->fetch();
		$this->companyPerson->insert(array("company_id" => $company_id,
			"person_id" => $person->id,
			"acting" => 2
		));
	}

	protected function saveSeat($button, \IPlace $seat = NULL, $companyId = NULL)
	{
		$last_id = $this->seat->insert($seat->dataAsSqlInput());

		// insert conection of seat to table company_seat
		if ($companyId) {
			$seat_id = $this->companySeat->insert(array("company_id" => $companyId,
				"seat_id" => $last_id));
		}
		return $last_id;
	}

	protected function saveFieldOfBussines($companyId, \Company $company)
	{   
		foreach ($company->getAreas() as $fieldId => $val) {
			$this->fieldsCompany->insert(array("company_id" => $companyId,
				"fields_id" => $fieldId));
		}
	}

	protected function savePersons($company_id, \Company $company)
	{
		$shares = $company->getShares();
		foreach ($company->getPersons() as $index => $person) {
			$seat_id = $this->saveSeat(NULL, $person->getSeat());
			$data = $person->dataAsSqlInput();
			$data["place"] = $seat_id;
			$last_id = $this->persons->insert($data);
			$this->companyPerson->insert(array("company_id" => $company_id,
				"person_id" => $last_id,
				"share" => $shares[$index]));
		}
	}

	protected function saveActingPersonsToDB($company_id, \Company $company)
	{
		$values = $company->getActions();
		$values["company_id"] = $company_id;
		unset($values["name"]);
		$this->acting->insert($values);
		$persons = $company->getPersons();
		$tempActPersons = $company->getTempActingPersons();
		$personCount = count($company->getPersons());
		foreach ($company->getActingIndexes() as $index) {
			if ($index < $personCount) {
				$person = $persons[$index];
			} else {
				$person = $tempActPersons[$index - $personCount];
			}
			$seat_id = $this->saveSeat(NULL, $person->getSeat());
			$data = $person->dataAsSqlInput();
			$data["place"] = $seat_id;
			$last_id = $this->persons->insert($data);
			$this->companyPerson->insert(array("company_id" => $company_id,
				"person_id" => $last_id,
				"acting" => 1));
		}
	}

	private function saveTaxes($company_id, \Company $company)
	{
		foreach ($company->getTaxes() as $index => $text) {
			if ($index == 2) {
				$reason = $text["tax_reason"];
				$data = array("company_id" => $company_id, "tax_id" => $index, "desc" => $reason);
			} else {
				$data = array("company_id" => $company_id, "tax_id" => $index);
			}
			$this->taxes->insertCompanyTaxes($data);
		}
	}
	
	protected function saveAccountancy($company_id, \Company $company)
	{
		if ($company->getAccountancy()) {
			$accountancyRow = $company->getAccountancy()->getSource();
			$this->companyAccountancy->insert(array(
				"company_id" => $company_id,
				"name" => $company->getAccountancy()->getName(),
				"vat" => $company->getAccountancy()->getVat(),
				"number_cars" => $company->getAccountancy()->getNumberOfCars(),
				"number_employees" => $company->getAccountancy()->getNumberOfEmployees(),
				"item_price" => $accountancyRow->item_price,
				"items_month" => $accountancyRow->items_month,
				"fee" => $accountancyRow->fee,
				"new_item_price" => $company->getAccountancy()->getItemPrice(),
				"new_item_price_discont" => $accountancyRow->fee ? NULL : ($company->getAccountancy()->getItemPrice()/2)
			));
		}
	}
	
	protected function saveSeatServices($companyId, \Company $company)
	{
		$seat = $company->getSeat();
		if (!$seat->hasOwnSeat()) {
			$this->seatService->addToCompany(array(
				"company_id" => $companyId,
				"months" => $seat->getMonths(),
				"price_12" => $seat->getSource()->price_12,
				"price_24" => $seat->getSource()->price_24,
				"price_36" => $seat->getSource()->price_36,
				"total_price" => $seat->getPrice(),
				"name" => $seat->getName()
			));
			
			$phone = $company->getPhoneService();
			if ($phone->getPrice()) {
				$this->phoneService->addToCompany(array(
					"company_id" => $companyId,
					"name" => $phone->getName(),
					"info" => $phone->getInfo(),
					"months" => $phone->getMonths(),
					"price" => $phone->getSource()->price,
					"total_price" => $phone->getPrice()
				));
			}
			
			$post = $company->getPostService();
			if ($post->getPrice()) {
				$this->postService->addToCompany(array(
					"company_id" => $companyId,
					"name" => $post->getName(),
					"info" => $post->getInfo(),
					"months" => $post->getMonths(),
					"price" => $post->getSource()->price,
					"total_price" => $post->getPrice()
				));
			}
		}
	}
	
	protected function calculateHash($password, $salt, $param = null)
	{
		if ($param == "md5") {
			return md5($password . str_repeat($salt, 10));
		} else {
			return hash('sha512', $password . str_repeat($salt, 10));
		}
	}

}
