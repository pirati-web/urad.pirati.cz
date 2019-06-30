<?php

namespace App\AdminModule\Presenters;

use App\Model,
	Nette,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer,
    Nette\Database\Context,
    Nette\Application\Responses,
	Nette\Application\UI\Form;

use Joseki\Application\Responses\PdfResponse;
/**
 * Homepage presenter.
 */
class AdminPresenter extends SecuredPresenter
{

        private $id;
        private $company_id;
        
        /** @var Nette\Database\Context */
	    private $database;
        /** @var Model\News */
        private $news;
        /** @var Model\Companies */
        private $companies;
        /** @var Model\Person */
        private $person;
        /** @var Model\Seat */
        private $seat;
        /** @var Model\Fields */
        private $fields;
        /** @var Model\CompanySeat */
        private $companySeat;
        /** @var Model\CompanyPerson */
        private $companyPerson;
        /** @var Model\CompanyProgress */
        private $companyProgress;
        /** @var Model\CompanyDocs */
        private $companyDocs;
        /** @var Model\FieldsCompany */
        private $fieldsCompany;        
        /** @var Model\FieldsCategory */
        private $fieldsCategory;
        /** @var Model\Acting */
        private $acting;
        /** @var Model\Banks */
        private $banks;
        /** @var Model\Payments */
        private $payments;
        /** @var Model\Progress */
        private $progress;
        /** @var Model\Reports */
        private $reports;
        /** @var Model\Users */        
        private $users;
        /** @var Model\Taxes */
        private $taxes;        
        /** @var Model\Invoice */
        private $invoice;
        
        /** @var DocsStorage */
        private $docsStorage;
        
        protected $company;
		
		/** @var Model\Helper\PaymentHelper @inject */
        public $paymentHelper;
        
        /** @var \Nette\Mail\IMailer @inject */
        public $mailer;
        
        private $docsEnum = array("spol_smlouva"=>1,"souhlas_sidlo"=>2,"faktura"=>3,
                                  "plna_moc_jednatel"=>4,"plna_moc_spolecnik"=>5,
                                  "prohlaseni_odpovednosti"=>6,"spravce_vkladu"=>7,
                                  "checklist"=>8,"prohlaseni_jednatele"=>9);

        public function __construct(Nette\Database\Context $database,
                                    Model\News $news, Model\Users $users, Model\Company $companies,
                                    Model\Seat $seat, Model\CompanySeat $companySeat, Model\Progress $progress, 
                                    Model\Person $person, Model\CompanyPerson $companyPerson, Model\CompanyProgress $companyProgress,
                                    Model\Acting $acting, Model\Banks $banks, Model\FieldsCompany $fieldsCompany, Model\Payments $payments, Model\Taxes $taxes, Model\CompanyDocs $companyDocs,
                                    Model\Fields $fields, Model\FieldsCategory $fieldsCategory, Model\Invoice $invoice, Model\Reports $reports)
	{
        $this->database = $database;
        $this->news = $news;
        $this->fields = $fields;
        $this->users = $users;
        $this->companies = $companies;
        $this->seat = $seat;
        $this->companySeat = $companySeat;
        $this->person = $person;
        $this->companyPerson = $companyPerson;
        $this->companyProgress = $companyProgress;
        $this->progress = $progress;
        $this->acting = $acting;
        $this->banks = $banks;
        $this->fieldsCompany = $fieldsCompany;
        $this->fieldsCategory = $fieldsCategory;
        $this->payments = $payments;
        $this->taxes = $taxes;
        $this->companyDocs = $companyDocs;   
        $this->invoice = $invoice;        
        $this->reports = $reports;
	}
               
        
        public function injectDocs(\DocsStorage $storage)
        {
            $this->docsStorage = $storage;
        }
        

        public function handleGetDoc($doc_filename, $doc_name){
            $suffix = (strpos($doc_name, "_edit") == (strlen($doc_name)-strlen("_edit"))) ? ".docx" : ".pdf";
            $document = $this->docsStorage->get_path($doc_filename.$suffix);
            $doc_name .= $suffix;     
            $this->sendResponse(new \Nette\Application\Responses\FileResponse($document, $doc_name));
        }
        
