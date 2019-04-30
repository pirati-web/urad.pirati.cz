<?php

namespace App\Model;

use Nette;


class Payments // extends Nette\Object
{

	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $database;

	const NOTARY_FEE = 4;
	const TRADE_LICENCE_FEE = 5;
	const COURT_FEE = 1;
	const ADMINISTRATIVE_FEE = 6;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/** @return Nette\Database\Table\Selection */
	public function findAll()
	{
		return $this->database->table('payments');
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

}
