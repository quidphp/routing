<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;

// _attrRoute
// trait that provides methods to work with routes in the attributes property
trait _attrRoute
{
    // routeAttr
    // retourne le tableau des routes, ou un nom de route, de classe ou une callable de route non triggé
    // pour retourner une version triggé, utiliser la méthode route
    final public function routeAttr($key=null)
    {
        $return = null;
        $route = $this->getAttr('route');

        if(is_string($key) || is_numeric($key))
        {
            $isCallable = static::isCallable($route);

            if($key === 0 && (is_string($route) || $isCallable === true))
            $return = $route;

            elseif(is_array($route) && $isCallable === false)
            {
                if(array_key_exists($key,$route))
                $return = $route[$key];

                elseif(in_array($key,$route,true))
                $return = $key;

                elseif($key === 0)
                $return = current($route);
            }
        }

        else
        $return = $route;

        return $return;
    }


    // routeClassSafe
    // retourne le classe de la route, pas l'objet instantié
    // possible d'overload la classe de la route
    final public function routeClassSafe($key=null,bool $overload=false):?string
    {
        $return = null;
        $key = (is_string($key) || is_numeric($key))? $key:0;
        $route = $this->routeAttr($key);

        if(is_string($route) && $overload === true)
        $return = $route::classOverload();

        else
        $return = $route;

        return $return;
    }


    // routeClass
    // retourne le classe de la route, pas l'objet instantié
    // envoie une exception si pas une string
    // possible d'overload la classe de la route
    final public function routeClass($key=null,bool $overload=false):string
    {
        $return = $this->routeClassSafe($key,$overload);

        if(!is_string($return))
        static::throw($key);

        return $return;
    }


    // routeSafe
    // créer un des objets routes en lien avec l'objet
    // une clé peut être fourni, sinon utilise la clé 0 par défaut
    // peut retourner route ou null
    final public function routeSafe($key=null,$segment=null):?Route
    {
        $return = null;
        $key = (is_string($key) || is_numeric($key))? $key:0;
        $route = $this->routeAttr($key);

        if(!empty($route))
        {
            $obj = $this->makeRoute($key,$route,$segment);

            if($obj instanceof Route)
            $return = $obj;
        }

        return $return;
    }


    // route
    // comme routeSafe mais doit absolument retourner une route
    final public function route($key=null,$segment=null):Route
    {
        return $this->routeSafe($key,$segment) ?: static::throw($key);
    }


    // makeRoute
    // construit la route a créer
    // accepte une callable ou un nom de route ou de classe étendant route
    // peut faire une préparation sur la route avant le make
    final protected function makeRoute($key,$route,$segment=null):?Route
    {
        $return = null;
        $class = $route;

        if(static::isCallable($route))
        $class = $route($this,false);

        if(is_string($class) && $this->shouldPrepareRoute($key,$class))
        $this->routePrepareConfig($key,$class);

        if(static::isCallable($route))
        $return = $route($this,true);

        elseif(is_string($route))
        {
            if($segment === null)
            $segment = $this;

            $return = $route::make($segment);
        }

        return $return;
    }


    // shouldPrepareRoute
    // retourne vrai si la route doit être préparé
    // est utilisé dans la méthode route
    protected function shouldPrepareRoute($key,string $route):bool
    {
        return false;
    }


    // routePrepareConfig
    // permet de préparer une les config d'une route
    // est utilisé dans la méthode route
    protected function routePrepareConfig($key,string $route):void
    {
        return;
    }
}
?>