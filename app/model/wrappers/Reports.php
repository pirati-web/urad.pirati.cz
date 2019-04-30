<?php

namespace App\Model;

use Nette;


class Reports 
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
		return $this->database->table('company_progress');
	}

	/** @return Nette\Database\Table\ActiveRow */
	public function last_month()
	{
		return $this->database->query('select * from company_progress where progress_id >= 8 and last_change BETWEEN (DATE(NOW()) - INTERVAL 1 MONTH) AND DATE(NOW())')->fetch();
	}

	/** @return Nette\Database\Table\ActiveRow */
	public function last_month_with_types()
	{
		return $this->database->query('
		select count(*) as cnt, type from company_progress 
		join company on 
		company_progress.company_id = company.id
		where progress_id >= 8 and 
		last_change BETWEEN (DATE(NOW()) - INTERVAL 1 MONTH) AND DATE(NOW())
		group by type');
	}

	/** @return Nette\Database\Table\ActiveRow */
	public function last_30_days()
	{
		return $this->database->query('select count(*) as cnt from company_progress where progress_id = 9 AND DATEDIFF(NOW(), last_change) < 30')->fetch();
	}

	/** @return Nette\Database\Table\ActiveRow */
	public function all_time_stats()
	{
		return $this->database->query('select count(*) as cnt from company_progress where progress_id = 9')->fetch();
	}

	/** @return Nette\Database\Table\ActiveRow */
	public function average_time()
	{
		return $this->database->query('select AVG(DATEDIFF(last_change, created)) as avg_time from company_progress where progress_id = 9')->fetch();
	}

}
