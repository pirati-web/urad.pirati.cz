<?php

interface abstractPerson {
    
    public function setDbId($dbId);
    
    public function getDbId();
    
    public function getFullName();
    
    public function dataAsSqlInput();
    
    public function getSeat();
    
    public function getDate();

    public function getId();

    public function getFullTextOutput();
}
