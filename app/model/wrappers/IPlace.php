<?php

/**
 *
 */
interface IPlace
{
	public function hasOwnSeat();
	
	public function getFullAddress();
	
	public function dataAsSqlInput();
	
	public function getOwnerName();
	
	public function getStreet();

    public function getStreetNumber();
	
    public function getCityPart();

    public function getCity();

    public function getZipCode();

    public function getState();
}
