<?php

namespace App\Model;

use Nette;
use Nette\SmartObject;

/**
 * Base
 */
abstract class Table // extends Nette\Object
{
	/** @var Nette\Database\Context */
	protected $db;

	public function __construct(Nette\Database\Context $db)
	{
		if (!isset($this->tableName)) {
			$class = get_called_class();
			throw new \InvalidArgumentException("Property \$tableName must be defined in $class.");
		}

		$this->db = $db;
	}
	
	public function _getDb()
	{
		return $this->db;
	}


	public function getTable()
	{
		return $this->db->table($this->tableName);
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->getTable();
	}



	/**
	 * @param  array
	 * @return \Nette\Database\Table\Selection
	 */
	public function findBy(array $by)
	{
		return $this->getTable()->where($by);
	}



	/**
	 * @param  array
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findOneBy(array $by)
	{
		return $this->findBy($by)->limit(1)->fetch();
	}



	/**
	 * @param  int
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function find($id)
	{
		return $this->getTable()->wherePrimary($id)->fetch();
	}


	public function insert(array $values)
	{
		return $this->getTable()->insert($values);
	}

}