<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Main;

// request
// extended class with methods to route an HTTP request
class Request extends Main\Request
{
    // config
    public static $config = [];


    // manageRedirect
    // vérifie la requête et manage les redirections possibles
    // certaines errors vont générer un code http 400 plutôt que 404 (bad request)
    // retourne un tableau avec les clés type, code et location
    // gère externalPost, redirection, unsafe et request
    public function manageRedirect(?Redirection $redirection=null):array
    {
        $return = ['type'=>null,'code'=>null,'location'=>null];
        $isAjax = $this->isAjax();
        $isSafe = $this->isPathSafe();
        $isExternalPost = $this->isExternalPost();
        $schemeHost = $this->schemeHost();
        $redirect = $this->redirect();
        $hasExtension = $this->hasExtension();

        // externalPost
        if($isExternalPost === true)
        {
            $return['type'] = 'externalPost';
            $return['code'] = 400;
        }

        else
        {
            // redirection
            if(empty($return['type']) && !empty($redirection))
            {
                $to = $redirection->get($this);

                if(!empty($to))
                {
                    $return['type'] = 'redirection';
                    $return['location'] = $to;
                }
            }

            // unsafe
            if(empty($return['type']) && $isSafe === false)
            {
                $return['type'] = 'unsafe';

                if($isAjax === true)
                $return['code'] = 400;

                else
                {
                    if($this->absolute() !== $schemeHost && !$hasExtension)
                    $return['location'] = $schemeHost;

                    else
                    $return['code'] = 400;
                }
            }

            // request
            if(empty($return['type']) && !empty($redirect))
            {
                $return['type'] = 'request';

                if($isAjax === true)
                $return['code'] = 400;

                else
                $return['location'] = $redirect;
            }

            if($return['location'] !== null && $return['code'] === null)
            $return['code'] = 302;
        }

        return $return;
    }


    // match
    // retourne un tableau avec toutes les routes qui matchs avec la requête
    public function match(Routes $routes,?Main\Session $session=null):?array
    {
        return $routes->match($this,$session);
    }


    // matchOne
    // retourne la première route qui match avec la requête
    public function matchOne(Routes $routes,?Main\Session $session=null):?string
    {
        return $routes->matchOne($this,$session);
    }


    // route
    // retourne la première route qui match avec la requête
    // la route retourné est triggé
    public function route(Routes $routes,?Main\Session $session=null):?Route
    {
        return $routes->route($this,$session);
    }
}

// config
Request::__config();
?>