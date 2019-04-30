<?php

namespace App\Model;

use Nette;


class CompanyPerson // extends Nette\Object
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
		return $this->database->table('company_person');
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
        
        public function findViewCompany($id)
	{
		return $this->database->table("cp_view")->where("company_id=?",$id);
	}
        

        public function findByUser($user){
                return $this->database->query("select company.id, company.name from company left join company_person on company.id = company_person.company_id where company_person.person_id = ?",$user)->fetchAll();
        }
}
