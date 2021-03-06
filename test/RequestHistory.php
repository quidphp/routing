<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Test\Routing;
use Quid\Base;
use Quid\Routing;

// requestHistory
// class for testing Quid\Routing\RequestHistory
class RequestHistory extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $boot = $data['boot'];
        $routes = $boot->routes();
        $request = new Routing\Request('/test.jpg');
        $request2 = new Routing\Request(Base\Request::export());
        $request3 = new Routing\Request('http://google.com');
        $request4 = new Routing\Request('/testbla|.jpg');
        $request4->setMethod('post');
        $rh = new Routing\RequestHistory();
        $rh->add($request2);
        $rh->add($request3);
        $rh->add($request4);
        $rh->add($request);
        assert($rh->add($request2) === $rh);

        // previousRoute
        assert(empty($rh->previousRoute($routes)));

        // previousRedirect

        // match
        assert(count($rh->match($routes)) === 5);
        assert(is_array($rh->match($routes)[0]));

        // route
        assert(count($rh->route($routes)) === 5);
        assert($rh->route($routes)[0] instanceof Routing\Route);

        return true;
    }
}
?>