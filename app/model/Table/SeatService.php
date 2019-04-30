<?php

namespace App\Model;

/**
 * SeatService
 */
class SeatService extends Table
{
	protected $tableName = "seat_service";
	
	public function addToCompany($data)
	{
		$this->db->table("company_seat_service")->insert($data);
	}
}
