<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Base;
use Quid\Main;

// requestHistory
// extended class for a collection containing a history of requests
class RequestHistory extends Main\RequestHistory
{
    // config
    protected static array $config = [];


    // previousRoute
    // retourne la route de la requête précédente ou un fallback
    final public function previousRoute(Routes $routes,$fallback=null,bool $hasExtra=true):?Route
    {
        $return = null;
        $previous = $this->previousRequest($hasExtra);

        if(!empty($previous))
        {
            $return = $previous->route($routes);

            if(!empty($return) && (!$return::isRedirectable() || !$return->canTrigger()))
            $return = null;
        }

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
    // ou si fallback est true et que previous est vide, redirige vers le schemeHost
    final public function previousRedirect(Routes $routes,$fallback=null,bool $hasExtra=true,?array $option=null):bool
    {
        $return = false;
        $option = Base\Arr::plus(['encode'=>true,'code'=>true,'kill'=>true],$option);
        $previous = $this->previousRoute($routes,$fallback,$hasExtra);

        if(empty($previous) && $fallback === true)
        $return = Base\Response::redirectSchemeHost($option['code'],$option['kill'],$option['encode']);

        if(!empty($previous))
        $return = Base\Response::redirect($previous->uriAbsolute(),$option['code'],$option['kill'],$option['encode']);

        return $return;
    }


    // match
    // pour chaque request, retourne un tableau avec toutes les routes qui matchs avec la requête
    final public function match(Routes $routes):array
    {
        $return = [];

        foreach ($this->request() as $key => $value)
        {
            $return[$key] = $value->match($routes);
        }

        return $return;
    }


    // route
    // pour chaque request, retourne la première route qui match avec la requête
    final public function route(Routes $routes):array
    {
        $return = [];

        foreach ($this->request() as $key => $value)
        {
            $return[$key] = $value->route($routes);
        }

        return $return;
    }
}

// init
RequestHistory::__init();
?>