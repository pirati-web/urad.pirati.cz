<?php

class ExistingCompany implements abstractPerson{
    
    private $name;
    private $ico;
    private $seat;
    private $dbId;
    private $choice;
    
    function __construct($name, $choice="", $ico="",$seat="") {
        $numargs = func_num_args();
        if ($numargs==1){
            $section = $name;
            $this->name = $section["company_name"];
            $this->ico = $section["ico"];
            $this->choice = $section["choice"];
            $this->seat = $this->parseSeat($section["seat"]);
        }else{
            $this->name = $name;
            $this->ico = $ico;
            $this->seat = $seat;
            $this->choice = $choice;
        }
    }
    
    
    function parseSeat($textSeat){
        return new Place(array("street"=>$textSeat));        
    }

    function getId() {
        return $this->getIco();
    }
    
    function getFullName() {
        return $this->name;//." ".$this->ico;
    }

    function getFullTextOutput() {
        $company_output = $this->getFullName().' se sídlem '.$this->getSeat()->notarGetFullAddress().', identifikační číslo '.$this->getIco();
        return $company_output;
    }
    
    function getName() {
        return $this->name;
    }

    function getIco() {
        return $this->ico;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setIco($ico) {
        $this->ico = $ico;
    }

    public function setDbId($dbId) {
        $this->dbId = $dbId;
    }

    public function getDbId() {
        return $this->dbId;
    }

    public function dataAsSqlInput() {
       $data = array("name"=>$this->name,
                     "ico"=>$this->ico,
                     "choice"=>$this->choice);
       return $data;
    }

    public function getSeat() {
        return $this->seat;
    }

    public function getDate() {
        return null;
    }

}
