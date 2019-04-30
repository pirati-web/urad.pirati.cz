<?php

class PhoneService
{
	/** @var \Nette\Database\Table\ActiveRow */
	protected $row;
	
	/** @var \Nette\Database\Table\ActiveRow */
	protected $tarifRow;
	
	public function __construct(\Nette\Database\Table\ActiveRow $row, \Nette\Database\Table\ActiveRow $tarifRow)
	{
		$this->row = $row;
		$this->tarifRow = $tarifRow;
	}
	
	public function getName()
	{
		return $this->row->name;
	}
	
	public function getInfo()
	{
		return $this->row->info;
	}
	
	public function getPrice()
	{
		$basePrice = $this->row->price;
		$months = $this->tarifRow->months;
		return $basePrice * $months;
	}
	
	public function getPriceWithVat()
	{
		return $this->getPrice() * ((100+$this->row->vat)/100);
	}
	
	public function getMonths()
	{
		return $this->tarifRow->months;
	}
	
	public function getSource()
	{
		return $this->row;
	}
	
	public function getVat()
	{
		return $this->row->vat;
	}
}
