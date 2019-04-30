<?php

namespace Maite\Diagnostics;

class Debugger extends \Tracy\Debugger {

	public static $lastTimestamp = 0;



	public static function tick() {
		self::$lastTimestamp += self::timer();
		$trace = debug_backtrace();
		$trace = $trace[0];
		echo \Nette\Utils\HTML::el('pre')->style('background-color: #fff')
			->setText(ceil(self::$lastTimestamp*1000).' ms ['.
				str_replace(ROOT_DIR, '', $trace['file']).':'.$trace['line'].']');
	}
}
