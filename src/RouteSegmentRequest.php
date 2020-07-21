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

// routeSegmentRequest
// class that analyzes if a request matches a route with segment (non-static value)
class RouteSegmentRequest extends RouteRequest
{
    // config
    protected static array $config = [];


    // dynamique
    protected string $langCode; // garde en mémoire la lang de la session
    protected ?int $type = null; // défini si la une requête a été fourni ou une valeur et la requête est celle de inst
    protected ?array $routeSegment = null; // garde en mémoire les segments requis par le path de la route
    protected ?array $requestSegment = null; // garde en mémoire les segments fournis par la request
    protected ?array $make = null; // garde en cache le résultat de makeRequestSegment
    protected ?array $segment = null; // garde en mémoire les données de segment, le retour de validateSegment


    // construct
    // construit l'objet routeRequest et lance le processus de match
    // si request est vide prend la requête courante
    // le code de lang doit être inclus pour aller chercher les path dans route
    final public function __construct(Route $route,$request=null,string $lang)
    {
        $this->setLangCode($lang);
        $this->setRoute($route);
        $this->setRequest($request);
    }


    // reset
    // reset les vérifications de l'objet à l'état initial
    final protected function reset():void
    {
        parent::reset();
        $this->segment = null;
        $this->make = null;
    }


    // isValid
    // retourne vrai si la route et la requête match et segment
    final public function isValid(Main\Session $session,bool $exception=false):bool
    {
        return $this->isValidMatch($session,$exception) && $this->isValidSegment($session,$exception);
    }


    // checkValid
    // envoie une exception si la route et la requête n'ont pas passés les tests match et segment
    final public function checkValid():bool
    {
        $return = ($this->valid('match') && $this->valid('segment'));

        if($return === false)
        static::throw();

        return $return;
    }


    // setLangCode
    // conserve en mémoire la langue de la session
    final protected function setLangCode(string $lang):void
    {
        $this->langCode = $lang;
    }


    // langCode
    // retourne la langue de la session
    final public function langCode():string
    {
        return $this->langCode;
    }


    // setRoute
    // change le nom de classe de la route
    // la classe doit être une sous-classe de routeSegment
    // lance la méthode reset
    final public function setRoute(Route $route):self
    {
        parent::setRoute($route);
        $lang = $this->langCode();
        $path = $this->routePath($lang);
        $segment = null;

        if(is_string($path))
        $segment = Base\Segment::get(null,$path);

        if(is_array($segment) && !empty($segment))
        $this->routeSegment = $segment;

        else
        static::throw('invalidSegment',$this->route);

        return $this;
    }


    // routeSegment
    // retourne le tableau des segments du chemin
    final public function routeSegment():array
    {
        return $this->routeSegment;
    }


    // setRequest
    // créer la requête de l'objet
    // si request est routeSegment, c'est un deep clone, mais request n'est pas cloné
    // si request est objet request, utilise la requête
    // sinon c'est une valeur, utilise la requête courante et envoie à parseRequestSegmentFromValue
    // lance la méthode reset
    final public function setRequest($request=null):self
    {
        $this->reset();

        if($request instanceof Main\Request)
        {
            $this->request = $request;
            $this->parseRequestSegmentFromRequest();
        }

        else
        {
            $this->request = Main\Request::instSafe() ?? Main\Request::live();
            $this->parseRequestSegmentFromValue($request);
        }

        static::checkClass($this->request,Main\Request::class);
        return $this;
    }


    // parseRequestSegmentFromRequest
    // parse les segments à partir de la requête
    final protected function parseRequestSegmentFromRequest():void
    {
        $routeSegment = $this->routeSegment();

        if(is_array($routeSegment) && !empty($routeSegment))
        {
            $this->type = 1;

            $catchAll = $this->isRouteCatchAll();
            $request = $this->request();
            $requestPath = $request->pathMatch();
            $lang = $this->langCode();
            $routePath = $this->routePath($lang);
            $requestSegment = Base\Path::getSegments($routePath,$requestPath);

            if($catchAll === true && $requestSegment === null)
            $requestSegment = $this->parseRequestSegmentFromRequestCatchAll();

            if(is_array($requestSegment) && !empty($requestSegment) && count($requestSegment) === count($routeSegment))
            $this->requestSegment = Base\Arr::cast($requestSegment);
        }

        else
        static::throw('invalidRouteSegment');
    }


