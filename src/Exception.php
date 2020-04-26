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
use Quid\Main;

// exception
// class used for a route exception, the next available route will instead be triggered
class Exception extends Main\Exception
{
    // config
    public static array $config = [
        'code'=>35 // code de l'exception
    ];
}

// init
Exception::__init();
?>