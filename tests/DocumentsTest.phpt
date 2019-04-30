<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class DocumentsTest extends Tester\TestCase
{
	private $container;

    /** @var App\Model\Service\DocumentService */
	private $documentService;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
        $this->company = new \Company("test_company", [8, 112]);        
        $this->documentService = $container->getByType("App\Model\Service\DocumentService");  
	}


	function setUp()
	{
        $this->company->setId(-1);
        $this->company->setCapital(1000);
        $this->company->setBank(array("name"=>"CSOB","id"=>1));
        
        $seat = new \Place("test street",
                           "17/299", "arrondissement", "test city", "12345", 
                           "test_state", "Test Owner Name");
        $shares =  [0.4, 0.6];
        $sp1 = new \ExistingCompany("test existing company", 1, 9989937, $seat);
        $sp2 = new \Person(2, "TestName", "TestSurname", "915823/6417", $seat, "","MvDr.","PhD.");
        $spolecnici = [$sp1];//, $sp2];                    
        $this->company->setShares($shares);
        $this->company->setPersons($spolecnici);
        $jednatele = [$sp1, $sp2];     
        $this->company->setActingPersons($spolecnici);
        $this->company->setSeat($seat);
	}

    function testDocumentServiceExists() {
        Assert::true( $this->documentService != null );
    }

	function testSomething()
	{
        dump($this->company->getActingPersons());
        dump($this->company->getPersons());
        $this->documentService->createSpolecenskaSmlouvaEditovatelna($this->company, true);
        $this->company->setType("simple");
        $this->documentService->createSpolecenskaSmlouvaEditovatelna($this->company, true);
		Assert::true( true );
	}

}


$test = new DocumentsTest($container);
$test->setUp();
$test->run();
