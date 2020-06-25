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

// flash
// class for a collection containing flash-like data, manages route key
class Flash extends Main\Flash
{
    // config
    protected static array $config = [];


    // onPrepareKey
    // préparation d'une clé pour flash
    // gestion de l'objet route
    final protected function onPrepareKey($return)
    {
        if($return instanceof Route)
        {
            $route = $return;
            $return = [];
            $return[] = $route::classFqcn();

            if($route::isSegmentClass())
            {
                $segments = array_values($route->segments());
                $return = Base\Arr::merge($return,$segments);
            }
        }

        return parent::onPrepareKey($return);
    }
}
?>