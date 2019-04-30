<?php

class Company {
        
    public $name;
    private $persons = array();
    private $actingPersons = array();
    private $tempActingPersons = array();
    private $areas = array();
    private $seat;
    private $bank;
    private $taxes = array();
    private $capital;
    private $shares;
    private $actionsOfAP;
    private $user;
    private $actingIndexes;
    private $actions;
    private $id;
    private $payments;
    private $nonFreeAreas = array();
	
    /** @var \App\Model\FieldsCategory */
	private $fieldsCategory; 
    
	private $paymentInfo;
    private $otherServices;
	private $compliance = FALSE;
	private $type = "full";
	
	/** @var \AccountancyService|NULL */
	private $accountancy;
	
	/** @var \PhoneService|NULL */
	private $phoneService;
	
	/** @var \PostService|NULL */
	private $postService;
			
    
    function __construct($name, $cat) {
        $this->name = $name;
        $this->fieldsCategory = $cat;
    }
	
	function setType($type)
	{
		switch ($type) {
			case "full":
			case "simple":
				$this->type = $type;
			break;
			default:
				$this->type = "full";
			break;
		}
	}
	
	function getType()
	{
		return $this->type;
	}
    
	/**
	 * 
	 * @return \App\Model\FieldsCategory
	 */
    function getFieldsCategory() {
        return $this->fieldsCategory;
    }

    function getOtherServices() {
        return array();
        //return $this->otherServices;
    }

    function setFieldsCategory($fieldsCategory) {
        $fieldsCategory->merge($this->fieldsCategory);
        $this->fieldsCategory = $fieldsCategory;
    }

    function setOtherServices($otherServices) {
        $this->otherServices = $otherServices;
    }
    
    function getPaymentInfo() {
        return $this->paymentInfo;
    }

    function setPaymentInfo($paymentInfo) {
        $this->paymentInfo = $paymentInfo;
    }

        
    function getPayments() {
        return $this->payments;
    }

    function setPayments($payments) {
        $this->payments = $payments;
    }

        function setId($id){
        $this->id = $id;
    }
    
    function getId(){
        return $this->id;
    }
    
    function getActions() {
        return $this->actions;
    }

    function setActions($actions) {
        $this->actions = $actions;        
    }   

    function getActingIndexes() {
        return $this->actingIndexes;
    }

    function setActingIndexes($actingIndexes) {
        $this->actingIndexes = $actingIndexes;
    }
    
    private function setNonFreeAreas($areas){
        $this->nonFreeAreas = $areas;
    }
    
    function getNonFreeAreas(){
        return $this->nonFreeAreas;
    }
        
    function getAreas(){
        return $this->areas;
    }
	
	function hasAgent()
	{
		foreach ($this->areas as $id => $_) {
			$pom = $this->fieldsCategory->getFiledsTypes()->where("fields_id = ?",$id)->fetch();
			if ($pom) {
				 if ($pom->type_id != 2) {
					 return TRUE;
				 }
			}
		}
		return FALSE;
	}
    
    function setAreas($areas){
        $this->areas = $areas;
        $nonFreeAreas = array();
        foreach($areas as $index=>$a){
            //\Tracy\Debugger::barDump(count($this->fieldsCategory->getTypeView()->where("id=? AND type_id<>2",$index)));
            if (count($this->fieldsCategory->getTypeView()->where("id=? AND type_id<>2",$index))>0){
                $nonFreeAreas[$index] = $a;
            }
        }
        $this->setNonFreeAreas($nonFreeAreas);
    }
    
    function getName() {
        return $this->name;
    }

    function getPersons() {
        return $this->persons;
    }

    function getActingPersons(){
        return $this->actingPersons;
    }
    
    function setActingPersons($actingPersons){
        $this->actingPersons = $actingPersons;
    }
            
    function getTempActingPersons(){
        return $this->tempActingPersons;
    }
    
    function setTempActingPersons($tempActingPersons){
        $this->tempActingPersons = $tempActingPersons;
    }
    
	/**
	 * 
	 * @return \IPlace|\Place|\SeatService
	 */
    function getSeat() {
        return $this->seat;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setPersons($persons) {
        $this->persons = $persons;
    }

    function setSeat($seat) {
        $this->seat = $seat;        
    }

    function getBank() {
        return $this->bank;
    }

    function getTaxes() {
        return $this->taxes;
    }

    function getCapital() {
        return $this->capital;
    }

    function getShares() {
        return $this->shares;
    }

    function getActionsOfAP() {
        return $this->actionsOfAP;
    }

    function getUser() {
        return $this->user;
    }

    function setBank($bank) {
        $this->bank = $bank;
    }

    function setTaxes($taxes) {
        $this->taxes = $taxes;
    }

    function setCapital($capital) {
        $this->capital = $capital;
    }

    function setShares($shares) {
        if (is_array($shares)){
            $this->shares = $shares;
        }else{
            $this->shares = ($shares) ? explode(",",$shares) : $shares;
        }
    }

    function setActionsOfAP($actionsOfAP) {
        $this->actionsOfAP = $actionsOfAP;
    }
    
    function setUser($user) {
        $this->user = $user;
    }
	
	public function setAccountancy(\AccountancyService $service = NULL) 
	{
		$this->accountancy = $service;
	}

	/**
	 * 
	 * @return \AccountancyService|NULL
	 */
	public function getAccountancy() 
	{
		return $this->accountancy;
	}
	
	public function setCompliance($value)
	{
		$this->compliance = $value;
	}
	
	
	public function getCompliance()
	{
		return $this->compliance;
	}

	public function setPhoneService(\PhoneService $service)
	{
		$this->phoneService = $service;
	}
	
	/**
	 * 
	 * @return \PhoneService|NULL
	 */
	public function getPhoneService()
	{
		return $this->phoneService;
	}
	
	public function setPostService(\PostService $service)
	{
		$this->postService = $service;
	}
	
	/**
	 * 
	 * @return \PostService|NULL
	 */
	public function getPostService()
	{
		return $this->postService;
	}
}
