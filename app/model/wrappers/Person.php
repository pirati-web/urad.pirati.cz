<?php

class Person implements abstractPerson
{

	private $choice;
	private $name;
	private $surname;
	private $date;
	private $title_pre;
	private $title_suf;
	private $place;
	private $tel;
	private $dbId;
	private $person_id;

	function __construct($choice, $name = "", $surname = "", $person_id = "", $place = "", $tel = "", $title_pre = "", $title_suf = "")
	{
		$numargs = func_num_args();
		if ($numargs == 1) {
			$section = $choice;
			$this->choice = $section["choice"];
			$this->name = $section["name"];
			$this->surname = $section["surname"];
			$this->person_id = $section["person_id"];
			$this->date = $this->RCtoDate($section["person_id"]);
			$this->title_pre = $section["title_pre"];
			$this->title_suf = $section["title_suf"];
			$this->place = new Place($section);
			$this->tel = $section["tel"];
		} else {
			$this->choice = $choice;
			$this->name = $name;
			$this->surname = $surname;
			$this->person_id = $person_id;
			$this->date = $this->RCtoDate($person_id);
			$this->title_pre = $title_pre;
			$this->title_suf = $title_suf;
			$this->place = $place;
			$this->tel = $tel;
		}
	}

	function RCtoDate($rc)
	{
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
		$d = \Nette\Utils\DateTime::createFromFormat("d.m.Y", $day . "." . $month . "." . $year);
		return $d;
	}

	function getFullTextOutput(){
		$person_output = $this->getFullName().', datum nar. '.$this->getBirthDate().', trvale bydlištěm '.$this->getSeat()->notarGetFullAddress();
		return $person_output;
	}

	function getId(){
		return $this->getPersonId();
	}

	function getPersonId()
	{
		return $this->person_id;
	}
	
	function getBirthDate()
	{
		$year = substr($this->person_id, 0, 2);
		$month = substr($this->person_id, 2, 2);
		$day = substr($this->person_id, 4, 2);
		if ($month > 12) { // zena
			$tmp = $month - 70; // po roce 2004
			if ($tmp < 0) { 
				$month = $month - 50; // do roku 2004
			} else {
				$month = $tmp;
			}
		}
		return \Nette\Utils\DateTime::createFromFormat("y-m-d", "$year-$month-$day")->format("d.m.Y");
	}

	function setPersonId($person_id)
	{
		$this->person_id = $person_id;
	}

	function getTel()
	{
		return $this->tel;
	}

	function setTel($tel)
	{
		$this->tel = $tel;
	}

	function getChoice()
	{
		return $this->choice;
	}

	function getName()
	{
		return $this->name;
	}

	function getSurname()
	{
		return $this->surname;
	}

	function getFullName()
	{
		$name = $this->name . " " . $this->surname;
		$name = ($this->title_pre) ? $this->title_pre . " " . $name : $name;
		$name = ($this->title_suf) ? $name . " " . $this->title_suf : $name;
		return $name;
	}

	function getDate()
	{
		return $this->date;
	}

	function getTitle_pre()
	{
		return $this->title_pre;
	}

	function getTitle_suf()
	{
		return $this->title_suf;
	}

	function getPlace()
	{
		return $this->place;
	}

	function setChoice($choice)
	{
		$this->choice = $choice;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function setSurname($surname)
	{
		$this->surname = $surname;
	}

	function setDate($date)
	{
		$this->date = $date;
	}

	function setTitle_pre($title_pre)
	{
		$this->title_pre = $title_pre;
	}

	function setTitle_suf($title_suf)
	{
		$this->title_suf = $title_suf;
	}

	function setPlace($place)
	{
		$this->place = $place;
	}

	function dataAsSqlInput()
	{
		$data = array("choice" => $this->choice,
			"name" => $this->name,
			"surname" => $this->surname,
			"date" => $this->date,
			"person_id" => $this->person_id,
			"title_pre" => $this->title_pre,
			"title_suf" => $this->title_suf,
			"tel" => $this->tel);
		return $data;
	}

	public function getDbId()
	{
		return $this->dbId;
	}

	public function setDbId($dbId)
	{
		$this->dbId = $dbId;
	}

	public function getSeat()
	{
		return $this->place;
	}

	public function getIco()
	{
		return "";
	}
}