        public function getCompanyFromDB($id=NULL){
            if ($id){                
                if ($this->template->superadmin){
                    $company = $this->companies->findById($id);
                }else{
                    $company = $this->companies->findByIdVerify($id,$this->user->getId());
                }
            }else{
                $company = $this->companies->findByUser($this->user->getId())->order("dateCreated DESC")->fetch();                
            }
            if (!$company){
                $this->company = NULL;
                return;
            }
            $this->company = new \Company($company->name,$this->fieldsCategory);
            $this->company->setId($company->id);
            $this->company->setCapital($company->value);
            $banka = $this->banks->findById($company->bank);
            $this->company->setBank(array("name"=>$banka->name,"id"=>$banka->id));
            
            $seatId = $this->companySeat->findByCompany($company->id);
            $seat =  new \Place($this->seat->findById($seatId->seat_id));
            $this->company->setSeat($seat);
            $spolecniciDB = $this->companyPerson->findViewCompany($company->id)->where("acting=0");
            $spolecnici = array();
            $shares =  array();
            foreach($spolecniciDB as $s){
                $shares[] = $s->share;
                switch ($s->choice){
                    case 1:
                        $sSeat = new \Place($this->seat->findById($s->place));
                        $sp = new \ExistingCompany($s->name,$s->choice,$s->ico, $sSeat);
                        $sp->setDbId($s->id);                        
                        $spolecnici[] = $sp;
                        break;
                    case 2:
                        $place = $this->seat->findById($s->place);
                        $sSeat = new \Place($place->street,$place->street_number,"",
                                $place->city,$place->zip_code,$place->state,"");
                        $sp = new \Person($s->choice,$s->name,$s->surname,$s->person_id,$sSeat,"",
                                $s->title_pre,$s->title_suf);
                        $sp->setDbId($s->id);
                        $spolecnici[] = $sp;
                        break;
                    case 3:
                        $sSeat = new \Place($this->seat->findById($s->place));
                        $sp = new \ForeignSubject($s->name,$s->choice,$s->text,$sSeat);
                        $sp->setDbId($s->id);
                        $spolecnici[] = $sp;
                        break;
                }
            }
            $this->company->setShares($shares);
            $this->company->setPersons($spolecnici);
            $jednateleDB = $this->companyPerson->findViewCompany($company->id)->where("acting=1");
            $jednatele = array();
            foreach($jednateleDB as $s){
                switch ($s->choice){
                    case 1:
                        $sSeat = new \Place($this->seat->findById($s->place));
                        $j = new \ExistingCompany($s->name,$s->choice, $s->ico, $sSeat);
                        $j->setDbId($s->id);
                        $jednatele[] = $j;
                        break;
                    case 2:
                        $place = $this->seat->findById($s->place);
                        $sSeat = new \Place($place->street,$place->street_number,"",
                                $place->city,$place->zip_code,$place->state,$place->owner_name);
                         $j = new \Person($s->choice,$s->name,$s->surname,$s->person_id,$sSeat,"",
                                $s->title_pre,$s->title_suf);
                         $j->setDbId($s->id);
                         $jednatele[] = $j;
                        break;
                    case 3:
                        $sSeat = new \Place($this->seat->findById($s->place));
                        $j = new \ForeignSubject($s->name,$s->choice, $s->text,$sSeat);
                        $j->setDbId($s->id);
                        $jednatele[] = $j;
                        break;
                }
            }
            
            $userID = $this->companyPerson->findViewCompany($company->id)->where("acting=2");
            $user = null;
            foreach($userID as $s){
                switch ($s->choice){
                    case 2:
                        $place = $this->seat->findById($s->place);
                        $sSeat = new \Place($place->street,$place->street_number,"",
                                $place->city,$place->zip_code,$place->state,"");
                        $user = new \Person($s->choice,$s->name,$s->surname,$s->person_id,$sSeat,"",
                                $s->title_pre,$s->title_suf);
                        $user->setDbId($s->id);
                        break;
                }
                break;
            }    
            $this->company->setUser($user);
            
            $taxes = array();
            $t = $this->taxes->findByCompany($company->id);
            foreach ($t as $tax){
                $taxes[$tax->tax_id] = $this->taxes->findById($tax->tax_id)->name;
            }
            $this->company->setTaxes($taxes);
            
            $this->company->setActingPersons($jednatele);
            $acting = $this->acting->getView($company->id)->fetch();
            $acting_name = $this->acting->findById($acting->acting_id);
            $this->company->setActions(array("acting_id"=>$acting->acting_id,
                                             "name"=>$acting_name->desc,
                                             "desc"=>$acting->desc));
            
            $fields = $this->fieldsCompany->findByCompanyId($company->id);
            $areas = array();
            foreach($fields as $f){
                $fDB = $this->fields->findById($f->fields_id);
                $areas[$fDB->id] = $fDB->name;
            }
            $this->company->setAreas($areas);            
        }
        
