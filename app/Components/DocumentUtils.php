<?php

namespace App\Utils;

use Nette;

/* 
credits
http://www.builder.cz/cz/forum/tema-1323303-zobrazeni-castky-slovy-v-php-castka-slovy/
*/


class DocumentUtils //extends Nette\Object
{

	use Nette\SmartObject;

    private $zero = "nula"; 
    private $two = "dvě"; 
    private $namesSmall =array("jedna", "dva", "tři", "čtyři", "pět", "šest", "sedm", "osm", "devět", "deset", 
    "jedenáct", "dvanáct", "třináct", "čtrnáct", "patnáct", "šestnáct", "sedmnáct", "osmnáct", "devatenáct"); 
    private $namesTens =array( "dvacet", "třicet", "čtyřicet", "padesát", "šedesát", "sedmdesát", "osmdesát", "devadesát" ); 
    private $namesHundreds =array( "jedno sto", "dvě stě", "tři sta", "čtyři sta", "pět set", "šest set", "sedm set", "osm set", "devět set" ); 
    private $namesThousands =array( "jeden tisíc", "dva tisíce", "tři tisíce", "čtyři tisíce", "tisíc" ); 
    private $namesMillions =array( "jeden milion", "dva miliony", "tři miliony", "čtyři miliony", "milionů" ); 
    
    private $namesCrowns =array( "korun českých", "koruna česká", "koruny české", "koruny české", "koruny české", "korun českých" ); 
    private $namesPercentage =array( "procent", "procento", "procenta", "procenta", "procenta", "procent" ); 
    private $WordsSeparator = " ";
    private $months = array("ledna", "února", "března","dubna","května","června","července","srpna","září","října","listopadu","prosince");

    function ApplySeparator($amountInWords)
    {
        return trim(str_Replace(" ", $this->WordsSeparator, $amountInWords));
    }
    
    function GetAmountPart_BelowHundred($amount)
    {
        if ($amount == 0) {
            return "";
        }
        $hundreds = floor($amount / 100);
        $amountBelowHundred = $amount - ($hundreds * 100);
        
        if ($amountBelowHundred == 0) {
            return "";
        }
        $tens = floor($amountBelowHundred / 10);
        $units = $amountBelowHundred - ($tens * 10);
        $result = "";
        if ($tens >= 2) {
            $result.= $this->namesTens[$tens - 2] . $this->WordsSeparator;
        }
    
        if ($amount >= 1 && $amountBelowHundred < 20) {
            $result.= $this->namesSmall[$amountBelowHundred - 1] . $this->WordsSeparator;
        }
        else
        if ($units > 0) {
            $result.= $this->namesSmall[$units - 1] . $this->WordsSeparator;    
        }
    
        return $result;
    }
    
    function GetAmountPart_Hundreds($amount)
    {
        if ($amount < 100) {
             return "";
        }
        $thousands = floor($amount / 1000);
        $amountBelowThousand = $amount - ($thousands * 1000);
        if ($amountBelowThousand < 100) {
            return "";
        }
        $hundreds = floor($amountBelowThousand / 100);
        $result = "";
        $result.= $this->namesHundreds[$hundreds - 1] . $this->WordsSeparator;
        return $result;
    }
    
    function GetAmountPart_Thousands($amount)
    {
        if ($amount < 1000) {
             return "";
        }
        $millions = floor($amount / 1000000);
        $amountBelowMillion = $amount - ($millions * 1000000);
        if ($amountBelowMillion < 100) {
            return "";
        }
        $thousands = $amountBelowMillion / 1000;
        $result = "";
        if ($thousands <= 4) {
            $result.= $this->namesThousands[$thousands - 1] . $this->WordsSeparator;
        }
        else {
            $result.= $this->AmmountWords($thousands) . $this->WordsSeparator . $this->namesThousands[count($this->namesThousands) - 1] . $this->WordsSeparator;
        }
    
        return $result;
    }
    
    function GetAmountPart_Millions($amount)
    {
        if ($amount < 1000000) { 
            return "";
        }
        $millions = floor($amount / 1000000);
        $result = "";
        if ($millions <= 4) {
            $result.= $this->namesMillions[$millions - 1] . $this->WordsSeparator;
        }
        else {
            $result.= $this->AmmountWords($millions) . $this->WordsSeparator . $this->namesMillions[count($this->namesMillions) - 1] . $this->WordsSeparator;
        }
    
        return $result;
    }

    function suffixName($amount, $names){
        if ($amount > 5){
            return $names[count($names) -1];
        }
        else {
            return $names[$amount];
        }
    }

    function orderingSuffix($amount, $result) {
        $elements = explode($this->WordsSeparator, $result);
        $number_as_text = strval($amount);
        $last_element = intval(substr($number_as_text, strlen($number_as_text)-1));
        if ($amount < 11 || $amount > 19) {
            switch ($last_element) {
                case 1:
                    $elements[count($elements)-1] = "prvního";
                    break;
                case 2:
                    $elements[count($elements)-1] = "druhého";
                    break;
                case 3:
                    $elements[count($elements)-1] = "třetího";
                    break;
                case 4:
                    $elements[count($elements)-1] = "čtvrtého";
                    break;
                case 5:
                    $elements[count($elements)-1] = "pátého";
                    break;
                case 9:
                    $elements[count($elements)-1] = "devátého";
                    break;
                case 10:
                    $elements[count($elements)-1] = "desátého";
                    break;
                default:
                    $elements[count($elements)-1] .= "ého";
            }
        } else {
            $elements[count($elements)-1] .= "ého";
        }
        if ($amount > 19) {
            $shift = ($amount % 10 == 0) ? 1 : 2;
            switch (intval($amount/10)) {
                case 2:
                    $elements[count($elements)-$shift] = "dvacátého";
                    break;
                case 3:
                    $elements[count($elements)-$shift] = "třicátého";
                    break;
            }
        }        
        return implode($this->WordsSeparator, $elements);
    }
    
    function AmmountWords($amount, $suffix='', $gender='F')
    {
        if ($suffix != "radove" ) {
            if ($amount == 0) return $this->zero;
            if ($gender=='M') {
                if ($amount == 1) return "jeden";
                if ($amount == 2) return "dva";
            }    
            if ($amount == 2) return $this->two; // aby to vrátilo "dvě" místo "dva"
        }
        
    
        // string $amountString = $amount.ToString("##########0");
    
        $result = "";
        if ($amount >= 1000000) {
            $result.= $this->GetAmountPart_Millions($amount);
        }
    
        if ($amount >= 1000) {
            $result.= $this->GetAmountPart_Thousands($amount);
        }
    
        if ($amount >= 100) {
            $result.= $this->GetAmountPart_Hundreds($amount);
        }
    
        $result.= $this->GetAmountPart_BelowHundred($amount);
        $result = $this->ApplySeparator($result);
        switch ($suffix) {
            case '%':
                $result .= $this->WordsSeparator . $this->suffixName($amount, $this->namesPercentage);
                break;
            case 'Kč':
                $result .= $this->WordsSeparator . $this->suffixName($amount, $this->namesCrowns);
                break;
            case "radove":
                $result = $this->orderingSuffix($amount, $result);
                break;
            default:
                break;
        }
        return $result;
    }

    function Mesic($value) {
        if ($value > 0 && $value < 13) {
            return $this->months[$value-1];
        }else {
            return "";
        }
    }
}