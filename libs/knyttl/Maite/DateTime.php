<?php
/**
 * Maite DateTime Library
 *
 * This source file is subject to the "New BSD License".
 *
 * @author     Vojtěch Knyttl
 * @copyright  Copyright (c) 2010 Vojtěch Knyttl
 * @license    New BSD License
 * @link       http://knyt.tl/
 */

namespace Maite;

use Nette;

class DateTime extends Nette\DateTime {

	const MORNING = 28800;



	public static function today() {
		return strtotime('midnight');
	}
}
