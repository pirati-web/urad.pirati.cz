<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model,
	Nette\Mail\Message,
	Nette\Mail\SendmailMailer,
	Nette\Security\Passwords,
	Nette\Application\UI\Form;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BaseAdminPresenter
{

	/** @var Model\Users */
	private $users;

	/** @var Model\UserManager */
	private $um;
	private $id;

	/** @persistent */
	public $backlink = '';

	public function __construct(Model\Users $users, Model\UserManager $um)
	{
		$this->users = $users;
		$this->um = $um;
	}

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */

	public function signInFormSucceeded($form, $values = NULL)
	{
		if (!$values) {
			$values = $form->getValues();
		}
		if ($values->remember) {
			$this->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->getUser()->setExpiration('20 minutes', TRUE);
		}
		try {
			$user = $this->users->findAll()->where('username', $values->username)->fetch();
			if ($user['checkFlag'] != 'success') {
				throw new Nette\Security\AuthenticationException('Registrační proces není dokončen, prosím zkontrolujte si email.');
			}
			$this->getUser()->login($values->username, $values->password);
			$this->restoreRequest($this->backlink);
			$this->redirect('Admin:');
		}
		catch (Nette\Security\AuthenticationException $e) {
			$this->flashMessage($e->getMessage());
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout(TRUE);
		$this->flashMessage('Byli jste odhlášeni.');
		$this->redirect(':Homepage:');
	}

	public function beforeRender()
	{
		$this->template->actions = array();
	}

	public function renderIn()
	{
		$this->template->menuItems = array();
	}

	public function renderReset()
	{
		$this->template->_form = $this['resetPassword'];
		$this->template->menuItems = array();
		$this->template->message = "";
		$this->template->messType = "";
	}

	public function renderProfile()
	{
		$this->setLayout('layoutAdmin');
		$row = $this->users->findById($this->getUser()->getId());
		$form = $this->createComponentProfileForm();
		$this->template->prForm = $form;
		$form->setDefaults($row);
	}

	public function renderFinish($id)
	{
		$values = $id;
		$this->template->menuItems = array();
		$user = $this->users->findAll()->where('checkFlag', $values)->fetch();
		if ($user && count($user) > 0) {
			$this->template->u = $user['id'];
			$table_date = $user['checkTimestamp'];
			$table_date2 = $table_date->add(new \DateInterval('PT30M'));
			$this->template->table = $table_date;
			$this->template->now = date("Y-m-d H-i-s");
			$interval = $table_date->diff(new \DateTime());
			$interval = $interval->format("%a;%H;%R%I");
			$this->template->interval = $interval;
			list($intD, $intH, $intM) = explode(";", $interval);
			$this->template->diffH = intval($intH);
			$this->template->diffD = intval($intD);
			$this->template->diffM = intval($intM);
			if (substr($values, 0, 1) == "r") {	
				$this->template->reset = "";
				$this['changePassWord']->setDefaults(array('stamp' => $values));
			} else {
				if ($this->template->diffD == 0 && $this->template->diffH == 0 && $this->template->diffM <= 0) {
					$this->flashMessage('Děkujeme za vaší registraci. Nyní se již můžete přihlásit.', 'success');
					$this->users->findAll()->where('checkFlag', $values)->fetch()->update(array('checkFlag' => 'success'));
					//$this->restoreRequest($this->backlink);
					$this->redirect('Sign:in',array("backlink" => NULL));
				} else {
					$this->flashMessage('vypršel časový limit', 'error');
					//$this->restoreRequest($this->backlink);
					$this->redirect('Sign:in',array("backlink" => NULL));
				}
			}
		}
	}

	function convert_datetime($str)
	{
		list($date, $time) = explode(' ', $str);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
		return $timestamp;
	}

	public function renderNewUser()
	{
		$this->template->_form = $this['newUserForm'];
		$this->template->menuItems = array();
		$this->template->message = "";
		$this->template->messType = "";
	}

	protected function createComponentProfileForm()
	{
		$form = new Form($this, 'profileForm');
		$form->addText('name', 'Jméno')
				->setRequired('Prosím zadejte jméno.');
		$form->addPassword('password', 'Heslo:', 30)
				->setRequired('Je nutné zadat heslo.');
		$form->addPassword('password2', 'Heslo znovu:', 30)
				->setRequired('Je nutné zadat heslo.')
				->addRule(Form::EQUAL, "Hesla se musí shodovat !", $form["password"]);
		$form->addHidden('stamp', 'nic');
		$form->addSubmit('change', "Změnit heslo");
		$form->addSubmit('send', 'Uložit');
		$form->onSuccess[] = [$this, "changePasswordSubmitted"];
		return $form;
	}

	public function newUserFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$m = $this->users->findAll()->where('mail', $values['mail'])->fetch();
		$ma = array();
		$ma['checkTimestamp'] = date("Y-m-d H-i-s");
		$ma['checkFlag'] = 'n' . $this->calculateHash($m['username'] . time(), $this->salt, "md5");
		$this->users->findById($m->id)->update($ma);

		$template = new Nette\Templating\FileTemplate($this->context->parameters['appDir'] . '/templates/Email/email.latte');
		$template->registerFilter(new Nette\Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->nadpis = 'Dokončení  hesla';
		$template->text = 'Dobrý den,<br> zaslali jste mail pro reset hesla na serveru urad.pirati.cz. Pro dokončení procesu navštivte do 15 minut <a href="' . $this->link("//Sign:finish", array("id" => $ma['checkFlag'])) . '">tento odkaz</a>.'
				. "<br><br>Vaši Piráti";

		$mail = new Message;
		$mail->setFrom('1. Pirátská <urad@pirati.cz>')
				->addTo($values['mail'])
				->setSubject('Změna hesla')
				->setHTMLBody($template);
		$mailer = new SendmailMailer;
		$mailer->send($mail);

		$this->flashMessage('Pro dokončení registrace navštivte mail.', 'success');
		$this->redirect("Admin:");
	}

	public function profileFormSucceeded($form)
	{
		$values = $form->getValues();
		$this->users->findById($this->getUser()->getId())->update($values);
		$this->flashMessage('Vaše údaje byly změněny', 'success');
		$this->redirect('Admin:default');
	}

	public function formCancelled()
	{
		$this->proccessFacade->stepBack();
		$this->redirect('admin');
	}

	public function changePasswordSubmitted($form)
	{
		$values = $form->getValues();
		if ($this->getUser()->isLoggedIn()) {
			$user = $this->users->findAll()->get($this->getUser()->getId());
			$passwd = Passwords::hash($values['password']);
			$name = $values['username'];
			if ($name == $this->getUser()->getIdentity()->getData()["username"]){
				$this->users->findById($this->getUser()->getId())->update(array(
					'password' => $passwd));
				$this->flashMessage('Heslo změněno', 'success');
				$this->redirect('Admin:default');
			}
		} else {
			$user = $this->users->findAll()->where('checkFlag', $values['stamp'])->fetch();
			$passwd = Passwords::hash($values['password']);
			$this->users->findAll()->where('checkFlag', $values['stamp'])->fetch()->update(array(
				'password' => $passwd, 'checkFlag' => 'success'));
			$this->flashMessage('Heslo změněno', 'success');
			$this->redirect('Sign:in');
		}
	}

	public function handleCheckUserR($mail)
	{
		if (gettype($mail) == 'string') {
			$cislo = $this->users->findAll()->where('mail', $mail)->count("*");
			$uziv = "";
			$uT = "";
			if ($cislo > 0) {
				$uziv = "Mailová adresa existuje";
				$uT = "success";
			} else {
				if (!\Nette\Utils\Validators::isEmail($mail)) {
					$uziv = "Zadejte platnou mailovou adresu";
					$uT = "error";
				} else {
					$uziv = "Takovou bohužel mailovou adresu neznáme.";
					$uT = "error";
				}
			}
			if ($mail == "") {
				$uziv = "Zadejte mailovou adresu";
				$uT = "error";
			}
			$this->template->message2 = $uziv;
			$this->template->messType2 = $uT;
			if ($this->presenter->isAjax()) {
				$this->invalidateControl('checkU');
			} else {
				
			}
		} else {
			$mail = $mail->value;
			$cislo = $this->users->findAll()->where('mail', $mail)->count("*");
			if ($cislo > 0) {
				$uziv = "Mailová adresa existuje, je možné zaslat nové údaje.";
				$uT = "succes";
				return true;
			} else {
				$uziv = "Takovou mailovou adresu bohužel neznáme.";
				$uT = "error";
				return false;
			}
		}
	}

	public function handleCheckUser($mail)
	{
		if (gettype($mail) == 'string') {
			$cislo = $this->users->findAll()->where('mail', $mail)->count("*");
			$uziv = "";
			$uT = "";
			if ($cislo > 0) {
				$uziv = "Mailová adresa je již použita pro jiný účet";
				$uT = "error";
			} else {
				if (!\Nette\Utils\Validators::isEmail($mail)) {
					$uziv = "Zadejte platnou mailovou adresu";
					$uT = "error";
				} else {
					$uziv = "Mailovou adresu je možné použít";
					$uT = "success";
				}
			}
			if ($mail == "") {
				$uziv = "Zadejte mailovou adresu";
				$uT = "error";
			}
			$this->template->message2 = $uziv;
			$this->template->messType2 = $uT;
			if ($this->presenter->isAjax()) {
				$this->invalidateControl('checkU');
			}
		} else {
			$mail = $mail->value;
			$cislo = $this->users->findAll()->where('mail', $mail)->count("*");
			if ($cislo > 0) {
				$uziv = "Mailová adresa je již použita pro jiný účet";
				$uT = "error";
				return false;
			} else {
				$uziv = "Mailovou adresu je možné použít";
				$uT = "success";
				return true;
			}
		}
	}

	public function resetPasswordSucceeded($form)
	{
		$values = $form->getValues();
		$m = $this->users->findAll()->where('mail', $values['mail'])->fetch();
		$ma = array();
		$ma['checkTimestamp'] = date("Y-m-d H-i-s");
		$ma['checkFlag'] = 'r' . $this->calculateHash($m['username'] . time(), $this->salt, "md5");
		$this->users->findById($m->id)->update($ma);

		$template = new Nette\Templating\FileTemplate($this->context->parameters['appDir'] . '/templates/Email/email.latte');
		$template->registerFilter(new Nette\Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->nadpis = 'Resetování hesla';
		$template->text = 'Dobrý den,<br> zaslali jste mail pro reset hesla na serveru urad.pirati.cz. Pro dokončení procesu navštivte do 15 minut <a href="' . $this->link("//Sign:finish", array("id" => $ma['checkFlag'])) . '">tento odkaz</a>.'
				. "<br><br>Vaši Piráti";

		$mail = new Message;
		$mail->setFrom('1. Pirátská <urad@pirati.cz>')
				->addTo($values['mail'])
				->setSubject('Změna hesla')
				->setHTMLBody($template);
		$mailer = new SendmailMailer;
		$mailer->send($mail);

		$this->flashMessage('Poždavek na změnu hesla byl odeslán', 'success');
		$this->redirect('Sign:in');
	}

	protected function createComponentResetPassword()
	{
		$form = new Form;
		$form->addText('mail', 'Zadejte email:', 30)
				->setRequired('Je nutné zadat mail!')
				->addRule(Form::EMAIL, 'Je nutné zadat email.')
				->addRule(callback($this, 'handleCheckUserR'), 'toto uživatelské jméno nexistuje');
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = [$this, 'resetPasswordSucceeded'];
		return $form;
	}

	protected function createComponentChangePassWord()
	{
		$form = new Form;
		$form->addHidden("username", $this->getUser()->getIdentity()->getData()["username"]);
		$form->addPassword('password', 'Heslo:', 30)
				->setRequired('Je nutné zadat heslo.');
		$form->addPassword('password2', 'Heslo znovu:', 30)
				->setRequired('Je nutné zadat heslo.')
				->addRule(Form::EQUAL, "Hesla se musí shodovat !", $form["password"]);
		$form->addHidden('stamp', 'nic');
		$form->addSubmit('change', "Změnit heslo");
		$form->onSuccess[] = [$this, 'changePasswordSubmitted'];
		return $form;
	}

}
