<?php

namespace App\Model;

use Nette;


class Taxes //extends Nette\Object
{

	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $database;

	const VAT = 2;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/** @return Nette\Database\Table\Selection */
	public function findAll()
	{
		return $this->database->table('taxes')->order("order ASC");
	}


	/** @return Nette\Database\Table\ActiveRow */
	public function findById($id)
	{
		return $this->findAll()->get($id);
	}

        public function findByCompany($id){
            return $this->database->table('company_tax')->where("company_id=?",$id);
        }
        

	/** @return Nette\Database\Table\ActiveRow */
	public function insert($values)
	{
		return $this->findAll()->insert($values);
	}
        
	public function insertCompanyTaxes($values)
	{
			return $this->database->table('company_tax')->insert($values);
	}

}
