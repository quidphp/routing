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

// request
// class for testing Quid\Routing\Request
class Request extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $isCli = Base\Server::isCli();
        $lc = new Routing\Request('/sada/ok');
        $redirection = new Routing\Redirection(['/en/james/ok'=>'/lol/ok']);
        $badExtension = new Routing\Request('/james/ok.jpg');
        $redi = new Routing\Request('/en/james/ok');
        $doubleSlash = new Routing\Request('/sada//ok');
        $endSlash = new Routing\Request('/asdok/ok/');
        $externalPost = new Routing\Request(['uri'=>'/external','method'=>'post','headers'=>['referer'=>'https://google.com']]);
        $nl = new Routing\Request('browserconfig.xml');
        $arg = new Routing\Request('-v');
        $argCli = new Routing\Request(['-v','cli'=>true]);

        // construct

        // isAjaxNavigation
        assert(!$badExtension->isAjaxNavigation());

        // isAjaxNotNavigation
        assert(!$badExtension->isAjaxNotNavigation());

        // manageRedirect
        assert($badExtension->manageRedirect() === ['type'=>null,'code'=>null,'location'=>null]);
        assert($externalPost->manageRedirect() === ['type'=>'externalPost','code'=>400,'location'=>null]);
        assert($doubleSlash->manageRedirect($redirection) === ['type'=>'requestUnsafe','code'=>302,'location'=>Base\Request::schemeHost()]);
        assert($nl->manageRedirect() === ['type'=>null,'code'=>null,'location'=>null]);
        assert($redi->manageRedirect($redirection)['type'] === null);
        assert($redi->manageRedirect($redirection)['code'] === 301);
        assert($redi->manageRedirect() === ['type'=>null,'code'=>null,'location'=>null]);
        assert($endSlash->manageRedirect($redirection) === ['type'=>'requestInvalid','code'=>302,'location'=>'/en/asdok/ok']);
        assert($lc->manageRedirect() === ['type'=>'requestInvalid','code'=>302,'location'=>'/en/sada/ok']);
        assert($argCli->manageRedirect() === ['type'=>null,'code'=>null,'location'=>null]);
        assert($arg->manageRedirect() === ['type'=>'requestInvalid','code'=>302,'location'=>Base\Request::schemeHost()]);

        // match

        // route

        // fromRoute

        return true;
    }
}
?>