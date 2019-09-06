<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Test\Routing;
use Quid\Routing;
use Quid\Base;

// nav
// class for testing Quid\Routing\Nav
class Nav extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
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