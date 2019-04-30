<?php

/**
 * Nette database vydaduje u view primarni klice, toto to u vybranych view resi
 */
class MyReflection extends \Nette\Database\Conventions\DiscoveredConventions
{

	public function getPrimary($table)
	{
		switch($table) {
			case "fc_view":
			case "fg_view":
			case "ft_view":
				$table = "fields";
			break;
			case "ca_view":
				$table = "company_actions";
			break;
			case "cp_view":
				$table = "person";
			break;
			case "cp_view":
				$table = "news";
			break;
			default:
			break;
		}
		return parent::getPrimary($table);
	}

}
