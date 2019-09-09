<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Main;

// nav
// class for storing route navigation-related data
class Nav extends Main\Map
{
    // config
    public static $config = [];


    // map
    protected static $is = 'string'; // les données de nav doivent être des routes, donc is est string
    protected static $allow = ['set','unset','empty','serialize']; // méthodes permises


    // route
    // génère une route à partir de celle gardé dans l'objet
    public function route($value):?Route
    {
        $return = null;
        $class = $value;
        $uri = $this->get($value);

        if(!empty($uri))
        {
            if(is_array($value))
            $class = current($value);

            $request = Request::newOverload($uri);

            if(is_string($class))
            {
                $route = $class::make($request);

                if($route->routeRequest()->isRouteRequestCompatible())
                $return = $route;
            }
        }

        return $return;
    }
}
?>