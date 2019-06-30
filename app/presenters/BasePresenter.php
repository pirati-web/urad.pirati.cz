<?php

namespace App\Presenters;

use Nette,
	App\Model,
	DOMDocument,
	Nette\Application\UI\Form;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
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

	public function parseRUIAN($address)
	{
		$url = "http://ags.cuzk.cz/arcgis/rest/services/RUIAN/Vyhledavaci_sluzba_nad_daty_RUIAN/MapServer/exts/GeocodeSOE/findAddressCandidates?SingleLine=";
		$url .=	urlencode($address);
		$url .= "&outSR=&maxLocations=&outFields=&searchExtent=&f=pjson";
		$html = file_get_contents($url);
		$parsed_json = json_decode($html);
		$candidates = array();
		if (array_key_exists("candidates", $parsed_json)) {
			foreach( $parsed_json["candidates"] as $candidate){
				$candidates[] = array("address" => $candidate["address"],
									  "score" => $candidate["score"]);
			}
		}
		$score = array_column($candidates, 'score');
		array_multisort($score, SORT_DESC, $candidates);
		return $candidates;		
	}

	public function parseJustice($name)
	{
		$companyName = "";
		$ico = "";
		$may_be_ico = str_replace('/\s+/', '', $name);
		if (preg_match('/\d{8}/m', $may_be_ico)) {
			$ico = $may_be_ico;
		} else {
			$companyName = $name;
		}
		$url = "https://or.justice.cz/ias/ui/rejstrik-\$firma?nazev=" . urlencode($companyName) . "&ico=" . urlencode($ico);
		$html = file_get_contents($url);
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$data = simplexml_import_dom($doc);
		$companies = [];
		foreach ($data->xpath('//table[@class="result-details"]') as $index => $com) {
			$rows = $com->xpath('.//tr');
			$firstRowColumns = $rows[0]->xpath('.//td');
			$subjectNameElement = $firstRowColumns[0];
			$icoElement = $firstRowColumns[1];
			$thirdRowColumns = $rows[2]->xpath('.//td');
			$seatElement = $thirdRowColumns[0];
			$companies[$index] = [
				'name' => trim(html_entity_decode(strip_tags($subjectNameElement->asXML()), ENT_QUOTES, 'utf-8')),
				'ico' => trim(html_entity_decode(strip_tags($icoElement->asXML()), ENT_QUOTES, 'utf-8')),
				'seat' => trim(html_entity_decode(strip_tags($seatElement->asXML()), ENT_QUOTES, 'utf-8')),
			];
			if ($index > 20) {
				break;
			}
		}

		return $companies;
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

		// call method signInFormSucceeded() on success
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
