<?php

/**
 *
 */
class SeatService implements IPlace
{

	/** @var \Nette\Database\Table\ActiveRow */
	protected $seatRow;

	/** @var \Nette\Database\Table\ActiveRow */
	protected $tarifRow;

	public function __construct(\Nette\Database\Table\ActiveRow $seatRow, \Nette\Database\Table\ActiveRow $tarifRow)
	{
		$this->seatRow = $seatRow;
		$this->tarifRow = $tarifRow;
	}

	public function hasOwnSeat()
	{
		return FALSE;
	}

	public function getName()
	{
		return $this->seatRow->name;
	}
	
	public function getVat()
	{
		return $this->seatRow->vat;
	}

	public function getPrice()
	{
		$months = $this->getMonths();
		return $this->seatRow["price_$months"] * $months;
	}

	public function getMonths()
	{
		return $this->tarifRow->months;
	}

	public function getPriceWithVat()
	{
		return $this->getPrice() * ((100 + $this->seatRow->vat) / 100);
	}

	public function getFullAddress()
	{
		if ($this->getStreetNumber()) {
			return trim($this->getStreet() . " " . $this->getStreetNumber() . ", " .
				$this->getCity() . ", " . $this->getZipCode());
		} else {
			return $this->getStreet();
		}
	}

	public function getSource()
	{
		return $this->seatRow;
	}

	function dataAsSqlInput()
	{
		$data = array(
			"street" => $this->seatRow->street,
			"street_number" => $this->seatRow->street_number,
			"city" => $this->seatRow->city,
			"zip_code" => $this->seatRow->zip_code,
			"state" => $this->seatRow->state,
			"owner_name" => ""
		);
		return $data;
	}

	function getOwnerName()
	{
		return "";
	}

	function getStreet()
	{
		return $this->seatRow->street;
	}

	function getStreetNumber()
	{
		return $this->seatRow->street_number;
	}

	function getCityPart()
	{
		return $this->seatRow->city_part;
	}

	function getCity()
	{
		return $this->seatRow->city;
	}

	function getZipCode()
	{
		return $this->seatRow->zip_code;
	}

	function getState()
	{
		return $this->seatRow->state;
	}

}
