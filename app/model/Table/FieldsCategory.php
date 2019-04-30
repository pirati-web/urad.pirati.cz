<?php

namespace App\Model;

use Nette;


class FieldsCategory //extends Nette\Object
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
		//return $this->database->table('fieldsCategories');
            return $this->database->table('fg_view');
	}

        /** @return Nette\Database\Table\Selection */
        public function getTypeView(){
            return $this->database->table("ft_view");
        }
        
        /** @return Nette\Database\Table\Selection */
        public function getGroups() {
            return $this->database->table('groups');
        }
        
        public function getTypes() {
            return $this->database->table("types")->order("order ASC");
        }
		
		public function getFiledsTypes()
		{
			return $this->database->table("fieldstypes");
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

}
