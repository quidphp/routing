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

// nav
// class for testing Quid\Routing\Nav
class Nav extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $nav = new Routing\Nav();

        // route

        // map
        assert($nav->set('ok','ok') === $nav);
        assert($nav->get('ok') === 'ok');

        return true;
    }
}
?>