    // parseRequestSegmentFromRequestCatchAll
    // parse les segments à partir de la requête
    // support pour catchAll avec un seul segment, sinon exception
    final protected function parseRequestSegmentFromRequestCatchAll():array
    {
        $return = [];
        $routeSegment = $this->routeSegment();
        $langCode = $this->langCode();
        $path = $this->routePath($langCode);

        if(is_string($path) && strlen($path))
        {
            $request = $this->request();
            $requestPath = $request->pathMatch();
            $segment = Base\Arr::valueLast($routeSegment);

            if(is_string($segment) && strlen($segment))
            {
                $path = Base\Path::spliceLast($path);
                $path = Base\Path::stripWrap($path,false,false);
                $array = Base\Path::arr($path);
                $segmentPath = $requestPath;

                if(!empty($array))
                {
                    $last = (Base\Arr::keyLast($array) + 1);
                    $segmentPath = Base\Path::splice(0,$last,$requestPath);
                    $segmentPath = Base\Path::stripWrap($segmentPath,false,false);
                }

                if(strlen($segmentPath))
                {
                    $requestPath = substr($requestPath,0,-strlen($segmentPath));
                    $requestPath = Base\Path::stripWrap($requestPath,false,false);

                    $segments = Base\Path::getSegments($path,$requestPath);
                    if(!empty($segments))
                    $return = $segments;

                    $return[$segment] = $segmentPath;
                }
            }
        }

        return $return;
    }


    // parseRequestSegmentFromValue
    // parse les segments de requête à partir de la valeur donnée en argument
    // pour la valeur donnée en argument, possible de donner un tableau via clé ou index
    final protected function parseRequestSegmentFromValue($value=null):void
    {
        $routeSegment = $this->routeSegment();

        if(is_array($routeSegment) && !empty($routeSegment))
        {
            $this->type = 2;

            foreach ($routeSegment as $i => $k)
            {
                if(is_array($value))
                {
                    if(array_key_exists($k,$value))
                    $v = $value[$k];

                    elseif(array_key_exists($i,$value))
                    $v = $value[$i];

                    else
                    $v = null;
                }

                else
                $v = $value;

                $this->requestSegment[$k] = $v;
            }
        }

        else
        static::throw('invalidRouteSegment');
    }


    // isRouteCatchAll
    // retourne vrai si la route est catchAll
    final public function isRouteCatchAll():bool
    {
        $route = $this->route();
        $catchAll = $route::getConfig('catchAll') ?? false;
        $routeSegment = $this->routeSegment();
        return $catchAll === true && !empty($routeSegment);
    }


    // isSegmentParsedFromValue
    // retourne vrai si les segments ont été parsed à partir d'une valeur, et que la request est celle de inst
    final public function isSegmentParsedFromValue():bool
    {
        return $this->type === 2;
    }


    // isRouteRequestCompatible
    // retourne vrai si les segments de route et request sont compatibles
    // si la route est catch all, retourne toujours true
    final public function isRouteRequestCompatible():bool
    {
        $return = false;

        if($this->isRouteCatchAll())
        $return = true;

        else
        {
            $routeSegment = $this->routeSegment();
            $requestSegment = $this->requestSegment;
            $return = (is_array($requestSegment) && !empty($requestSegment) && count($requestSegment) === count($routeSegment));
        }

        return $return;
    }


    // requestSegment
    // retourne retourne les segments de la requête, sous une forme keyValue
    // envoie une exception si la propriété requestSegment est toujours null ou si elle ne match pas avec les segments de la route
    final public function requestSegment():array
    {
        if(!$this->isRouteRequestCompatible())
        static::throw($this->route(),'segmentMismatch','requires',...$this->routeSegment());

        return $this->requestSegment;
    }


    // hasRequestSegment
    // retourne vrai si l'objet contient le ou les segments de requête données en argument
    final public function hasRequestSegment(string ...$values):bool
    {
        $segment = $this->requestSegment();

        return Base\Arr::every($values,fn($value) => array_key_exists($value,$segment));
    }


    // checkRequestSegment
    // envoie une exception si un des segments de requête n'existent pas
    final public function checkRequestSegment(string ...$values):bool
    {
        $return = $this->hasRequestSegment(...$values);

        if($return === false)
        static::throw();

        return $return;
    }


    // changeRequestSegment
    // permet de changer la valeur d'un des segments de requête de la classe
    // un objet changé vide le tableau valid et la propriété segment
    final public function changeRequestSegment(string $key,$value):self
    {
        return $this->changeRequestSegments([$key=>$value]);
    }


    // changeRequestSegments
    // permet de changer la valeur de plusieurs segments de requête de la classe
    // un objet changé vide le tableau valid et la propriété segment
    // valeur false est remplacé par defautSegment
    // valeur true est remplacé par replaceSegment
    final public function changeRequestSegments(array $values):self
    {
        $this->checkRequestSegment(...array_keys($values));
        $this->reset();
        $route = $this->route();
        $defaultSegment = $route::getDefaultSegment();
        $replaceSegment = $route::getReplaceSegment();

        $values = Base\Obj::cast($values);
        foreach ($values as $key => $value)
        {
            if($value === false && is_string($defaultSegment))
            $values[$key] = $defaultSegment;

            elseif($value === true && is_string($replaceSegment))
            $values[$key] = $replaceSegment;
        }

        $this->requestSegment = Base\Arr::replace($this->requestSegment,$values);

        return $this;
    }


