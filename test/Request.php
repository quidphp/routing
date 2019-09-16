<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Test\Routing;
use Quid\Base;
use Quid\Routing;

// request
// class for testing Quid\Routing\Request
class Request extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // prepare
        $redirection = new Routing\Redirection(['/en/james/ok'=>'/lol/ok']);
        $badExtension = new Routing\Request('/james/ok.jpg');
        $redi = new Routing\Request('/en/james/ok');
        $doubleSlash = new Routing\Request('/sada//ok');
        $endSlash = new Routing\Request('/asdok/ok/');
        $externalPost = new Routing\Request(['uri'=>'/external','method'=>'post','headers'=>['referer'=>'https://google.com']]);
        $nl = new Routing\Request('browserconfig.xml');

        // manageRedirect
        assert($badExtension->manageRedirect() === ['type'=>null,'code'=>null,'location'=>null]);
        assert($externalPost->manageRedirect() === ['type'=>'externalPost','code'=>400,'location'=>null]);
        assert($doubleSlash->manageRedirect($redirection) === ['type'=>'unsafe','code'=>302,'location'=>Base\Request::schemeHost()]);
        assert($nl->manageRedirect() === ['type'=>null,'code'=>null,'location'=>null]);
        assert($redi->manageRedirect($redirection)['type'] === 'redirection');
        assert($redi->manageRedirect() === ['type'=>null,'code'=>null,'location'=>null]);
        assert($endSlash->manageRedirect($redirection) === ['type'=>'request','code'=>302,'location'=>'/en/asdok/ok']);

        // match

        // matchOne

        // route

        return true;
    }
}
?>