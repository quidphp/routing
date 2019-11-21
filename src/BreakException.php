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

// breakException
// class for a break exception which breaks the root matching loop
class BreakException extends Main\Exception
{
    // config
    public static $config = [
        'code'=>36 // code de l'exception
    ];
}

// init
BreakException::__init();
?>