<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Base;
use Quid\Main;

// routeRequest
// class that analyzes if a request matches a route
class RouteRequest extends Main\Root
{
    // config
    protected static array $config = [];


    // dynamique
    protected string $route; // nom de la classe de la route
    protected Main\Request $request; // copie ou référence de la requête
    protected array $valid = []; // garde en mémoire les tests passés
    protected $fallback = null; // garde en mémoire la raison que la route ira en fallback


    // construct
    // construit l'objet routeRequest et lance le processus de match
    // si request est vide prend la requête courante
    // session doit être inclu pour faire les de role, session et csrf
    public function __construct(Route $route,$request=null)
    {
        $this->setRoute($route);
        $this->setRequest($request);
    }


    // toString
    // retourne le nom de la route
    final public function __toString():string
    {
        return static::route();
    }


    // clone
    // clone est permis
    final public function __clone()
    {
        return;
    }


    // reset
    // reset les vérifications de l'objet à l'état initial
    protected function reset():void
    {
        $this->valid = [];
        $this->fallback = null;
    }


    // isValid
    // retourne vrai si la route et la requête match
    public function isValid(Main\Session $session,bool $exception=false):bool
    {
        return $this->isValidMatch($session,$exception);
    }


    // checkValid
    // envoie une exception si la route et la requête n'ont pas passés les tests match
    public function checkValid():bool
    {
        return $this->valid('match') ?: static::throw();
    }


    // isValidMatch
    // retourne vrai si la route et la requête match
    // si la propriété match et null, lance match
    final public function isValidMatch(Main\Session $session,bool $exception=false):bool
    {
        $return = false;

        if(!$this->valid('match'))
        $this->validateMatch($session,$exception);

        $return = $this->valid('match');

        return $return;
    }


    // checkValidMatch
    // envoie une exception si la route et la requête n'ont pas passés le test match
    final public function checkValidMatch():self
    {
        if(!$this->valid('match'))
        static::throw();

        return $this;
    }


    // isRequestInst
    // retourne vrai si la requête de l'objet routeRequest et la requête courante, storé dans core/request inst
    final public function isRequestInst():bool
    {
        return $this->request() === Main\Request::instSafe();
    }


    // valid
    // retourne la propriété valid, qui contient les tests passés par l'objet
    // possible de retourner seulement le résultat d'un test
    final public function valid(?string $key=null)
    {
        $return = $this->valid;

        if(is_string($key))
        $return = (array_key_exists($key,$this->valid) && is_bool($this->valid[$key]))? $this->valid[$key]:false;

        return $return;
    }


    // fallback
    // retourne la raison du fallback
    final public function fallback()
    {
        return $this->fallback;
    }


    // setFallback
    // change la valeur de la propriété fallback de l'objet
    // met un tableau si la clé est timeout et value est string
    final protected function setFallback(string $key,$value,Main\Session $session):void
    {
        if($key === 'timeout')
        {
            $fallback = $key;
            $timedOut = $this->timedOut($value,$session);

            if($timedOut !== null)
            {
                $route = $this->route();
                $route::timeoutStamp($timedOut);
                $fallback = [$key,$timedOut];
            }
        }

        else
        $fallback = $key;

        $this->fallback = $fallback;
    }


    // route
    // retourne le nom de classe de la route
    final public function route():string
    {
        return $this->route;
    }


    // setRoute
    // change le nom de classe de la route
    // la classe doit être une sous-classe de route
    // lance la méthode reset
    public function setRoute(Route $route):self
    {
        $this->reset();
        $this->route = $route::classFqcn();

        return $this;
    }


    // routePath
    // retourne le path de la route
    // utilise la route lié à l'objet
    final public function routePath(?string $lang=null,bool $null=false,bool $pathMatch=false)
    {
        $return = null;
        $pathMatch = ($pathMatch === true)? $this->request()->pathMatch():null;
        $return = static::pathFromRoute($this->route(),$lang,$null,$pathMatch);

        return $return;
    }


    // request
    // retourne la requête de l'objet
    final public function request():Main\Request
    {
        return $this->request;
    }


