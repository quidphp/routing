<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Main;

// breakException
// class for an exception which breaks the root matching loop
class BreakException extends Main\Exception
{
    // config
    protected static array $config = [
        'code'=>36 // code de l'exception
    ];
}

// init
BreakException::__init();
?>