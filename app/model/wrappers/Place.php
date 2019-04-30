<?php

class Place implements IPlace {
    
    private $street;
    private $streetNumber;
    private $cityPart;
    private $city;
    private $zipCode;
    private $state;
    private $ownerName;
    
    function __construct($street, $streetNumber="", $cityPart="", $city="", $zipCode="", $state="",$owner_name="") {
        $numargs = func_num_args();
        if ($numargs==1){
            $section = $street;
            $this->street = $section["street"];
            if (!is_array($street)){
                $this->streetNumber = $section["street_number"];
                $this->cityPart = $section["city_part"];
                $this->city = $section["city"];
                $this->zipCode = $section["zip_code"];
                $this->state = $section["state"];
                $this->ownerName = $section["owner_name"];            
            }
        }else{
            $this->street = $street;
            $this->streetNumber = $streetNumber;
            $this->cityPart = $cityPart;
            $this->city = $city;
            $this->zipCode = $zipCode;
            $this->state = $state;
            $this->ownerName = $owner_name;
        }
    }

    function getFullAddress(){
        if ($this->streetNumber){
            return trim($this->street." ".$this->streetNumber.", ".
                $this->city.", ".$this->zipCode);
        }else{
            return $this->street;
        }
    }

    function notarGetFullAddress(){
        if ($this->streetNumber){
            return trim($this->street." ".$this->streetNumber.", ".
                $this->zipCode.", ".$this->city);
        }else{
            return $this->street;
        }
    }
    
    
    function getOwnerName() {
        return $this->ownerName;
    }

    function setOwnerName($ownerName) {
        $this->ownerName = $ownerName;
    }
    
    function getOwner_count() {
        return $this->owner_count;
    }

    function setOwner_count($owner_count) {
        $this->owner_count = $owner_count;
    }

        function getStreet() {
        return $this->street;
    }

    function getStreetNumber() {
        return $this->streetNumber;
    }

    function getCityPart() {
        return $this->cityPart;
    }

    function getCity() {
        return $this->city;
    }

    function getZipCode() {
        return $this->zipCode;
    }

    function getState() {
        return $this->state;
    }

    function setStreet($street) {
        $this->street = $street;
    }

    function setStreetNumber($streetNumber) {
        $this->streetNumber = $streetNumber;
    }

    function setCityPart($cityPart) {
        $this->cityPart = $cityPart;
    }

    function setCity($city) {
        $this->city = $city;
    }

    function setZipCode($zipCode) {
        $this->zipCode = $zipCode;
    }

    function setState($state) {
        $this->state = $state;
    }

    function dataAsSqlInput(){
        $data = array("street"=>$this->street,
                      "street_number"=>$this->streetNumber,
                      "city"=>$this->city,
                      "zip_code"=>$this->zipCode,
                      "state"=>$this->state,
                      "owner_name"=>$this->ownerName);
        return $data;
    }

	public function hasOwnSeat()
	{
		return TRUE;
	}

}
