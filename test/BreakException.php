<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Test\Routing;
use Quid\Base;
use Quid\Main;
use Quid\Routing;

// breakException
// class for testing Quid\Routing\BreakException
class BreakException extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // construct
        $e = new Routing\BreakException('well');

        // exception
        assert(!$e instanceof Main\Contract\Catchable);
        assert($e->getCode() === 35);

        return true;
    }
}
?>