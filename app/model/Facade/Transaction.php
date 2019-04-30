<?php

namespace App;

/**
 * Transaction
 *
 */
class Transaction //extends \Nette\Object
{

	use \Nette\SmartObject;

	/** @var \Nette\Database\Context */
	protected $connection;
	
	public function __construct(\Nette\Database\Context $connection)
	{
		$this->connection = $connection;
	}
	
	public function beginTransaction()
	{
		$this->connection->beginTransaction();
	}
	
	public function commit()
	{
		$this->connection->commit();
	}
	
	public function rollBack()
	{
		$this->connection->rollBack();
	}
}
