<?php

namespace App\Model;

use Nette;


class Invoice // extends Nette\Object
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
		return $this->database->table('invoice');
	}


	/** @return Nette\Database\Table\ActiveRow */
	public function findById($id)
	{
		return $this->findAll()->get($id);
	}
        
        public function getInvoiceNumber($id){
                return $this->findAll()->where("docs_id=?",$id);
        }

	/** @return Nette\Database\Table\ActiveRow */
	public function insert($values)
	{
		return $this->findAll()->insert($values);
	}

}