    // setRequest
    // change la requête de l'objet
    // si requête est null, prend la requête read-only de inst ou crée une nouvelle requête live
    // lance la méthode reset
    public function setRequest($request=null):self
    {
        $this->reset();

        if(is_string($request) || is_array($request))
        $request = Main\Request::newOverload($request);

        elseif($request === null || (is_object($request) && !$request instanceof Main\Request))
        $request = Main\Request::instSafe() ?? Main\Request::live();

        static::typecheck($request,Main\Request::class);
        $this->request = $request;

        return $this;
    }


    // validateMatch
    // lance le processus de match entre la route et la request
    // si exception est true, lance une exception avec le nom de la clé où le match bloque
    final public function validateMatch(Main\Session $session,bool $exception=false):bool
    {
        $return = false;
        $lang = $session->lang();
        $route = $this->route();
        $match = $route::getConfig('match') ?? [];

        $path = $this->routePath($lang,true,true);
        $emptyPath = $this->routePath(null,true,true);
        $go = false;

        if((is_string($path) || $path === null) && $this->path($path))
        $go = true;

        elseif((is_string($emptyPath) || $emptyPath === null) && $this->path($emptyPath))
        $go = true;

        if($go === true)
        {
            foreach ($match as $key => $value)
            {
                if(is_string($key))
                {
                    if($value === null)
                    $return = true;

                    elseif($this->hasMethod($key))
                    $return = $this->$key($value,$session);

                    elseif(static::isCallable($value))
                    $return = $value($this,$session);

                    else
                    static::throw('invalidMatchKey',$key);

                    if($return === false)
                    {
                        $this->setFallback($key,$value,$session);

                        if($exception === true)
                        static::throw($route,$key,$value,$return);

                        break;
                    }
                }
            }
        }

        elseif($exception === true)
        static::throw($route,'go',$path,$emptyPath);

        return $this->valid['match'] = $return;
    }


    // validateArray
    // validate une valeur dans un array
    // utiliser pour valider headers, query et post
    protected function validateArray($value,array $array):bool
    {
        return Base\Validate::arr($value,$array);
    }


    // path
    // retourne vrai si la requête et la route match le path
    // path null match
    public function path(?string $value):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        elseif(is_string($value))
        {
            $match = $this->request()->pathMatch();
            $value = Base\Path::stripStart($value);
            $return = ($value === $match);
        }

