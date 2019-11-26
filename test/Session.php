<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 * Readme: https://github.com/quidphp/routing/blob/master/README.md
 */

namespace Quid\Test\Routing;
use Quid\Base;
use Quid\Routing;

// session
// class for testing Quid\Routing\Session
class Session extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $boot = $data['boot'];
        $s = $boot->session();

        // structureNav

        // nav
        assert($s->nav() instanceof Routing\Nav);

        // navEmpty
        assert($s->navEmpty() === $s);

        return true;
    }
}
?>