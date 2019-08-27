<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Main;
use Quid\Base;

// routeRequest
class RouteRequest extends Main\Root
{
	// config
	public static $config = [
		'match'=>['ssl','ajax','host','method','query','post','genuine','header','lang','ip','browser','session','role','csrf','captcha','timeout'], // clé valable pour match
		'verify'=>['query','post','genuine','header','lang','ip','browser','session','role','csrf','captcha','timeout'] // clé valable pour verify
	];


	// dynamique
	protected $route = null; // nom de la classe de la route
	protected $request = null; // copie ou référence de la requête
	protected $valid = []; // garde en mémoire les tests passés, comme match et verify
	protected $fallback = null; // garde en mémoire la raison que la route ira en fallback


	// construct
	// construit l'objet routeRequest et lance le processus de match
	// si request est vide prend la requête courante
	// session doit être inclu pour faire les de role, session et csrf
	public function __construct(string $route,$request=null)
	{
		$this->setRoute($route);
		$this->setRequest($request);

		return;
	}


	// toString
	// retourne le nom de la route
	public function __toString():string
	{
		return static::route();
	}


	// reset
	// reset les vérifications de l'objet à l'état initial
	// méthode protégé
	protected function reset():self
	{
		$this->valid = [];
		$this->fallback = null;

		return $this;
	}


	// isValid
	// retourne vrai si la route et la requête match et verify
	public function isValid(Main\Session $session,bool $exception=false):bool
	{
		return ($this->isValidMatch($session,$exception) && $this->isValidVerify($session,$exception))? true:false;
	}


	// checkValid
	// envoie une exception si la route et la requête n'ont pas passés les tests match et verify
	public function checkValid():bool
	{
		$return = ($this->valid('match') && $this->valid('verify'))? true:false;

		if($return === false)
		static::throw();

		return $return;
	}


	// isValidMatch
	// retourne vrai si la route et la requête match
	// si la propriété match et null, lance match
	public function isValidMatch(Main\Session $session,bool $exception=false):bool
	{
		$return = false;

		if(!$this->valid('match'))
		$this->validateMatch($session,$exception);

		$return = $this->valid('match');

		return $return;
	}


	// checkValidMatch
	// envoie une exception si la route et la requête n'ont pas passés le test match
	public function checkValidMatch():self
	{
		if(!$this->valid('match'))
		static::throw();

		return $this;
	}


	// isValidVerify
	// retourne vrai si la route et la requête passe le test verify
	// si la propriété verify et null, lance verify
	public function isValidVerify(Main\Session $session,bool $exception=false):bool
	{
		$return = false;

		if(!$this->valid('verify'))
		$this->validateVerify($session,$exception);

		$return = $this->valid('verify');

		return $return;
	}


	// checkValidVerify
	// envoie une exception si la route et la requête n'ont pas passés le test verify
	public function checkValidVerify():self
	{
		if(!$this->valid('verify'))
		static::throw();

		return $this;
	}


	// isRequestInst
	// retourne vrai si la requête de l'objet routeRequest et la requête courante, storé dans core/request inst
	public function isRequestInst():bool
	{
		return ($this->request() === Main\Request::instSafe())? true:false;
	}


	// valid
	// retourne la propriété valid, qui contient les tests passés par l'objet
	// possible de retourner seulement le résultat d'un test
	public function valid(?string $key=null)
	{
		$return = $this->valid;

		if(is_string($key))
		$return = (array_key_exists($key,$this->valid) && is_bool($this->valid[$key]))? $this->valid[$key]:false;

		return $return;
	}


	// fallback
	// retourne la raison du fallback
	// peut être null, une string ou un array
	public function fallback()
	{
		return $this->fallback;
	}


	// setFallback
	// change la valeur de la propriété fallback de l'objet
	// met un tableau si la clé est timeout et value est string
	// méthode protégé
	protected function setFallback(string $key,$value,Main\Session $session):self
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

