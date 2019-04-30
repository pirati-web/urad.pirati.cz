<?php

namespace App\Model\Facade;

/**
 * DiscountFacade
 */
class DiscountFacade
{
	/** @var \Nette\Http\SessionSection */
	protected $session;
	
	protected $vouchers = array(
		"xport16" => array(
			"name" => "Sleva 500Kč",
			"value" => 500
		)
	);
	
	public function __construct(\Nette\Http\Session $session)
	{
		$this->session = $session->getSection("general");
	}
	
	public function getCode()
	{
		return $this->session->discountCode;
	}
	
	public function setCode($code)
	{
		if (!isset($this->vouchers[$code])) {
			throw new InvalidCodeExcetion;
		}
		$this->session->discountCode = $code;
	}
	
	public function getVoucher($code)
	{
		if (!isset($this->vouchers[$code])) {
			throw new VoucherNotFoundExcetion;
		}
		return $this->vouchers[$code];
	}
}

class VoucherNotFoundExcetion extends \Exception {}
class InvalidCodeExcetion extends \Exception {}