    // keepRequestSegments
    // garde les segments de requêtes spécifiés, les autres sont mis à null
    // un objet changé vide le tableau valid et la propriété segment
    final public function keepRequestSegments(string ...$values):self
    {
        $this->checkRequestSegment(...$values);
        $this->reset();

        foreach ($this->requestSegment as $key => $value)
        {
            if(!in_array($key,$values,true))
            $this->requestSegment[$key] = null;
        }

        return $this;
    }


    // makeRequestSegment
    // passe les segments de requêtes dans la méthode makeSegment de la route
    // si la méthode makeSegment retourne false, utilise le defaultSegment si disponible
    // si la méthode makeSegment retourne true, utilise replaceSegment si disponible
    // retourne un tableau, utilisé par la méthode uri
    // le résultat de cette méthode est gardé en cache dans la propriété makeRequestSegment
    // l'argument lang est maintenant envoyé dans le callback (par exemple pour permettre à slug de changer de lang)
    final public function makeRequestSegment(?string $lang=null):array
    {
        $return = $this->make;
        $langChange = ($lang !== $this->langCode());

        if(empty($return) || $langChange === true)
        {
            $route = $this->route();
            $defaultSegment = $route::getDefaultSegment();
            $replaceSegment = $route::getReplaceSegment();
            $requestSegment = $this->requestSegment();

            foreach ($requestSegment as $key => $value)
            {
                if(is_string($defaultSegment) && ($value === $defaultSegment || $value === false))
                $v = $defaultSegment;

                elseif(is_string($replaceSegment) && ($value === $replaceSegment || $value === true))
                $v = $replaceSegment;

                else
                {
                    $callable = $this->routeCallableSegment($key);
                    $v = $callable('make',$value,$requestSegment,$lang);

                    if($v === false && is_string($defaultSegment))
                    $v = $defaultSegment;

                    elseif($v === true && is_string($replaceSegment))
                    $v = $replaceSegment;

                    elseif(is_object($v))
                    $v = Base\Obj::cast($v);

                    if(is_numeric($v) && !is_string($v))
                    $v = (string) $v;
                }

                if(!is_string($v))
                static::throw($route,$key,'mustReturnString');

                else
                $return[$key] = $v;
            }

            if($langChange === false)
            $this->make = $return;
        }

        if(empty($return))
        static::throw();

        return $return;
    }


    // isValidSegment
    // retourne vrai si la route et la requête passe le test segment
    // si la propriété segment et null, lance segment
    final public function isValidSegment(Main\Session $session,bool $exception=false):bool
    {
        $return = false;

        if(!$this->valid('segment'))
        $this->validateSegment($session,$exception);

        $return = $this->valid('segment');

        return $return;
    }


    // checkValidSegment
    // envoie une exception si la route et la requête ne passe pas le test segment
    final public function checkValidSegment():self
    {
        if(!$this->valid('segment'))
        static::throw();

        return $this;
    }


    // validateSegment
    // lance le processus segment entre la route et la request
    // peut appeler validateDefaultSegment si la la valeur est null ou defaultSegment, sinon appele validateSegment
    // si exception est true, lance une exception avec le nom de la clé où le match bloque
    // validateSegment et validateDefaultSegment bloque seulement si la valeur de retour est false
    final public function validateSegment(Main\Session $session,bool $exception=false):bool
    {
        $return = true;
        $route = $this->route();
        $loop = $this->orderRouteSegment();
        $keyValue = $loop;
        $defaultSegment = $route::getDefaultSegment();
        $this->segment = [];

        foreach ($loop as $key => $value)
        {
            $callable = $this->routeCallableSegment($key);
            $value = (is_string($value) && $value === $defaultSegment)? null:$value;

            $v = $callable('match',$value,$keyValue);

            if($v === false)
            {
                $return = false;
                $this->fallback = ['segment',$key];
                $this->segment[$key] = false;

                if($exception === true)
                static::throw($route,$key,$value);
            }

            else
            {
                $keyValue[$key] = $v;
                $this->segment[$key] = $v;
            }
        }

        return $this->valid['segment'] = $return;
    }


