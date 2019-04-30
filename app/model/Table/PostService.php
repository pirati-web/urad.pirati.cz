<?php

namespace App\Model;

/**
 * PostService
 */
class PostService extends Table
{
	protected $tableName = "post_service";
	
	public function addToCompany($data)
	{
		$this->db->table("company_post_service")->insert($data);
	}
}
