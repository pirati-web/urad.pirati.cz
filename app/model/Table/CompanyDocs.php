<?php

namespace App\Model;

use Nette;


class CompanyDocs //extends Nette\Object
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
		return $this->database->table('company_docs');
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
        
    /** @return Nette\Database\Table\Selection */
	public function findByCompany($companyId)
	{
		return $this->findAll()->where("company_id=?",$companyId);
	}

	/** @return Nette\Database\Table\Selection */
	public function getDocumentTypes(){
		return $this->database->table('docstype');
	}


}
