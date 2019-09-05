<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/test/blob/master/LICENSE
 */

namespace Quid\Test\Routing;
use Quid\Routing;
use Quid\Main;
use Quid\Base;

// exception
// class for testing Quid\Routing\Exception
class Exception extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// construct
		$e = new Routing\Exception('well');

		// exception
		assert(!$e instanceof Main\Contract\Catchable);
		assert($e->getCode() === 34);

		return true;
	}
}
?>