<?php

class otherServices {
    
    private $values;
    
    function __construct($fields) {
        foreach($fields as $key=>$value){
            $this->values[$key] = $value;
        }
    }
    
    function getValues() {
        return $this->values;
    }

    function setValues($values) {
        $this->values = $values;
    }

        
    function getAccountance() {
        return $this->values["accountance"];
    }

    function setAccountance($accountance) {
        $this->values["accountance"] = $accountance;
    }

    function merge($other){
        $newValues = array();
        foreach ($other as $key=>$value){
            $newValues[$key] = $value;
        }
        foreach($this->values as $key=>$value){
            $newValues[$key] = $value;
        }
        $this->values = $newValues;
    }
    


}
