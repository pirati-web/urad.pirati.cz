<?php

namespace App\Model;

use Nette;


class Acting //extends Nette\Object
{
	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/** @return Nette\Database\Table\Selection */
	public function findAll()
	{
		return $this->database->table('acting')->order("order ASC");
	}


	/** @return Nette\Database\Table\ActiveRow */
	public function findById($id)
	{
		return $this->findAll()->get($id);
	}


	/** @return Nette\Database\Table\ActiveRow */
	public function insert($values)
	{
		return $this->database->table("company_actions")->insert($values);
	}
        
        /** @return Nette\Database\Table\ActiveRow */
	public function getView($companyId)
	{
		return $this->database->table("ca_view")->where("company_id=?",$companyId);
	}


}
