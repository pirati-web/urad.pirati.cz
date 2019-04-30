<?php

namespace App\Model;

use Nette;


class Company //extends Nette\Object
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
		return $this->database->table('company');
	}


	/** @return Nette\Database\Table\ActiveRow */
	public function findById($id)
	{
		return $this->findAll()->get($id);
	}
        
	public function findByIdVerify($id, $userId)
	{
		return $this->findAll()->where("id=? AND user_id=?",$id, $userId)->fetch();
	}
        
	public function findByUser($userId)
	{
		return $this->findAll()->where("user_id=?",$userId);
	}
        

	/** @return Nette\Database\Table\ActiveRow */
	public function insert($values)
	{
		return $this->findAll()->insert($values);
	}


	public function delete($id)
	{	
		$this->database->beginTransaction();
		try {
			// tables needs sophisticated deletion
			$complicated_tables = array('company_person','company_seat', 'company_docs');
			foreach ($simple_tables as $table_name) {
				$this->deleteComplicatedRowInTable($table_name, "company_id =?", $id);
			}
			// tables requiring simple delete
			$simple_tables = array("company_accountancy_service", "company_actions", "company_docs",
								"company_fields", "company_phone_service", "company_post_service",
									"company_progress", "company_seat_service", "company_tax");
			foreach ($simple_tables as $table_name) {
				$this->deleteRowInTable($table_name, "company_id =?", $id);
			}
			$company_in_db = $this->findById($id);
			// delete user, safe because every company has only one user
			$this->database->table("users")->where("id = ?", $company_in_db->user_id)->fetch()->delete();
			// if all this is ok, then delete company record			
			$company_in_db->delete();
			$this->database->commit();
		} catch (PDOException $ex) {
			$this->database->rollback();
		} catch (Exception $e) {
			$this->database->rollback();
		}
	}

	private function deleteRowInTable($name, $query_string, $query_params) {
		$this->database->table($name)->where($query_string, $query_params)->delete();
	}

	private function deleteComplicatedRowInTable($name, $query_string, $query_params) {
		$results = $this->database->table($name)->where($query_string, $query_params);
		foreach( $results as $result ){
			$result->delete();
		}
	}
	

}
