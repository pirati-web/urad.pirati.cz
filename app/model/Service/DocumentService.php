<?php

namespace App\Model\Service;

use Nette;
use App\Model;
use Joseki\Application\Responses\PdfResponse;

/**
 * DocumentService
 */
class DocumentService 
{

	use Nette\SmartObject;

	/** @var Nette\Application\UI\ITemplateFactory */
	protected $templateFactory;

	/** @var \App\Model\CompanyDocs */
	protected $companyDocs;

	/** @var \App\Model\Payments */
	protected $payments;

	/** @var \App\Model\Person */
	protected $person;

	/** @var \App\Model\Seat */
	protected $seat;

	/** @var \App\Model\CompanyPerson */
	protected $companyPerson;

	/** @var \App\Model\Invoice */
	protected $invoice;
	
	/** @var \App\Model\Helper\PaymentHelper */
	protected $paymentHelper;
	
	protected $testMode = FALSE;
	
	public static $docsEnum = array("spol_smlouva" => 1, "souhlas_sidlo" => 2, "zalohova_faktura" => 3,
		"plna_moc_jednatel" => 4, "plna_moc_spolecnik" => 5,
		"prohlaseni_odpovednosti" => 6, "spravce_vkladu" => 7,
		"checklist" => 8, "prohlaseni_jednatele" => 9, "faktura" => 10,
		"spol_smlouva_notar" => 11, "vyzva" => 12, "spol");

	public function __construct(Nette\Application\UI\ITemplateFactory $templateFactory, Model\CompanyDocs $companyDocs, Model\Payments $payments, Model\Seat $seat, Model\Person $person, Model\CompanyPerson $companyPerson, Model\Invoice $invoice, Model\Helper\PaymentHelper $paymentHelper)
	{
		$this->templateFactory = $templateFactory;
		$this->companyDocs = $companyDocs;
		$this->payments = $payments;
		$this->seat = $seat;
		$this->person = $person;
		$this->companyPerson = $companyPerson;
		$this->invoice = $invoice;
		$this->paymentHelper = $paymentHelper;
	}

	public function setTestMode($value)
	{
		$this->testMode = $value;
	}
	
	protected function createTemplate()
	{
		return $this->templateFactory->createTemplate();
	}

	public function createCestneProhlaseniJednatele(\Company $company)
	{
		foreach ($company->getActingPersons() as $jednatel) {
			$template = $this->createTemplate();
			$template->setFile($this->getDocsDir()."/Cestne_prohlaseni_jednatele.latte");
			$template->company = $company;
			$template->jednatel = $jednatel;
			$template->isForeign = FALSE;
			if ($jednatel instanceof \ForeignSubject) {
				$template->isForeign = TRUE;
			}
			$pdf = new PdfResponse($template);
			// optional
			$name = Nette\Utils\Strings::webalize($company->getName());
			$jName = Nette\Utils\Strings::webalize($jednatel->getFullName());
			$title = $name . "_cestne_prohlaseni_jednatele_" . $jName;
			$pdf->documentTitle = $title;
			$pdf->pageFormat = "A4"; // wide format
			$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
			$pdfFilename = md5(date("Y-m-d H:i:s.U") . $jName . "prohlaseni");
			$pdf->save($this->getDataDir(), $pdfFilename);
			$values = array("company_id" => $company->getId(),
				"doc_type" => self::$docsEnum["prohlaseni_jednatele"],
				"filename" => $pdfFilename,
				"name" => $title);
			$this->companyDocs->insert($values);
		}
	}

