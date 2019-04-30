<?php

class ForeignSubject implements abstractPerson {

    private $name;
    private $text;
    private $dbId;
    private $choice;

    function __construct($name, $choice="", $text = "", $seat = "") {
        $numargs = func_num_args();
        if ($numargs == 1) {
            $section = $name;
            $this->text = $section["foreign"];
            $this->name = "";//zahraniční subjekt";
            $this->seat = new Place("","");//$this->parseSeat("dummy seat");
            $this->choice = $section["choice"];
        } else {
            $this->name = $name;
            $this->text = $text;
            $this->seat = $seat;
            $this->choice = $choice;
        }
    }

    function parseSeat($textSeat) {
        return new Place(array("street" => $textSeat));
    }

    function getFullName() {
        return ($this->name) ? ($this->name)." ".$this->text : $this->text; //." ".$this->ico;
    }

    function getName() {
        return $this->name;
    }

    function getIco() {
        return null;//$this->ico;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setIco($ico) {
        $this->ico = $ico;
    }

    function setSeat($seat) {
        $this->seat = $seat;
    }
    
    public function setDbId($dbId) {
        $this->dbId = $dbId;
    }

    public function getDbId() {
        return $this->dbId;
    }

    public function dataAsSqlInput() {
        $data = array("name" => $this->name,
                      "text" => $this->text,
                      "choice" => $this->choice);
        return $data;
    }

    public function getSeat() {
        return $this->seat;
    }

    function getText() {
        return $this->text;
    }

    function setText($text) {
        $this->text = $text;
    }

    public function getDate() {
        return null;
    }

}
