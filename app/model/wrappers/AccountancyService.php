<?php

/**
 * Trida spocita cenu vybraneho tarifu podle voleb klienta
 */
class AccountancyService
{
	/** @var \Nette\Database\Table\ActiveRow */
	protected $row;
	
	protected $vat = FALSE;
	protected $nubmerOfCars = 0;
	protected $numberOfEmployees = 0;

	/**
	 * 
	 * @param \Nette\Database\Table\ActiveRow $row zvoleny tarif bud z tabulky accountancy_service nebo company_accountancy_service
	 * @param type $vat
	 * @param type $number_cars
	 * @param type $number_employees
	 */
	public function __construct(\Nette\Database\Table\ActiveRow $row, $vat, $number_cars, $number_employees)
	{
		$this->row = $row;
		$this->vat = $vat;
		$this->nubmerOfCars = $number_cars;
		$this->numberOfEmployees = $number_employees;
	}
	
	public function getName()
	{
		return $this->row->name;
	}
	
	public function getItemPrice()
	{
		if ($this->vat) {
			return $this->row->item_price + 2;
		}
		return $this->row->item_price;
	}

	/**
	 * Spocita mesicni cenu tarifu
	 * @return type
	 */
	public function getPrice()
	{
		//if ($this->row) {
		if ($this->row->fee) { // pusaly
			$itemsPerMonth = $this->row->items_month;
			$itemPrice = $this->getItemPrice();
			$monthlyPrice = $itemPrice*$itemsPerMonth;
			$cars = $this->nubmerOfCars;
			if ($cars > 0) {
				$monthlyPrice += 400; // za prvni vozidlo 400
				$cars--;
				if ($cars > 0) { // za dalsi 100
					$monthlyPrice += $cars * 100;
				}
			}
			if ($this->numberOfEmployees > 0) {
				$monthlyPrice += $this->numberOfEmployees * 200;
			}
			return $monthlyPrice;
		} else {
			$monthlyPrice = 0;
			$cars = $this->nubmerOfCars;
			if ($cars > 0) {
				$monthlyPrice += 400; // za prvni vozidlo 400
				$cars--;
				if ($cars > 0) { // za dalsi 100
					$monthlyPrice += $cars * 100;
				}
			}
			if ($this->numberOfEmployees > 0) {
				$monthlyPrice += $this->numberOfEmployees * 200;
			}
			return $monthlyPrice;
		}
		//} else {
		//	return 0;
		//}
	}
	
	public function getSource()
	{
		return $this->row;
	}
	
	public function getNumberOfCars()
	{
		return $this->nubmerOfCars;
	}
	
	public function getNumberOfEmployees()
	{
		return $this->numberOfEmployees;
	}
	
	public function getVat()
	{
		return $this->vat;
	}
}
