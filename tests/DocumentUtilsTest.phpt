<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class DocumentUtilsTest extends Tester\TestCase
{
	private $container;


        function __construct(Nette\DI\Container $container)
	{
             $this->container = $container;
             $this->documentUtils = new \App\Utils\DocumentUtils();
	}


	function setUp()
	{
              
        }       


    function testNumbersToText()
    {
        Assert::same( "dva tisíce čtyři sta padesát šest", $this->documentUtils->AmmountWords(2456));
        Assert::same( "dvě stě tisíc", $this->documentUtils->AmmountWords(200000));
        Assert::same( "čtyři sta čtyřicet šest milionů pět set čtyřicet pět tisíc čtyři sta padesát šest", $this->documentUtils->AmmountWords(446545456));
        Assert::same( "jedna", $this->documentUtils->AmmountWords(1));
        Assert::same( "dvě", $this->documentUtils->AmmountWords(2));
        Assert::same( "dva", $this->documentUtils->AmmountWords(2,'','M'));
        Assert::same( "dva tisíce devatenáct", $this->documentUtils->AmmountWords(2019));
        Assert::same( "jedna koruna česká", $this->documentUtils->AmmountWords(1, 'Kč'));
        Assert::same( "prvního", $this->documentUtils->AmmountWords(1, 'radove'));             
        Assert::same( "třicátého", $this->documentUtils->AmmountWords(30, 'radove'));
        Assert::same( "patnáctého", $this->documentUtils->AmmountWords(15, 'radove'));
        Assert::same( "dvacátého třetího", $this->documentUtils->AmmountWords(23, 'radove'));
        Assert::same( "třetího", $this->documentUtils->AmmountWords(3, 'radove'));  
        Assert::same( "druhého", $this->documentUtils->AmmountWords(2, 'radove'));   
    }

}


$test = new DocumentUtilsTest($container);
$test->setUp();
$test->run();

  
  
            