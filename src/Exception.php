<?php
declare(strict_types=1);
namespace Quid\Routing;
use Quid\Main;

// exception
class Exception extends Main\Exception
{
	// config
	public static $config = array(
		'code'=>34 // code de l'exception
	);
}

// config
Exception::__config();
?>