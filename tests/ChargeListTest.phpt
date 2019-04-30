<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class ChargeListTest extends Tester\TestCase
{
	private $container;


	function __construct(Nette\DI\Container $container)
	{
        $this->container = $container;
        //($name, $price, $priceWithoutVat, $vat = 0, $info = "", $priceComment = "")
        $this->item1 = new \ChargeItem("item1", 2323, 2223);
        $this->item2 = new \ChargeItem("item2", 2323, 2223, true);
        $this->item3 = new \ChargeItem("item3", 1212, 112);
	}


	function setUp()
	{
        $this->list = new \ChargeList();
        $this->list->add("cat1", "item1", 2323, 2223, true);
        $this->list->add("cat1", "item1.1", 2323, 2223, false);
        $this->list->add("cat2", "item2", 12323, 1223, false);        
        $this->list->add("cat3", "item3", 123, 23, true);                
	}


    function testItems()
    {
        Assert::true( $this->item1->hasVat() === false);
        Assert::true( $this->item2->hasVat() === true);
        Assert::true( $this->item1->getName() === "item1");
        Assert::true( $this->item2->getName() === "item2");
    }

	function testChargeList()
	{
        Assert::true( $this->list != null );
        $items = $this->list->getByCategory("cat1");
        Assert::true( $items[0]->getName() === "item1");
        Assert::true( $items[1]->getName() === "item1.1");
        Assert::true( $this->list->getTotalSum() === 2323+2323+12323+123 );
        Assert::true( $this->list->getSumNotVat() === 2323+12323 );
        Assert::true( $this->list->getSumWithoutVat() === 2223+23 );
        Assert::true( $this->list->getSumVat() === (2323-2223)+(123-23) );
	}

}


$test = new ChargeListTest($container);
$test->setUp();
$test->run();

  
  
            