    public function beforeRender() {
        parent::beforeRender();
        if ($this->getUser()->getRoles()[0]==1){
            $this->template->companies = $this->companies->findAll();   
            $this->template->superadmin = TRUE;
        }else{
            $this->getCompanyFromDB();                
            $this->template->companies = $this->companies->findByUser($this->user->getId())->order("dateCreated DESC");
            $this->template->superadmin = FALSE;
        }            
        $this->template->news = $this->news->getView();
        if (method_exists($this->getUser()->getIdentity(), "get")){
            $db_user = $this->users->findByName($this->getUser()->getIdentity()->get("email"));
            $this->getUser()->getIdentity()->set("register",$db_user->register);
        }
    }    
    
	public function renderDefault()
	{
        $this->template->id = NULL;
        $this->template->leadingCompany = $this->companies->findAll()->order("rand()")->limit(1)->fetch();
        if ( $this->template->leadingCompany ) {
            $seatId = $this->companySeat->findByCompany($this->template->leadingCompany->id);
            $this->template->seat =  $this->seat->findById($seatId->seat_id);
        }
    }


    public function renderReports()
	{
        $this->template->last_month = $this->reports->last_month();        
        $this->template->last_month_list = $this->reports->last_month_list();     
        $this->template->overall = $this->reports->all_time_stats();        
        $this->template->averagetime = $this->reports->average_time();
        $this->template->lmwt = $this->reports->last_month_with_types();
    }

    protected function createComponentForm($name)
	{
		$form = new Form;
		$form->addText("company", "")
				->addRule(Form::FILLED, "Musíte vyplnit název nové firmy");		
		$form->addSubmit("search", "hledat")
				->onClick[] = [$this, "handleFindCompany"];
		$form->addProtection();
		return $form;
	}

    public function handleFindCompany($button)
	{
        $values = $button->getForm()->getValues();
        $company_name = $values["company"];
		if (gettype($company_name) == 'string') {
            $company = $this->database->query('select * from company where name LIKE ?', "%".$company_name."%");			
            if ($company) {
                $this->template->companyName = $company;
            } else {
                $this->template->message = "Takovou firmu bohužel neznáme.";	    	
            }            
			if ($this->presenter->isAjax()) {                                
                $this->redrawControl('findCompanyName');
			}
        } 
	}
    
    protected function createComponentNextStepForm()
	{
        $companyId = ($this->company) ? $this->company->getId() : "";
        $form = new Form();
        $form->addHidden("cId", $companyId);
        $form->addHidden("pId", '')
              ->setRequired("Musite zaskrtnout novy stav")
              ->addRule(Form::PATTERN, 'Novy krok nebyl nastaven', '[0-9]');
        $form->addSubmit('confirm', 'Potvrdit kroky')
             ->onClick[] = [$this, "nextStep"];
        $form->addProtection();
        return $form;
    }

