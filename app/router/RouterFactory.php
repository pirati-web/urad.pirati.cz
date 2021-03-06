<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		//n Route::$defaultFlags = Route::SECURED;
        $router[] = new Route('o-p-auth/logout', 'OPAuth:logout');
		$router[] = new Route('o-p-auth/callback', 'OPAuth:callback');
		$router[] = new Route('o-p-auth/<strategy>', 'OPAuth:auth');
		$router[] = new Route('o-p-auth/<strategy>/oauth2callback', 'OPAuth:auth');
		$router[] = new Route('o-p-auth/<strategy>/oauth_callback', 'OPAuth:auth');
		$router[] = new Route('o-p-auth/<strategy>/int_callback', 'OPAuth:auth');
		
		$router[] = new Route('admin/<presenter>/<action>[/<id>]', array(
            'module' => 'Admin',
            'presenter' => 'Default',
            'action' => 'default',
        ));
		
		//$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = new Route('<presenter>/<action>[/<id>]', [
			//'module' => 'Front',
			'presenter' => array(
				Route::VALUE => 'Homepage',
				//Route::VALUE => 'NewCompany',
				Route::FILTER_TABLE => array(
					"zalozeni-firmy" => "NewCompany"
				),		
			),
			'action' => 'default',
			"id" => NULL
		]);
		
		return $router;
	}

}
