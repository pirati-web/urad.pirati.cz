<?php

namespace App\Model;

/**
 * PhoneService
 */
class PhoneService extends Table
{
	protected $tableName = "phone_service";
	
	public function addToCompany($data)
	{
		$this->db->table("company_phone_service")->insert($data);
	}
}