        return $return;
    }


    // ssl
    // retourne vrai si la requête et la route match ssl
    final public function ssl($value):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        elseif(is_bool($value))
        $return = ($value === $this->request()->isSsl());

        return $return;
    }


    // ajax
    // retourne vrai si la requête et la route match ajax
    final public function ajax($value):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        elseif(is_bool($value))
        $return = ($value === $this->request()->isAjax());

        return $return;
    }


    // cli
    // retourne vrai si la requête et la route match cli
    final public function cli($value):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        elseif(is_bool($value))
        $return = ($value === $this->request()->isCli());

        return $return;
    }


    // host
    // retourne vrai si la requête et la route match host
    final public function host($value):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        else
        {
            $host = $this->request()->host();

            if(!empty($host))
            {
                if(is_string($value))
                $value = [$value];

                $return = (is_array($value) && !empty($value) && in_array($host,$value,true));
            }
        }

        return $return;
    }


    // method
    // retourne vrai si la requête et la route match method
    final public function method($value):bool
    {
        $return = false;

        if($value === null || $value === false || $value === true)
        $return = true;

        else
        {
            $method = $this->request()->method();

            if(!empty($method))
            {
                if(is_string($value))
                $value = [$value];

                if(is_array($value) && !empty($value))
                {
                    $value = Base\Arr::map($value,fn($v) => strtolower($v));
                    $return = (in_array($method,$value,true));
                }
            }
        }

        return $return;
    }


    // header
    // retourne vrai si la requête et la route match header de requête
    // insensible à la case
    final public function header($value):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        else
        {
            $headers = $this->request()->headers();
            $headers = Base\Arrs::keysValuesLower($headers);

            if(is_array($value))
            $value = Base\Arrs::keysValuesLower($value);

            elseif(is_string($value))
            $value = Base\Str::lower($value);

            $return = $this->validateArray($value,$headers);
        }

        return $return;
    }


    // lang
    // retourne vrai si la requête et la route match method
    final public function lang($value):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        else
        {
            $lang = $this->request()->lang();

            if(!empty($lang))
            {
                if(is_string($value))
                $value = [$value];

                $return = (is_array($value) && !empty($value) && in_array($lang,$value,true));
            }
        }

        return $return;
    }


    // ip
    // retourne vrai si la requête et la route match ip
    final public function ip($value):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        else
        {
            if(is_string($value))
            $value = [$value];

            if(is_array($value))
            {
                $ip = $this->request()->ip();
                $return = (!empty($ip) && Base\Ip::allowed($ip,$value));
            }
        }

        return $return;
    }


    // query
    // retourne vrai si la requête et la route match query
    final public function query($value):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        else
        {
            $query = $this->request()->queryArray();
            $return = $this->validateArray($value,$query);
        }

        return $return;
    }


    // post
    // retourne vrai si la requête et la route match post
    final public function post($value):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        else
        {
            $post = $this->request()->post();
            $return = $this->validateArray($value,$post);
        }

        return $return;
    }


    // genuine
    // retourne vrai si le post de la requête contient la clé genuine et que le contenu est vide
    final public function genuine($value):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        else
        $return = $this->request()->hasValidGenuine();

        return $return;
    }


    // role
    // retourne vrai si la route et le rôle de la session match
    final public function role($value,Main\Session $session):bool
    {
        return static::allowed($value,$session->role());
    }


    // session
    // retourne vrai si la route et la session match
    // la validation se fait en utilisation des noms de méthode sur l'objet de session
    final public function session($value,Main\Session $session):bool
    {
        $return = false;

        if($value === null)
        $return = true;

        else
        {
            if(!is_array($value))
            $value = [$value];

            foreach ($value as $method)
            {
                $return = false;

                if(is_string($method))
                $return = $session->$method();

                if($return === false)
                break;
            }
        }

        return $return;
    }


    // csrf
    // retourne vrai si la chaîne csrf de la requête et de la session match
    final public function csrf($value,?Main\Session $session=null):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        elseif($value === true && !empty($session))
        {
            $requestCsrf = $this->request()->csrf();
            $return = ($session->isCsrf($requestCsrf));
        }

        return $return;
    }


    // captcha
    // retourne vrai si la chaîne captcha de la requête et de la session match
    final public function captcha($value,?Main\Session $session=null):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        elseif($value === true && !empty($session))
        {
            $requestCaptcha = $this->request()->captcha();
            $return = ($session->isCaptcha($requestCaptcha));
        }

        return $return;
    }


    // timeout
    // retourne vrai si tous les timeouts définis sont valides (et non pas en timeout)
    // un nom de timeout non existant n'est pas timedOut, donc retourne true
    // si timeout est true, prend tous les timeouts définis
    final public function timeout($value,?Main\Session $session=null):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        elseif(!empty($session))
        {
            $timedOut = $this->timedOut($value,$session);
            $return = ($timedOut === null);
        }

        return $return;
    }


    // timedOut
    // retourne le nom du timeout qui est timedOut
    // si timeout est true, prend tous les timeouts définis
    final protected function timedOut($value,Main\Session $session):?string
    {
        $return = null;
        $route = $this->route();

        if($value === true)
        $value = array_keys($route::timeout());

        if((is_string($value) || is_array($value)))
        {
            $value = (array) $value;

            if(!empty($value))
            {
                foreach ($value as $v)
                {
                    if($route::timeoutGet($v) !== null && $route::isTimedOut($v))
                    {
                        $return = $v;
                        break;
                    }
                }
            }
        }

        return $return;
    }


    // schemeHost
    // retourne le schemeHost pour la routeRequest
    // si different est true, retourne seulement le schemeHost si différent de la requête de l'objet
    final public function schemeHost(bool $different=false):?string
    {
        $return = $this->route()::schemeHost();

        if($different === true)
        {
            $request = $this->request();
            $requestSchemeHost = $request->schemeHost();

            if($requestSchemeHost === $return)
            $return = null;
        }

        return $return;
    }


    // uri
    // prépare un des chemins d'une route request en vue d'une génération uri
    // la variable lang est obligatoire et filtre le tableau de chemin avec seulement les paths compatibles pour la langue
    public function uri(string $lang,?array $option=null):?string
    {
        $return = null;
        $path = $this->routePath($lang);
        $option = Base\Arr::plus($option,['schemeHost'=>true]);

        if(is_string($path))
        $return = $this->uriPrepare($path,$lang,$option);

        return $return;
    }


    // uriPrepare
    // ajoute la langue à l'uri si le chemin ne contient pas d'extension
    // la langue n'est pas ajouté si le uri est un chemin vide /
    // permet d'ajouter les query à conserver, tel que défini dans route/query si option query est true
    // ou si option query est un array, ajoute la query à l'array
    // si schemeHost est true, ajoute le schemeHost de la route si différent de la requête de l'objet
    final protected function uriPrepare(string $return,?string $lang=null,?array $option=null)
    {
        $request = $this->request();
        $route = $this->route();
        $option = Base\Arr::plus(['query'=>true,'schemeHost'=>false],$option);

        if(is_string($lang))
        {
            $strlen = strlen($return);
            $return = Base\Path::str($return);

            if($strlen > 0 && !Base\Path::hasExtension($return))
            $return = Base\Path::addLang($lang,$return);
        }

        $routeQuery = $route::getConfig('query');
        if(!empty($routeQuery) && $option['query'] === true)
        {
            $requestQuery = $request->queryArray();

            if(!empty($requestQuery))
            {
                $query = Base\Arr::gets($routeQuery,$requestQuery);

                if(!empty($query))
                $return = Base\Uri::changeQuery($query,$return);
            }
        }

        elseif(is_array($option['query']))
        $return = Base\Uri::changeQuery($option['query'],$return);

        if($option['schemeHost'] === true)
        {
            $schemeHost = $this->schemeHost(true);

            if(!empty($schemeHost))
            $return = Base\Uri::combine($schemeHost,$return);
        }

        return $return;
    }


    // uriOutput
    // génère une uri via la méthode base/uri output
    // l'uri généré peut être relative ou absolut
    // le schemeHost utilisé est celui de la route de l'objet
    final public function uriOutput(string $lang,?array $option=null):?string
    {
        $return = null;
        $path = $this->uri($lang,$option);

        if(is_string($path))
        {
            $schemeHost = $option['schemeHost'] ?? $this->schemeHost(true);
            $option = Base\Arr::plus($option,['schemeHost'=>$schemeHost]);
            $return = Base\Uri::output($path,$option);
        }

        return $return;
    }


    // uriRelative
    // génère une uri via la méthode base/uri relative
    // l'uri est toujours relative
    final public function uriRelative(string $lang,?array $option=null):?string
    {
        $return = null;
        $path = $this->uri($lang,$option);

        if(is_string($path))
        $return = Base\Uri::relative($path,$option);

        return $return;
    }


    // uriAbsolute
    // génère une uri via la méthode base/uri absolute
    // l'uri est toujours absolut
    // le schemeHost utilisé est celui de la route de l'objet
    final public function uriAbsolute(string $lang,?array $option=null):?string
    {
        $return = null;
        $path = $this->uri($lang,$option);

        if(is_string($path))
        {
            $schemeHost = $option['schemeHost'] ?? $this->schemeHost();
            $return = Base\Uri::absolute($path,$schemeHost,$option);
        }

        return $return;
    }


    // allowed
    // retourne vrai si la route et le rôle match
    // renvoie à role/validate
    final public static function allowed($value,Main\Role $role):bool
    {
        $return = false;

        if($value === null || $value === false)
        $return = true;

        else
        $return = $role->validate($value);

        return $return;
    }


    // pathFromRoute
    // retourne le path de la route
    // si une lang est fourni, retourne le path compatible avec la langue
    // si pathMatch est fourni, on va retourner le chemin exact si existant, mais ne supporte pas les segments
    final public static function pathFromRoute(string $route,?string $lang=null,bool $null=false,?string $pathMatch=null)
    {
        $return = false;
        $paths = $route::paths();

        if(is_string($lang) && array_key_exists($lang,$paths))
        $paths = (array) $paths[$lang];

        if(is_string($pathMatch) && in_array($pathMatch,$paths,true))
        $return = $pathMatch;

        else
        {
            foreach ($paths as $key => $value)
            {
                if(is_numeric($key))
                {
                    if(is_string($value) || ($value === null && $null === true))
                    {
                        $return = $value;
                        break;
                    }
                }
            }
        }

        return $return;
    }
}
?>