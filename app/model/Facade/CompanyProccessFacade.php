<?php

namespace App\Model\Facade;
use Nette\Http\Session;
use Nette\Http\SessionSection;

class CompanyProccessFacade extends BaseFacade
{
	const STEP1_NAMESPACE = "1";
	const STEP2_NAMESPACE = "2";
	const STEP5_NAMESPACE = "5";
	
	/** @var Session */
	protected $session;
	
	public function __construct(\App\Transaction $transaction, Session $session)
	{
		parent::__construct($transaction);
		$this->session = $session;
	}
	
	public function saveStep1Data($values)
	{
		$this->saveToSession($this->getSection(self::STEP1_NAMESPACE), $values);
	}
	
	public function getStep1Data()
	{
		return $this->readFromSession($this->getSection(self::STEP1_NAMESPACE));
	}

	public function saveStep2Data($values)
	{
		$this->saveToSession($this->getSection(self::STEP2_NAMESPACE), $values);
	}
	
	public function getStep2Data()
	{
		return $this->readFromSession($this->getSection(self::STEP2_NAMESPACE));
	}
	
	public function saveStep5Data($values)
	{
		$this->saveToSession($this->getSection(self::STEP5_NAMESPACE), $values);
	}
	
	public function getStep5Data()
	{
		return $this->readFromSession($this->getSection(self::STEP5_NAMESPACE));
	}
	
	public function stepBack()
	{
		$section = $this->getSection("general");
		if ($section->stepCounter) {
			$section->stepCounter--;
		}
	}
	
	public function nextStep()
	{
		$section = $this->getSection("general");
		$section->stepCounter += ($section->stepCounter < 10) ? 1 : 0;
	}

	protected function saveToSession(SessionSection $section, $values)
	{
		foreach ($values as $key => $val) {
			$section[$key] = $val;
		}
	}
	
	protected function readFromSession(SessionSection $section)
	{
		$data = array();
		foreach ($section as $key => $value) {
			$data[$key] = $value;
		}
		return $data;
	}
	
	protected function getSection($section)
	{
		return $this->session->getSection($section);
	}
}