		return $this;
	}


	// route
	// retourne le nom de classe de la route
	public function route():string
	{
		return $this->route;
	}


	// setRoute
	// change le nom de classe de la route
	// la classe doit être une sous-classe de route
	// lance la méthode reset
	public function setRoute(string $route):self
	{
		if(is_subclass_of($route,Route::class,true))
		{
			$this->reset();
			$this->route = $route;
		}

		else
		static::throw();

		return $this;
	}


	// request
	// retourne la requête de l'objet
	public function request():Main\Request
	{
		return $this->request;
	}


	// setRequest
	// change la requête de l'objet
	// si requête est null, prend la requête read-only de inst ou crée une nouvelle requête live
	// lance la méthode reset
	public function setRequest($request=null):self
	{
		if(is_string($request) || is_array($request))
		$request = Main\Request::newOverload($request);

		elseif($request === null || (is_object($request) && !$request instanceof Main\Request))
		$request = Main\Request::instSafe() ?? Main\Request::live();

		if($request instanceof Main\Request)
		{
			$this->reset();
			$this->request = $request;
		}

		else
		static::throw();

		return $this;
	}


	// validateMatch
	// lance le processus de match entre la route et la request
	// si exception est true, lance une exception avec le nom de la clé où le match bloque
	public function validateMatch(Main\Session $session,bool $exception=false):bool
	{
		$return = false;
		$lang = $session->lang();
		$route = $this->route();
		$match = $route::$config['match'] ?? [];

		$path = $route::path($lang,true);
		$emptyPath = $route::path(null,true);
		$go = false;

		if((is_string($path) || $path === null) && $this->path($path))
		$go = true;

		elseif((is_string($emptyPath) || $emptyPath === null) && $this->path($emptyPath))
		$go = true;

		if($go === true)
		{
			foreach ($match as $key => $value)
			{
				if(is_string($key) && in_array($key,static::$config['match'],true))
				{
					$return = ($value === null)? true:$this->$key($value,$session);

					if($return === false)
					{
						if($exception === true)
						static::throw($key,$value);

						break;
					}
				}

				else
				static::throw($key);
			}
		}

		return $this->valid['match'] = $return;
	}


	// validateVerify
	// lance le processus verify entre la route et la request
	// si exception est true, lance une exception avec le nom de la clé où le match bloque
	public function validateVerify(Main\Session $session,bool $exception=false):bool
	{
		$return = false;
		$route = $this->route();
		$verify = $route::$config['verify'] ?? [];

		foreach ($verify as $key => $value)
		{
			if(is_string($key) && in_array($key,static::$config['verify'],true))
			{
				$return = ($value === null)? true:$this->$key($value,$session);

				if($return === false)
				{
					$this->setFallback($key,$value,$session);

					if($exception === true)
					static::throw($key,$value);

					break;
				}
			}

			else
			static::throw($key);
		}

		return $this->valid['verify'] = $return;
	}


	// validateArray
	// validate une valeur dans un array
	// utiliser pour valider headers, query et post
	// méthode protégé, peut être étendu
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

			if($value === $match)
			$return = true;
		}

		return $return;
	}


	// ssl
	// retourne vrai si la requête et la route match ssl
	public function ssl($value):bool
	{
		$return = false;

		if($value === null)
		$return = true;

		elseif(is_bool($value))
		{
			if($value === $this->request()->isSsl())
			$return = true;
		}

		return $return;
	}


	// ajax
	// retourne vrai si la requête et la route match ajax
	public function ajax($value):bool
	{
		$return = false;

		if($value === null)
		$return = true;

		elseif(is_bool($value))
		{
			if($value === $this->request()->isAjax())
			$return = true;
		}

		return $return;
	}


	// host
	// retourne vrai si la requête et la route match host
	public function host($value):bool
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

				if(is_array($value) && !empty($value) && in_array($host,$value,true))
				$return = true;
			}
		}

		return $return;
	}


	// method
	// retourne vrai si la requête et la route match method
	public function method($value):bool
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
					$value = array_map('strtolower',$value);

					if(in_array($method,$value,true))
					$return = true;
				}
			}
		}

		return $return;
	}


	// header
	// retourne vrai si la requête et la route match header de requête
	// insensible à la case
	public function header($value):bool
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
	public function lang($value):bool
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

				if(is_array($value) && !empty($value) && in_array($lang,$value,true))
				$return = true;
			}
		}

		return $return;
	}


	// ip
	// retourne vrai si la requête et la route match ip
	public function ip($value):bool
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

				if(!empty($ip) && Base\Ip::allowed($ip,$value))
				$return = true;
			}
		}

		return $return;
	}


	// browser
	// retourne vrai si la requête et la route match browser
	public function browser($value):bool
	{
		$return = false;

		if($value === null || $value === false)
		$return = true;

		else
		{
			$browserName = $this->request()->browserName();

			if(!empty($browserName))
			{
				if(is_string($value))
				$value = [$value];

				if(is_array($value) && !empty($value) && Base\Arr::in($browserName,$value,false))
				$return = true;
			}
		}

		return $return;
	}


	// query
	// retourne vrai si la requête et la route match query
	public function query($value):bool
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
	public function post($value):bool
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
	public function genuine($value):bool
	{
		$return = false;

		if($value === null || $value === false)
		$return = true;

		else
		$return = $this->request()->hasEmptyGenuine();

		return $return;
	}


	// role
	// retourne vrai si la route et le rôle de la session match
	public function role($value,Main\Session $session):bool
	{
		return static::allowed($value,$session->role());
	}


	// session
	// retourne vrai si la route et la session match
	// la validation se fait en utilisation des noms de méthode sur l'objet de session
	public function session($value,Main\Session $session):bool
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
	public function csrf($value,?Main\Session $session=null):bool
	{
		$return = false;

		if($value === null || $value === false)
		$return = true;

		elseif($value === true && !empty($session))
		{
			$requestCsrf = $this->request()->csrf();

			if($session->isCsrf($requestCsrf))
			$return = true;
		}

		return $return;
	}


	// captcha
	// retourne vrai si la chaîne captcha de la requête et de la session match
	public function captcha($value,?Main\Session $session=null):bool
	{
		$return = false;

		if($value === null || $value === false)
		$return = true;

		elseif($value === true && !empty($session))
		{
			$requestCaptcha = $this->request()->captcha();

			if($session->isCaptcha($requestCaptcha))
			$return = true;
		}

		return $return;
	}


	// timeout
	// retourne vrai si tous les timeouts définis sont valides (et non pas en timeout)
	// un nom de timeout non existant n'est pas timedOut, donc retourne true
	// si timeout est true, prend tous les timeouts définis
	public function timeout($value,?Main\Session $session=null):bool
	{
		$return = false;

		if($value === null || $value === false)
		$return = true;

		elseif(!empty($session))
		{
			$timedOut = $this->timedOut($value,$session);

			if($timedOut === null)
			$return = true;
		}

		return $return;
	}


	// timedOut
	// retourne le nom du timeout qui est timedOut
	// si timeout est true, prend tous les timeouts définis
	// méthode protégé
	protected function timedOut($value,Main\Session $session):?string
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
	public function schemeHost(bool $different=false):?string
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
		$route = $this->route();
		$path = $route::path($lang);
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
	// méthode protégé
	protected function uriPrepare(string $return,?string $lang=null,?array $option=null)
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

		$routeQuery = $route::$config['query'] ?? null;
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
	public function uriOutput(string $lang,?array $option=null):?string
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
	public function uriRelative(string $lang,?array $option=null):?string
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
	public function uriAbsolute(string $lang,?array $option=null):?string
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
	public static function allowed($value,Main\Role $role):bool
	{
		$return = false;

		if($value === null || $value === false)
		$return = true;

		else
		$return = $role::validate($value);

		return $return;
	}
}
?>