	public function createVyzva(\Company $company, $invNumber)
	{
		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/vyzva.latte");
		$template->company = $company;
		$template->invNumber = $invNumber;
		$pdf = new PdfResponse($template);
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_vyzva";
		$pdf->documentTitle = $title;
		$pdf->pageFormat = "A4"; // wide format
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdfFilename = md5(date("Y-m-d H:i:s.U") . "call");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["vyzva"],
			"filename" => $pdfFilename,
			"name" => $title);
		return $this->companyDocs->insert($values);
	}


	public function createChecklist(\Company $company)
	{
		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/Pokyny.latte");
		$template->company = $company;
		$template->specialDocs = self::hasSpecialDocs($company);
		$template->fields = $company->getFieldsCategory()->getFiledsTypes()->where("fields_id IN (?)", array_keys($company->getAreas()))->fetchAssoc("fields_id");
		$pdf = new PdfResponse($template);
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_pokyny";
		$pdf->documentTitle = $title; 
		$pdf->pageFormat = "A4"; // wide format
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdfFilename = md5(date("Y-m-d H:i:s.U") . "checklist");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["checklist"],
			"filename" => $pdfFilename,
			"name" => $title);
		return $this->companyDocs->insert($values);
	}

	public function createZalohovaFaktura(\Company $company, \ChargeList $charges)
	{
		// store invoice
		$values = array("company_id" => $company->getId(),
			"created" => new \DateTime(),
			'amount' => $charges->getTotalSum());
		if ($this->testMode) {
			$invNumber = "#test#";
		} else {
			$invNumber = $this->invoice->insert($values);
		}

		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/zalohovaFaktura.latte");
		$template->charges = $charges;
		$this->setupInvoiceTemplate($company, $invNumber, $template);

		// Tip: In template to make a new page use <pagebreak>
		$pdf = new PdfResponse($template);
		// optional
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_zalohova_faktura";
		$pdf->documentTitle = $title;
		$pdf->pageFormat = "A4"; // wide format
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdf->setSaveMode(PdfResponse::INLINE);
		$pdfFilename = md5(date("Y-m-d H:i:s.U").$invNumber . "zalohova_faktura");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["zalohova_faktura"],
			"filename" => $pdfFilename,
			"name" => $title);
		$doc_id = $this->companyDocs->insert($values);
		if (!$this->testMode) {
			$this->invoice->findAll()->where("id=?", $invNumber)->fetch()->update(array("docs_id" => $doc_id));
		}
		return $invNumber;
	}
	
	public function createFaktura(\Company $company, $invNumber, \ChargeList $charges)
	{
		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/faktura.latte");
		$template->charges = $charges;

		$this->setupInvoiceTemplate($company, $invNumber, $template);

		// Tip: In template to make a new page use <pagebreak>
		$pdf = new PdfResponse($template);
		// optional
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_faktura";
		$pdf->documentTitle = $title;
		$pdf->pageFormat = "A4"; // wide format
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdf->setSaveMode(PdfResponse::INLINE);
		$pdfFilename = md5(date("Y-m-d H:i:s.U") . "faktura");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["faktura"],
			"filename" => $pdfFilename,
			"name" => $title);
		$doc_id = $this->companyDocs->insert($values);
		if (!$this->testMode) {
			$this->invoice->findAll()->where("id=?", $invNumber)->fetch()->update(array("invoice_docs_id" => $doc_id));
		}
		//exit;
	}

	public function createPlnaMocJednatele(\Company $company)
	{
		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/Plna_moc_jednatele.latte");
		$template->company = $company;
		foreach ($company->getActingPersons() as $jednatel) {
			// Tip: In template to make a new page use <pagebreak>
			$template->jednatel = $jednatel;
			$pdf = new PdfResponse($template);
			// optional
			$name = Nette\Utils\Strings::webalize($company->getName());
			$jName = Nette\Utils\Strings::webalize($template->jednatel->getFullName());
			$jID = Nette\Utils\Strings::webalize($template->jednatel->getId());
			$title = $name . "_plna_moc_jednatele_" . $jName;
			$pdf->documentTitle = $title;
			$pdf->pageFormat = "A4"; // wide format
			$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
			$pdf->setSaveMode(PdfResponse::INLINE);
			$pdfFilename = md5(date("Y-m-d H:i:s.U") . $jID .$jName . "jednatel");
			$pdf->save($this->getDataDir(), $pdfFilename);
			$values = array("company_id" => $company->getId(),
				"doc_type" => self::$docsEnum["plna_moc_jednatel"],
				"filename" => $pdfFilename,
				"name" => $title);
			$this->companyDocs->insert($values);
		}
	}

	public function createPlnaMocSpolecnika(\Company $company)
	{
		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/Plna_moc_spolecnika.latte");
		$template->company = $company;
		foreach ($company->getPersons() as $spolecnik) {
			$template->spolecnik = $spolecnik;
			$pdf = new PdfResponse($template);
			// optional
			$name = Nette\Utils\Strings::webalize($company->getName());
			$sName = Nette\Utils\Strings::webalize($spolecnik->getFullName());
			$sID = Nette\Utils\Strings::webalize($spolecnik->getId());
			$title = $name . "_plna_moc_spolecnika_" . $sName;
			$pdf->documentTitle = $title;
			$pdf->pageFormat = "A4"; // wide format
			$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
			$pdfFilename = md5(date("Y-m-d H:i:s.U") . $sID. $sName . "spolecnik");
			$pdf->save($this->getDataDir(), $pdfFilename);
			$values = array("company_id" => $company->getId(),
				"doc_type" => self::$docsEnum["plna_moc_spolecnik"],
				"filename" => $pdfFilename,
				"name" => $title);
			$this->companyDocs->insert($values);
		}
	}

	public function createProhlaseniSpravceVkladu(\Company $company)
	{
		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/Prohlaseni_spravce_vkladu.latte");
		$template->company = $company;
		// Tip: In template to make a new page use <pagebreak>
		$pdf = new PdfResponse($template);
		// optional
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_prohlaseni_spravce_vkladu";
		$pdf->documentTitle = $title;
		$pdf->pageFormat = "A4"; // wide format
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdf->setSaveMode(PdfResponse::INLINE);
		$pdfFilename = md5(date("Y-m-d H:i:s.U") . "spravce");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["spravce_vkladu"],
			"filename" => $pdfFilename,
			"name" => $title,
			"public" => 0);
		return $this->companyDocs->insert($values);
	}

	public function createSouhlasSUmistenimSidla(\Company $company)
	{
		$template = $this->createTemplate();
		$template->setFile($this->getDocsDir()."/Souhlas_s_umistenim_sidla.latte");
		$template->company = $company;
		// Tip: In template to make a new page use <pagebreak>
		$pdf = new PdfResponse($template);

		// optional
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_souhlas_s_umistenim_sidla";
		$pdf->documentTitle = $title; 
		$pdf->pageFormat = "A4"; // wide format
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdf->setSaveMode(PdfResponse::INLINE);
		$pdfFilename = md5(date("Y-m-d H:i:s.U") . "souhlas_sidla");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["souhlas_sidlo"],
			"filename" => $pdfFilename,
			"name" => $title);
		return $this->companyDocs->insert($values);
	}

	public function createSpolecenskaSmlouva(\Company $company)
	{
		$latte = new \Latte\Engine;
		$latte->addFilter('padline', function ($s, $len = 60, $padding=STR_PAD_RIGHT) {
			return $s;
		});

		if ($company->getType() == "full") {
			$template_file = $this->getDocsDir()."/spolecenska_smlouva_full.latte";
		} else {
			$template_file = $this->getDocsDir()."/spolecenska_smlouva_simple.latte";
		}
		$template_params = array('company' => $company);
		$template = $latte->renderToString($template_file, $template_params);
			
		$pdf = new PdfResponse($template);

		// optional
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_spolecenska_smlouva";
		$pdf->documentTitle = $title; 
		$pdf->pageFormat = "A4"; // wide format "A4-L"
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdfFilename = md5(date("Y-m-d H:i:s.U") . "spol_smlouva");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["spol_smlouva"],
			"filename" => $pdfFilename,
			"name" => $title);
		$this->companyDocs->insert($values);
		
		$template_params = array('company' => $company);
		$template = $latte->renderToString($template_file, $template_params);

		$pdf = new PdfResponse($template);
		// optional
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_spolecenska_smlouva_notar";
		$pdf->documentTitle = $title;
		$pdf->pageFormat = "A4"; // wide format "A4-L"
		$pdf->getMPDF()->setFooter("|urad.pirati.cz|"); // footer
		$pdfFilename = md5(date("Y-m-d H:i:s.U") . "spol_smlouva_notar");
		$pdf->save($this->getDataDir(), $pdfFilename);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["spol_smlouva_notar"],
			"filename" => $pdfFilename,
			"name" => $title);
		$this->companyDocs->insert($values);
	}


	public function createSpolecenskaSmlouvaEditovatelna(\Company $company, $test_run=false)
	{

		$template = $this->createTemplate();
		if ($company->getType() == "full") {
			$source = $this->getDocsDir()."/spolecenska_smlouva_notar_full.latte";
		} else {
			$source = $this->getDocsDir()."/spolecenska_smlouva_notar_simple.latte";
		}			
		$template->company = $company;
		$latte = new \Latte\Engine;
		$latte->addFilter('padline', function ($s, $len = 60, $padding=STR_PAD_RIGHT) {
			$string_length = strlen($s);
			if ($string_length > $len){
				$how_many_times = (int)floor($string_length/$len) + 1;
				// uncomment when miracle happens and there will be php 7 on a server
				// $how_many_times = intdiv($string_length, $len) + 1;
				$len = $how_many_times * $len;
				$len = $len - (int)floor($len/20);
				// uncomment when miracle happens and there will be php 7 on a server
				// $len = $len - intdiv($len, 20);
			}
			return str_pad($s, $len, "-", $padding);
		});

		$latte->addFilter('slovne', function($amount, $suffix, $gender='F'){
			$utils = new \App\Utils\DocumentUtils();
			if (intval($amount)){
				return $utils->AmmountWords($amount, $suffix, $gender);
			}
			return "";
		});

		$latte->addFilter('mesic', function($value){
			$utils = new \App\Utils\DocumentUtils();
			if (intval($value)){
				return $utils->Mesic(intval($value));
			}
			return "";
		});
		
		$name = Nette\Utils\Strings::webalize($company->getName());
		$title = $name . "_spolecenska_smlouva_notar_edit";

		$html_content = $latte->renderToString($source, $template->getParameters());
		$html_file = $latte->renderToString($source, $template->getParameters());
		$gen_fname_html = $this->getDataDir().'/'.$company->getId().'.html';        
		file_put_contents($gen_fname_html, $html_file);     

		// Convert with libreoffice HTML -> ODT
		$libreoffice_command = "libreoffice -env:UserInstallation=\"file:///tmp/LibO_Conversion\" --headless --invisible --convert-to odt --outdir '".$this->getDataDir()."' ".$gen_fname_html;
		\Tracy\Debugger::barDump($libreoffice_command);
		$odt = exec(escapeshellcmd($libreoffice_command));        

		// Convert with libreoffice ODT -> DOCX
		$gen_fname_odt = $this->getDataDir().'/'.$company->getId().'.odt';                
		$libreoffice_command_2 = "libreoffice -env:UserInstallation=\"file:///tmp/LibO_Conversion\" --headless --invisible --convert-to docx --outdir '".$this->getDataDir()."' ".$gen_fname_odt;
		\Tracy\Debugger::barDump($libreoffice_command_2);
		$docx = exec(escapeshellcmd($libreoffice_command_2));              
		
		$gen_fname_docx = $this->getDataDir().'/'.$company->getId().'.docx';

		$docxFilename = md5(date("Y-m-d H:i:s.U") . "spol_smlouva_notar_edit");

		\Nette\Utils\FileSystem::rename($gen_fname_docx, $this->getDataDir().'/'.$docxFilename.'.docx');
		\Nette\Utils\FileSystem::delete($gen_fname_html);
		\Nette\Utils\FileSystem::delete($gen_fname_odt);
		$values = array("company_id" => $company->getId(),
			"doc_type" => self::$docsEnum["spol_smlouva_notar"],
			"filename" => $docxFilename,
			"name" => $title);
		if (!$test_run){
			$this->companyDocs->insert($values);
		}
	}
	
	protected function setupInvoiceTemplate(\Company $company, $invNumber, $template)
	{
		$template->company = $company;
		$template->invNumber = $invNumber;

		/* collect all surcharges */
		$template->payments = $this->payments->findAll()->where("step=?", 1);
		$notarP = $this->paymentHelper->getNotaryFees($company->getCapital(),$company->getType());
		$template->notar = $notarP;
		$paymentPersonId = $this->companyPerson->findAll()->where("company_id=? AND acting=2", $company->getId())->fetch();
		$template->paymentDetails = $this->person->findById($paymentPersonId->person_id);
		$template->paymentPlaceDetails = $this->seat->findById($template->paymentDetails->place);

		$template->specialAreas = array();
		if (array_key_exists(90, $company->getAreas())) {
			$template->specialAreas[] = $this->payments->findById(7);
		}
		if (array_key_exists(182, $company->getAreas())) {
			$template->specialAreas[] = $this->payments->findById(8);
		}
		if (array_key_exists(183, $company->getAreas())) {
			$template->specialAreas[] = $this->payments->findById(9);
		}

		// surcharges
		//$template->danCost = $this->payments->findById(13);
		$template->foreignPeopleCost = $this->payments->findById(10);
		$template->foreignAPeopleCost = $this->payments->findById(11);
		$template->actingCost = $this->payments->findById(12);
		$template->complianceCost = $this->payments->findById(23);
	}
	
	protected function getDocsDir()
	{
		return __DIR__ . "/../../templates/docs";
	}
	
	protected function getDataDir()
	{
		return __DIR__ . '/../../../data/';
	}

	public static function hasSpecialDocs($company)
	{
		$specialDocs = false;
		foreach ($company->getNonFreeAreas() as $index => $a) {
			if ($index == 183 || $index == 182 || $index == 9 ||
					$index == 2 || $index == 90) {
				continue;
			}
			$specialDocs = true;
		}
		return $specialDocs;
	}
}
