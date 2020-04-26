<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 * Readme: https://github.com/quidphp/routing/blob/master/README.md
 */

namespace Quid\Routing;
use Quid\Base;
use Quid\Main;

// flash
// class for a collection containing flash-like data, manages route key
class Flash extends Main\Flash
{
    // config
    public static array $config = [];


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
                $return = Base\Arr::append($return,$segments);
            }
        }

        return parent::onPrepareKey($return);
    }
}
?>