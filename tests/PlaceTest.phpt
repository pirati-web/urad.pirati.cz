<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class PlaceTest extends Tester\TestCase
{
	private $container;


	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
        $this->place = new \Place("test street",
                                    "17/299", "arrondissement", "test city", "12345", 
                                    "test_state", "Test Owner Name");
	}


	function setUp()
	{
        
	}


	function testValidData()
	{
        Assert::true( $this->place->getOwnerName() === "Test Owner Name" );
	}

}


$test = new PlaceTest($container);
$test->run();

  
  
            