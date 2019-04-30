<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class CompanyTest extends Tester\TestCase
{
	private $container;


	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
        $this->company = new \Company("test_company", [8, 112]);
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
        $sp2 = new \Person(2, "TestName", "TestSurname", 1, $seat, "","MvDr.","PhD.");
        $spolecnici = [$sp1, $sp2];                    
        $this->company->setShares($shares);
        $this->company->setPersons($spolecnici);
        $jednatele = [$sp1, $sp2];        
	}

    function getCompany(){
        return $this->company;
    }


	function testSomething()
	{
        Assert::true( $this->company->getName() === "test_company" );
	}

}


$test = new CompanyTest($container);
$test->setUp();
$test->run();

  
  
            