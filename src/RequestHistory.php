<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Main;
use Quid\Base;

// requestHistory
// extended class for a collection containing a history of requests
class RequestHistory extends Main\RequestHistory
{
	// config
	public static $config = [];


	// previousRoute
	// retourne la route de la requête précédente
	public function previousRoute(Routes $routes,$fallback=null,bool $hasExtra=true,?Main\Session $session=null):?Route
	{
		$return = null;
		$previous = $this->previousRequest($hasExtra);

		if(!empty($previous))
		$return = $previous->route($routes,$session);

		if(empty($return) && !empty($fallback))
		{
			if(is_string($fallback) && is_a($fallback,Route::class,true))
			$return = $fallback::make();

			elseif($fallback instanceof Route)
			$return = $fallback;
		}

		return $return;
	}


	// previousRedirect
	// permet de rediriger vers la dernière entrée ou un objet route spécifié en premier argument
	// possible de mettre une classe de route ou un objet route à utiliser comme fallback
	public function previousRedirect(Routes $routes,$fallback=null,bool $hasExtra=true,?Main\Session $session=null,?array $option=null):bool
	{
		$return = false;
		$option = Base\Arr::plus(['encode'=>true,'code'=>true,'kill'=>true],$option);
		$previous = $this->previousRoute($routes,$fallback,$hasExtra,$session);

		if(!empty($previous))
		$return = $previous->redirect($option['code'],$option['kill'],$option['encode']);

		return $return;
	}


	// match
	// pour chaque request, retourne un tableau avec toutes les routes qui matchs avec la requête
	public function match(Routes $routes,?Main\Session $session=null):array
	{
		$return = [];

		foreach ($this->request() as $key => $value)
		{
			$return[$key] = $value->match($routes,$session);
		}

		return $return;
	}


	// matchOne
	// pour chaque request, retourne la première route qui match avec la requête
	public function matchOne(Routes $routes,?Main\Session $session=null):array
	{
		$return = [];

		foreach ($this->request() as $key => $value)
		{
			$return[$key] = $value->matchOne($routes,$session);
		}

		return $return;
	}


	// route
	// pour chaque request, retourne la première route qui match avec la requête
	// la route retourné est triggé
	public function route(Routes $routes,?Main\Session $session=null):array
	{
		$return = [];

		foreach ($this->request() as $key => $value)
		{
			$return[$key] = $value->route($routes,$session);
		}

		return $return;
	}
}

// config
RequestHistory::__config();
?>