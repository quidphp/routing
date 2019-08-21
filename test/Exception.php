<?php
declare(strict_types=1);
namespace Quid\Routing\Test;
use Quid\Routing;
use Quid\Main;
use Quid\Base;

// exception
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