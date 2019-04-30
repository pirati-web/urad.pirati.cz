<?php

namespace App\Model\Helper;

/**
 * PaymentHelper
 */
class PaymentHelper
{
	public function getNotaryFees($value, $companyType)
	{
		if ($companyType == "full") {
			$base = 4000;
		} else {
			$base = 2000;
		}
		$sum = 0;
		$percentage = array(0.02, 0.012, 0.006, 0.003, 0.002, 0.001, 0.0005);
		$values = array(0, 100000, 500000, 1000000, 3000000, 20000000, 30000000, 100000000);
		for ($i = 1; $i < count($percentage); $i++) {
			if ($value > ($values[$i] - $values[$i - 1])) {
				$sum += ($values[$i] - $values[$i - 1]) * $percentage[$i - 1];
				$value -= $values[$i];
			} else {
				$sum += $value * $percentage[$i - 1];
				break;
			}
		}
		return ($sum > $base) ? $sum : $base;
	}
}
