<?php
/**
 * Maite Math library.
 *
 * This source file is subject to the "New BSD License".
 *
 * @author     Vojtěch Knyttl
 * @copyright  Copyright (c) 2010 Vojtěch Knyttl
 * @license    New BSD License
 * @link       http://knyt.tl/
 */

namespace Maite\Utils;

class Math {

	/**
	 * Converts decadic representation of geographic coordinates
	 * to degree-minute-second representation.
	 *
	 * @access public
	 * @static
	 * @param mixed $dec
	 * @return void
	 */
	public static function dec2dms($dec) {
		$vars = explode('.', $dec) ;
		$tempma = ('0.'.$vars[1]) * 3600;
		$min = floor($tempma / 60);
		return (object) array(
			'deg' => $vars[0],
			'min' => $min,
			'sec' => floor($tempma - ($min*60)));
	}



	/**
	 * Converts decadic number to the one of base 25.
	 *
	 * @access public
	 * @static
	 * @param int
	 * @return string
	 */
	public static function toChar($int) {
		$str = '';
		while($int) {
			$str .= chr($int%25+97);
			$int = floor($int / 25);
		}
		return $str;
	}



	/**
	 * Converts from base 25 to decadic.
	 *
	 * @access public
	 * @static
	 * @param string
	 * @return int
	 */
	public static function deChar($str) {
		$len = strlen($str) - 1;
		$val = 0;
		for($n = $len; $n >= 0; $n--) {
			$val = $val * 25 + ord(substr($str, $n, 1)) - 97;
		}
		return $val;
	}
}
