<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Main;

// breakException
// class for a break exception which breaks the root matching loop
class BreakException extends Main\Exception
{
    // config
    public static $config = [
        'code'=>35 // code de l'exception
    ];
}

// config
BreakException::__config();
?>