    // orderRouteSegment
    // méthode qui permet d'ordonner les segments de la route pour le loop de validateSegment
    final protected function orderRouteSegment():array
    {
        $return = [];
        $requestSegment = $this->requestSegment();
        $route = $this->route();
        $segments = Base\Arr::clean($route::getConfig('segment') ?? []);
        $segmentsKeys = array_keys($segments);

        if(is_array($segments) && Base\Arr::keysAre($segmentsKeys,$requestSegment))
        {
            foreach ($segments as $key => $value)
            {
                $return[$key] = $requestSegment[$key];
            }
        }

        else
        static::throw('incompatibleSegments',$segmentsKeys,array_keys($requestSegment));

        return $return;
    }


    // validateArray
    // validate une valeur dans un array
    // utiliser pour valider headers, query et post
    // les segments sont remplacés à partir de requestSegment
    // supporte un tableau multidimensionnel
    final protected function validateArray($value,array $array):bool
    {
        $return = false;

        if(is_array($value))
        {
            $segment = $this->requestSegment();

            if(!empty($segment))
            {
                $value = Base\Segment::setsArray(null,$segment,$value);
                $value = Base\Arrs::cast($value);
            }
        }

        $return = Base\Validate::arr($value,$array);

        return $return;
    }


    // segment
    // retourne les segment validés
    final public function segment(?Main\Session $session=null,bool $exception=false):array
    {
        if(!empty($session))
        $this->isValidSegment($session,$exception);

        elseif($exception === true)
        $this->checkValidSegment();

        $return = $this->segment;

        if(empty($return))
        static::throw();

        return $return;
    }


    // path
    // retourne vrai si la requête et la route match le path, en tenant compte des segments
    // si la route est catch all, envoie à pathCatchAll
    final public function path(?string $value):bool
    {
        $return = false;

        if(is_string($value) && Base\Path::hasSegment($value))
        {
            if($this->isRouteCatchAll())
            $return = $this->pathCatchAll($value);

            else
            {
                $match = $this->request()->pathMatch();
                $value = Base\Path::stripStart($value);
                $return = (Base\Path::sameWithSegments($value,$match));
            }
        }

        else
        $return = parent::path($value);

        return $return;
    }


    // pathCatchAll
    // retourne vrai si la requête et la route match le path, en tenant compte des segments et que la route est catchAll
    // le chemin doit finir par le dernier segment de la route
    // tout le reste du chemin, sauf le dernier segment, doit valider
    final protected function pathCatchAll(string $value):bool
    {
        $return = false;
        $route = $this->route();
        $routeSegment = $this->routeSegment();
        $countSegment = count($routeSegment);

        if($countSegment > 0 && strlen($value))
        {
            $lastSegment = Base\Arr::valueLast($routeSegment);
            $match = $this->request()->pathMatch();

            if(is_string($lastSegment) && strlen($match) && Base\Str::isEnd("[$lastSegment]",$value))
            {
                $value = Base\Path::arr($value);
                $match = Base\Path::arr($match);

                if(count($match) >= count($value))
                {
                    $value = Base\Arr::spliceLast($value);
                    $match = Base\Arr::gets(array_keys($value),$match);

                    if(empty($value) && empty($match))
                    $return = true;

                    else
                    {
                        $value = Base\Path::str($value);
                        $value = Base\Path::stripWrap($value,false,false);
                        $match = Base\Path::str($match);
                        $match = Base\Path::stripWrap($match,false,false);
                        $return = (Base\Path::sameWithSegments($value,$match));
                    }
                }
            }
        }

        return $return;
    }


    // uri
    // prépare un des chemins d'une routeSegment request en vue d'une génération uri
    // la variable lang est obligatoire et filtre le tableau de chemin avec seulement les paths compatibles pour la langue
    // si une valeur est objet après makeSegment passe dans cast
    // envoie une exception si une valeur de segment n'est pas string ou numérique
    final public function uri(string $lang,?array $option=null):?string
    {
        $return = null;
        $path = $this->routePath($lang);
        $segment = $this->makeRequestSegment($lang);

        $path = Base\Segment::sets(null,$segment,$path);
        $option = Base\Arr::plus($option,['schemeHost'=>true]);

        if(is_string($path) && strlen($path))
        $return = $this->uriPrepare($path,$lang,$option);

        else
        static::throw('segmentReplaceFailed');

        return $return;
    }


    // routeCallableSegment
    // retourne la callable à utiliser pour le segment
    // envoie une exception si la callable n'existe pas
    final protected function routeCallableSegment(string $key):callable
    {
        $return = null;
        $route = $this->route();
        $segments = $route::getConfig('segment') ?? null;
        $callable = null;

        if(is_array($segments) && array_key_exists($key,$segments))
        $callable = $segments[$key];

        if(is_string($callable))
        $callable = [$route,$callable];

        if(static::isCallable($callable))
        $return = $callable;

        else
        static::throw('segmentMethodNotFound',$key);

        return $return;
    }
}
?>