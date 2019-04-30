<?php


/**
 * Stara se o seznam poplatku za zalozeni firmy. 
 * Pouziva se v pravem sloupci na webu a ve fakturach
 */
class ChargeList
{
	const CATEGORY_REGULAR = "regular_charges";
	const CATEGORY_ADDITIONAL = "additional_charges";
	
	protected $items = array();
	
	protected $totalSum = 0; // suma cen s DPH nebo cen, kde se DPH neuctuje - suma k zaplaceni
	protected $sumWithoutVat = 0; // suma pouze cen bez DPH. Polozky bez DPH tu nejsou vubec
	protected $sumVat = 0; // suma samotne hodnoty DPH
	protected $sumNotVat = 0; // suma pouze polozek nepodlehajici DPH
	
	/**
	 * 
	 * @param type $category
	 * @param string|array $name jmeno polozky na fakture nebo v pravem sloupci. Muze byt pole s odlisnimy nazvy pro ruzne vypisy
	 * @param type $price cena zapocitana do celkove sumy, tedy bud cena s DPH a nebo pokud se DPH neuctuje tak ta cena
	 * @param type $priceWithoutVat cena bez DPH pokud se u polozky uctuje
	 * @param type $vat DPH
	 * @param type $info popis do icka
	 * @param type $priceComment komentar k cene
	 */
	public function add($category, $name, $price, $priceWithoutVat, $vat = 0, $info = "", $priceComment = "")
	{
		$item = new ChargeItem($name, $price, $priceWithoutVat, $vat, $info, $priceComment);
		$this->items[$category][] = $item;
		
		$this->totalSum += $price;
		if ($vat) {
			$this->sumVat += ($price - $priceWithoutVat);
			$this->sumWithoutVat += $priceWithoutVat;
		} else {
			$this->sumNotVat += $price;
		}
	}
	
	public function getByCategory($category)
	{
		if (!isset($this->items[$category])) {
			return array();
		}
		return $this->items[$category];
	}
	
	public function getAll()
	{
		$result = array();
		foreach ($this->items as $item) {
			$result = array_merge($result, $item);
		}
		return $result;
	}
	
	public function getTotalSum()
	{
		return $this->totalSum;
	}
	
	public function getSumWithoutVat()
	{
		return $this->sumWithoutVat;
	}
	
	public function getSumNotVat()
	{
		return $this->sumNotVat;
	}
	
	public function getSumVat()
	{
		return $this->sumVat;
	}
}

class ChargeItem
{
	protected $name;
	public $price;
	public $priceWithoutVat;
	public $vat;
	public $info;
	public $priceComment;
	
	public function __construct($name, $price, $priceWithoutVat, $vat = 0, $info = "", $priceComment = "")
	{
		$this->name = $name;
		$this->price = $price;
		$this->priceWithoutVat = $priceWithoutVat;
		$this->vat = $vat;
		$this->info = $info;
		$this->priceComment = $priceComment;
	}
	
	public function hasVat()
	{
		return (bool) $this->vat;
	}
	
	public function getName($for = NULL)
	{
		if (!$for) {
			return $this->name;
		} else {
			if (!is_array($this->name)) {
				return $this->name;
			} else {
				return $this->name[$for];
			}
		}
	}
}