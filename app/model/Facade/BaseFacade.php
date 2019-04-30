<?php

namespace App\Model\Facade;

use Nette;


class BaseFacade //extends Nette\Object
{

	use Nette\SmartObject;

	/** @var \App\Transaction */
	protected $transaction;

	public function __construct(\App\Transaction $transaction)
	{
		$this->transaction = $transaction;
	}
}
