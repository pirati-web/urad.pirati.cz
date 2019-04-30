<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class PersonTest extends Tester\TestCase
{
	private $container;


	function __construct(Nette\DI\Container $container)
	{
        $this->container = $container;
        $seat = new \Place("test street",
                           "17/299", "arrondissement", "test city", "12345", 
                           "test_state", "Test Owner Name");
        $this->person1 = new \Person(2, "TestName", "TestSurname", "915823/6417", $seat, "","MvDr.","PhD.");
        $this->person2 = new \Person(["choice"=>2,
                                      "name"=>"TestName",
                                      "surname"=>"TestSurname",
                                      "person_id" => "915823/6417",
                                      "street"=> "test_street",
                                      "tel" => 231231,
                                      "title_pre"=>"MvDr.",
                                      "title_suf"=>"PhD."]);                                      
        $this->company1 = new \ExistingCompany("test_company", 2, 384839483, $seat);
        $this->company2 = new \ExistingCompany(["company_name"=>"test_company",
                                                "choice"=>2,
                                                "ico"=>384839483,
                                                "seat"=>$seat]);
	}


	function setUp()
	{
    
	}

    function testCompanyArgs()
    {
        Assert::true( $this->company1->getName() === "test_company" );
        Assert::true( $this->company1->getId() === 384839483 );
    }

    function testCompanyArray()
    {
        Assert::true( $this->company2->getName() === "test_company" );
        Assert::true( $this->company2->getId() === 384839483 );
    }

    function testPersonArgs()
	{
        Assert::true( $this->person1->getName() === "TestName" );
        Assert::true( $this->person1->getFullName() === "MvDr. TestName TestSurname PhD." );
        Assert::true( $this->person1->getBirthDate() === "23.08.1991");
        Assert::true( $this->person1->getId() === "915823/6417");
    }
    
    function testPersonArray()
	{
        Assert::true( $this->person2->getName() === "TestName" );
        Assert::true( $this->person2->getFullName() === "MvDr. TestName TestSurname PhD." );
        Assert::true( $this->person2->getBirthDate() === "23.08.1991");
        Assert::true( $this->person2->getId() === "915823/6417");
	}

}


$test = new PersonTest($container);
$test->setUp();
$test->run();

  
  
            