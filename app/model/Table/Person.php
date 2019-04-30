<?php

namespace App\Model;

use Nette;


class Person //extends Nette\Object
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
		return $this->database->table('person');
	}


	/** @return Nette\Database\Table\ActiveRow */
	public function findById($id)
	{
		return $this->findAll()->get($id);
	}


	/** @return Nette\Database\Table\ActiveRow */
	public function insert($values)
	{
		return $this->findAll()->insert($values);
	}
        
        /** @return Nette\Database\Table\ActiveRow */
	public function fakturyInsert($values)
	{
		return $this->database->table("fact_person")->insert($values);
	}
        
        public function findAllFaktury()
        {
            	return $this->database->table('fact_person');
        }

}