    public function nextStep($button){
        $values = $button->getForm()->getValues();
        $company =($this->company) ? $this->company : $this->companies->findById($values["cId"]);
        $company_progress = $this->companyProgress->findByCompany($company->id)->limit(1)->fetch(1);
        while ($company_progress->progress_id < $values["pId"]) {
            $this->companyProgress->increment($values["cId"]);
            $company_progress = $this->companyProgress->findByCompany($company->id)->limit(1)->fetch(1);
            $this->sendProgressEmail($values["cId"], $company_progress);
        }
        $this->redirect('show', $values["cId"]);
    }

    private function sendProgressEmail($company_id, $company_progress){
        
        $progress_description = $this->progress->findById($company_progress->progress_id);
        $company = ($this->company) ? $this->company : $this->companies->findById($company_id);     
        // find the user belonging to the company
        $user_id = $this->companies->findById($company_id)->user_id;
        $user = $this->users->findById($user_id);

        $template = $this->createTemplate()->setFile($this->context->parameters['appDir'].'/AdminModule/templates/Email/progress.latte');
        $template->nadpis = $progress_description->email_header;
        $template->text = $progress_description->email_body;
        $template->company_name = $company->name;  

        $mail = new Message;
        $mail->setFrom('1. Pirátská <urad@pirati.cz>')
                ->addTo($user->mail)
                ->setSubject($company->name .' '. $progress_description->email_header)
                ->setHTMLBody($template, FALSE);
        $this->mailer->send($mail);
    }
        
    public function formCancelled()
	{
		$this->redirect('default');
    }
    

    public function renderOrders($filter = NULL){
        switch ($filter) {
            case "done":
                $this->template->companies = $this->companyProgress->findAllView()->where("progress_id = 9")->order("progress_id DESC, last_change DESC");
                break;
            case "on_hold":
                $this->template->companies = $this->companyProgress->findAllView()->where("progress_id < 9 AND DATEDIFF(NOW(), last_change) > 21")->order("progress_id DESC, last_change DESC");
                break;
            default:
                $this->template->companies = $this->companyProgress->findAllView()->where("progress_id < 9")->order("progress_id DESC, last_change DESC");
        }        
        $this->template->progress_names = $this->progress->findAll();
    }


    public function actionDelete($id){        
        $this->companies->delete($id);
        $this->redirect('orders');
    }
        
        
    public function renderShow($id){
        $userId = $this->user->getId();
        $this->beforeRender();
        $this->getCompanyFromDB($id);
        $this->template->company = $this->company;
        $this->template->docs = $this->companyDocs->findByCompany($id);
        $doctypes = [];
        foreach ($this->companyDocs->getDocumentTypes() as $d){
            $doctypes[$d->id] = $d->name;
        }
        $this->template->docstype = $doctypes;
        $this->template->specialDocs = false;
        $this->template->progress = $this->companyProgress->findByCompany($id)->limit(1)->fetch();
        $this->template->progress_names = $this->progress->findAll();
    }
        
        
    public function renderShowUser($id){
        $userId = $this->user->getId();
        $this->beforeRender();          
        if ($this->template->superadmin){
            $s = $this->person->findById($id);
            switch ($s->choice){
                case 1:
                    $sSeat = new \Place($this->seat->findById($s->place));
                    $user = new \ExistingCompany($s->name,$s->choice, $s->ico, $sSeat);
                    break;
                case 2:
                    $place = $this->seat->findById($s->place);
                    $sSeat = new \Place($place->street,$place->street_number,"",
                            $place->city,$place->zip_code,$place->state,$place->owner_name);
                    $user = new \Person($s->choice,$s->name,$s->surname,$s->person_id,$sSeat,"",
                            $s->title_pre,$s->title_suf);
                    $user->setDate($s->date);
                    $user->setTel($s->tel);
                    break;
                case 3:
                    $sSeat = new \Place($this->seat->findById($s->place));
                    $user = new \ForeignSubject($s->name,$s->choice, $s->text,$sSeat);
                    break;
            }
            $this->template->companies = $this->companyPerson->findByUser($s->id);
            $this->template->userA = $user;
        }
    }        
        
}
