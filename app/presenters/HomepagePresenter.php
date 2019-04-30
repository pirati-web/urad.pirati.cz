<?php

namespace App\Presenters;

use App\Model,
	Nette,
	Nette\Application\UI\Form,
	Nette\Utils\Strings,
	Nette\Mail\Message,
	Nette\Mail\SendmailMailer,
	Nette\Utils\Image;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	/** @var Model\Categories */
	private $categories;

	/** @var Model\Items */
	private $items;

	/** @var Model\Comments */
	private $comments;

	/** @var Model\Users */
	private $users;

	/** @var Model\Likes */
	private $likes;

	/** @var Model\LikesItems */
	private $likesItems;
	private $id;

	/** @persistent */
	public $backlink = '';

	public function __construct(Model\Users $users)
	{
		parent::__construct();
		$this->users = $users;
	}

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	public function renderDefault()
	{
		$this->setLayout('layout_microsite');
		$this->restoreRequest($this->backlink);
		$this->template->categories = "";
		$this->template->items = array();
		$section = $this->session->getSection('orderForm');
		$section->count++; // increment counter by one
		$this->template->userDetails = $this->session->getSection("userForm");
	}
	
	public function actionDemand()
	{
		if (!$this->session->getSection("demand")->demand) {
			$this->redirect("Homepage:");
		}
	}

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
			$this->getUser()->login($values->username, $values->password);
			$user = $this->users->findAll()->where('username', $values->username)->fetch();
			if ($user['checkFlag'] != 'success') {
				throw new Nette\Security\AuthenticationException('Registrační proces není dokončen, zkontrolujte si email.');
			}
			$this->redirect('Admin:');
		}
		catch (Nette\Security\AuthenticationException $e) {
			$this->flashMessage($e->getMessage());
		}
	}

	public function formDemand($form)
	{
		$values = $form->getForm()->getValues(TRUE);
		$template = $this->getTemplate()->setFile($this->context->parameters['appDir'] . '/templates/Email/email.latte');		
		$template->nadpis = 'Firma' . $this->getSession()->getSection("demand")->data["name"];
		$template->text = '<b>Email:</b> '.$this->getSession()->getSection("demand")->data["email"]
				. '<br><b>Text poptávky:</b><br>'.$values["text"];
		$mail = new Message;
		$mail->setFrom('1. Pirátská <urad@pirati.cz>')
				->addTo("urad@pirati.cz")
				->setSubject('Nová poptávka')
				->setHTMLBody($template);
		$mailer = new SendmailMailer;
		$mailer->send($mail);

		$this->flashMessage("Děkujeme za váš zájem o Compliance. Služba je zatím ve verzi beta.");
		$this->session->getSection("demand")->remove();
		$this->redirect("Homepage:");
	}
	
	public function formExisting($form)
	{
		$values = $form->getForm()->getValues(TRUE);
		$template = $this->getTemplate()->setFile($this->context->parameters['appDir'] . '/templates/Email/email.latte');		
		$template->nadpis = 'Vaše firma ' . $values["name"];
		$template->text = 'Dobrý den,<br><br>děkujeme za Váš zájem o Compliance - automatické sledování regulací Vaší firmy. Služba je momentálně ve vývoji. Neváhejte se nám ozvat s Vašimi právními problémy. Díky Vám budeme moci tuto jedinečnou službu přizpůsobit tak, aby Vám skutečně vyhovala.<br><br>S pozdravem,<br>Vaši Piráti';
		$mail = new Message;
		$mail->setFrom('1. Pirátská <urad@pirati.cz>')
				->addTo($values['email'])
				->addBcc('urad@pirati.cz')
				->setSubject('Vítá Vás 1. Pirátská')
				->setHTMLBody($template);
		$mailer = new SendmailMailer;
		$mailer->send($mail);

		$this->flashMessage("Děkujeme za váš zájem o Compliance. Služba je zatím ve verzi beta.");
		$this->session->getSection("demand")->demand = 1;
		$this->session->getSection("demand")->data = $values;
		$this->redirect("Homepage:demand");
	}
	
	protected function createComponentDemandForm()
	{
		$form = new Form;
		$form->addTextArea("text", "")
				->addRule(Form::FILLED, 'Popiště prosím Váš problém');
		$form->addSubmit('send', '')
				->onClick[] = [$this, "formDemand"];
		$form->addProtection();
		return $form;
	}

	protected function createComponentExistingForm()
	{
		$form = new Form;
		$form->addText("name", "")
				->addRule(Form::FILLED, 'Zadejte prosím jméno vaší společnosti.');
		$form->addText("email", "")
				->addRule(Form::FILLED, 'Zadejte prosím email.')
				->addRule(Form::EMAIL, 'Email není v platném formátu.');
		$form->addSubmit('register', '')
				->onClick[] = [$this, "formExisting"];
		$form->addProtection();
		return $form;
	}

	protected function createComponentNewCompanyForm()
	{
		$form = new Form;
		$form->addText("name", "");
		$form->addSubmit("search", "potvrdit")
						->setValidationScope(NULL)
				->onClick[] = [$this, "handleSearch"];
		$form->addProtection();
		return $form;
	}

	public function handleSearch($button)
	{
		$values = $button->getForm()->getValues();
		$section = $this->session->getSection("company");
		$section["name"] = $values->name;
		$this->redirect("NewCompany:default");
	}

	public function userFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$section = $this->session->getSection("userForm");
		foreach ($values as $key => $val) {
			$section[$key] = $val;
		}
		$this->redirect("Homepage:");
	}

	function dateValidator($item, $arg)
	{
		return $item->value % 11 == 0;
	}

	protected function createComponentUserForm()
	{
		$form = new Form;
		$form->addText('name', 'jméno')
				->setRequired('Prosíme zadejte jméno.');
		$form->addText('surname', 'příjmení')
				->setRequired('Prosíme zadejte jméno.');
		$form->addText('date', 'Datum narození')
				->addRule(callback($this, 'dateValidator'), "Zadejte platné datum");
		//->setRequired("Prosíme zadejte datum narození.");
		$form->addSubmit('search', 'hledat')
				//->setAttribute('class', 'default')
				->onClick[] = [$this, "userFormSucceeded"];

		$form->addSubmit('cancel', 'Cancel')
						->setValidationScope(NULL)
				->onClick[] = [$this, "formCancelled"];
		$form->addProtection();
		return $form;
	}

	public function formCancelled()
	{
		$this->redirect('default');
	}

}
