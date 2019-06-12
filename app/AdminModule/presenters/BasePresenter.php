<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form;

/**
 * Base presenter for all application presenters.
 */
abstract class BaseAdminPresenter extends Nette\Application\UI\Presenter
{
	protected $salt = "asdjjlk76hjk89HJKH";
	protected $company;
	
	/** @var Model\JusticeLimit @inject */
	public $justiceLimitTable;
	
	/** @var Model\Company @inject */
	public $companyTable;
	
	protected $disableApp = FALSE;

	protected function startup()
	{
		parent::startup();
		$this->template->allowGa = $this->context->parameters["ga"];
	}
	
	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->disableApp = $this->disableApp;
	}

	public function calculateHash($password, $salt, $param = null)
	{
		if ($param == "md5") {
			return md5($password . str_repeat($salt, 10));
		} else {
			return hash('sha512', $password . str_repeat($salt, 10));
		}
	}

	protected function createComponentSignInForm()
	{
		$form = new Form;
		$form->addText('username', 'Username:')
				->setRequired('Please enter your username.');

		$form->addPassword('password', 'Password:')
				->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = [$this, "signInFormSucceeded"];
		return $form;
	}

	protected function createComponentNewUserForm()
	{
		$form = new Form;
		$form->addText('mail', 'Emailová adresa:')
				->addRule(Form::FILLED, 'Zadejte email.')
				->addRule(Form::EMAIL, 'Zadejte platný email.')
				->addRule(callback($this, 'handleCheckUser'), 'toto uživatelské jméno je již regostrováno');
		$form->addText('name', 'Jméno')
				->setRequired('Prosím zadejte jméno.');
		$form->addPassword('password', 'Heslo:')
				->setRequired('Zadejte heslo.');
		$form->addPassword('password2', 'Heslo:')
				->setRequired('Vyplňtě ještě jednou heslo pro kontrolu.')
				->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);
		$form->addTextArea('cond', 'Podmínky')
				->setDisabled(True)
				->setDefaultValue("Podmínky .")
				->addRule(Form::FILLED, 'Musíte souhlasit s podmínkami');
		$form->addSubmit('send', 'Uložit')
				->onClick[] = [$this, "newUserFormSucceeded"];
		$form->addSubmit('back', 'Předchozí krok')
						->setValidationScope(FALSE)
				->onClick[] = [$this, "formCancelled"];
		// call method signInFormSucceeded() on success
		$form->onSuccess[] = [$this, "newUserFormSucceeded"];

		$form->addProtection();
		return $form;
	}

	public function proccessException(\Exception $e)
	{
		if ($this->context->parameters["productionMode"]) {
			\Tracy\Debugger::log($e, \Tracy\Debugger::ERROR);
		} else {
			\Tracy\Debugger::getBlueScreen()->render($e);
			exit;
		}
	}

}

class JusticeLimitException extends \Exception {}
