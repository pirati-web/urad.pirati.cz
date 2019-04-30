<?php

namespace App\Model;

/**
 * AccountancyService
 */
class AccountancyService extends Table
{
	protected $tableName = "accountancy_service";
	
	public function getServicesByCategory($categoryId)
	{
		return $this->findBy(array("accountancy_service_category_id" => $categoryId))->order("position");
	}
	
}
