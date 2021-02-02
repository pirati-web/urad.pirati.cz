<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator//extends Nette\Object 
{

	use Nette\SmartObject;

	const
		TABLE_NAME = 'users',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_MAIL = 'mail',
		COLUMN_REAL_NAME = 'name',
		COLUMN_REAL_SURNAME = 'surname',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role';


	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}
        
        public function getUserName($id){
            $row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->fetch();
            return $row['username'];
        }


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('Nesprávné uživetlské jméno.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('Zadali jste špatné heslo.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update(array(
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);
		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
                //return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], array("name"=>$row['name']));
	}


	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function add($values, $password)
	{
		return $this->database->table(self::TABLE_NAME)->insert(array(
			self::COLUMN_NAME => $values['mail'],
			self::COLUMN_MAIL => $values['mail'],
			self::COLUMN_REAL_NAME => $values['name'],
			self::COLUMN_REAL_SURNAME => $values['surname'],
			'factname' => $values['mail'],
			'checkFlag' => '',
			self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
		));
	}

	/**
	 * Updates password for user.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function change_password($username, $password)
	{
		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('Nesprávné uživetlské jméno.', self::IDENTITY_NOT_FOUND);
		} else {
			return $this->database->table(self::TABLE_NAME)->update(array(
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
				))->where(self::COLUMN_NAME."=". $username);
		}
	}

}
