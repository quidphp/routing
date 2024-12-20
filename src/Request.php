<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Base\Html;
use Quid\Main;

// request
// extended class with methods to route an HTTP request
class Request extends Main\Request
{
    // config
    protected static array $config = [
        'navigation'=>'Quid-Navigation'
    ];


    // construct
    // construit un objet request
    // permet de mettre un objet route en argument
    final public function __construct($value=null,?array $attr=null)
    {
        if($value instanceof Route)
        $value = static::fromRoute($value);

        parent::__construct($value,$attr);
    }


    // isAjaxNavigation
    // retourne vrai si la requête vient de la navigation history api
    final public function isAjaxNavigation():bool
    {
        return $this->isAjax() && $this->isHeader($this->getAttr('navigation'));
    }


    // isAjaxNotNavigation
    // retourne vrai si la requête est ajax mais pas la navigation via history api
    final public function isAjaxNotNavigation():bool
    {
        return $this->isAjax() && !$this->isHeader($this->getAttr('navigation'));
    }


    // isPathApi
    // retourne vrai si la requête semble être un api
    final public function isPathApi():bool
    {
        return str_starts_with($this->path(),'/api/') && strlen($this->path()) > 5;
    }


    // manageRedirect
    // vérifie la requête et manage les redirections possibles
    // certaines errors vont générer un code http 400 plutôt que 404 (bad request)
    // retourne un tableau avec les clés type, code et location
    // gère externalPost, redirection, requestUnsafe et requestInvalid
    final public function manageRedirect(?Redirection $redirection=null):array
    {
        $return = ['type'=>null,'code'=>null,'location'=>null];
        $isAjax = $this->isAjax();
        $isSafe = $this->isPathSafe();
        $isExternalPost = $this->isExternalPost();
        $schemeHost = $this->schemeHost();
        $redirect = $this->redirect();
        $hasExtension = $this->hasExtension();
        $argumentNotCli = $this->isPathArgumentNotCli();
        $isApi = $this->isPathApi();

        // ajouté récemment pour pouvoir éviter la redirection de language si c'est un api
        if($isApi)
        $requestInvalid = false;
        else
        $requestInvalid = (!empty($redirect) || $argumentNotCli);

        // externalPost
        if($isExternalPost === true)
        {
            $return['type'] = 'externalPost';
            $return['code'] = 400;
        }

        else
        {
            // redirection
            if(!empty($redirection))
            {
                $to = $redirection->get($this);

                if(!empty($to))
                {
                    $return['code'] = 301;
                    $return['location'] = $to;
                }
            }

            // requestUnsafe
            if(empty($return['location']) && $isSafe === false)
            {
                $return['type'] = 'requestUnsafe';

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

            // requestInvalid
            if(empty($return['location']) && $requestInvalid === true)
            {
                $redirect = $redirect ?: $schemeHost;

                $return['type'] = 'requestInvalid';

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
    final public function match(Routes $routes,bool $fallback=false,bool $debug=false):?array
    {
        return $routes->match($this,$fallback,$debug);
    }


    // route
    // retourne la première route qui match avec la requête
    final public function route(Routes $routes,$after=null,bool $fallback=false,bool $debug=false):?Route
    {
        return $routes->route($this,$after,$fallback,$debug);
    }


    // fromRoute
    // retourne un tableau de départ request à partir d'un objet route
    final public static function fromRoute(Route $route):array
    {
        $return = [];
        $return['uri'] = $route->uriAbsolute();
        $return['ajax'] = $route::isAjax() ?? false;

        if($route::isMethod('post'))
        {
            $session = $route::session();
            $return['method'] = 'post';
            $postMatch = $route::getConfig('match/post');
            $post = [];

            if($route::hasMatch('csrf'))
            {
                $name = $session->getCsrfName();
                $post[$name] = $session->csrf();
            }

            if($route::hasMatch('genuine'))
            {
                $name = Html::getGenuineName();
                $post[$name] = '';

                $name = Html::getGenuineName(2);
                $post[$name] = 1;
            }

            if($route::hasMatch('captcha'))
            {
                $name = $session->getCaptchaName();
                $post[$name] = $session->captcha();
            }

            if(is_array($postMatch) && !empty($postMatch))
            {
                foreach ($postMatch as $key => $value)
                {
                    if(is_string($key))
                    $post[$key] = $value;

                    elseif(is_string($value))
                    $post[$value] = '';
                }
            }

            $return['post'] = $post;
        }

        else
        $return['method'] = 'get';

        return $return;
    }
}

// init
Request::__init();
?>