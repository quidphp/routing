<?php
declare(strict_types=1);
namespace Quid\Routing;
use Quid\Main;
use Quid\Base;

// _segment
trait _segment
{
	// routeSegmentRequest
	// retourne l'objet routeSegmentRequest
	// envoie une exception si la route n'a pas de segment
	public function routeSegmentRequest():RouteSegmentRequest 
	{
		$return = $this->routeRequest();
		
		if(!$return instanceof RouteSegmentRequest)
		static::throw('routeHasNoSegment');
		
		return $return;
	}
	
	
	// initSegment
	// init seulement les segments de la route
	// comme isValidSegment mais retourne la route plutôt qu'un booléean
	public function initSegment(bool $exception=false):self 
	{
		$this->isValidSegment($exception);
		
		return $this;
	}
	
	
	// isValidSegment
	// retourne vrai si la requête et les segments de route match
	public function isValidSegment(bool $exception=false):bool 
	{
		return $this->routeSegmentRequest()->isValidSegment(static::session(),$exception);
	}


	// checkValidSegment
	// envoie une exception si la requête et la route ne passent pas le test segment
	// si valid est false, le test n'est pas lancé et utilise le résultat courant
	public function checkValidSegment(bool $valid=true):self 
	{
		if($valid === true)
		$this->isValidSegment(true);
		
		else
		$this->routeSegmentRequest()->checkValidSegment();
		
		return $this;
	}
	
	
	// segment
	// retourne le tableau des data de segment, ou une valeur de segment si la clé est fournie en argument
	// si make est false ou segment n'a pas été validé retourne à partir de requestSegment
	// si make est true retourne le résultat de makeRequestSegment
	// peut aussi retourner un segment via index si un int est fourni
	// envoie aussi une exception si le segment demandé n'existe pas
	public function segment($key=null,?bool $make=null)
	{
		$return = null;
		$routeRequest = $this->routeSegmentRequest();
		$valid = $routeRequest->valid('segment');
		
		if($make === true)
		$return = $routeRequest->makeRequestSegment();
		
		elseif($make === false || $valid === false)
		$return = $routeRequest->requestSegment();
		
		else
		$return = $routeRequest->segment();

		if(is_array($return))
		{
			if(is_scalar($key))
			{
				if(is_string($key) && array_key_exists($key,$return))
				$return = $return[$key];
				
				elseif(is_int($key) && array_key_exists($key,($return = array_values($return))))
				$return = $return[$key];
				
				else
				static::throw('doesNotExist',$key);
			}
			
			elseif(is_array($key))
			{
				$return = Base\Arr::gets($key,$return);
				
				if(count($return) !== count($key))
				static::throw('doesNotExist');
			}
		}
		
		return $return;
	}
	
	
	// hasSegment
	// retourne vrai si l'objet contient le ou les segments de requête données en argument
	public function hasSegment(string ...$values):bool 
	{
		return $this->routeSegmentRequest()->hasRequestSegment(...$values);
	}

	
	// checkSegment
	// envoie une exception si un des segments de requête n'existent pas
	public function checkSegment(string ...$values):bool 
	{
		return $this->routeSegmentRequest()->checkRequestSegment(...$values);
	}
	
	
	// changeSegment
	// permet de changer la valeur d'un des segments de requête de l'objet
	// un objet changé vide le tableau valid et la propriété segment de routeRequestSegment
	// l'objet route et routeSegmentRequest sont cloné
	public function changeSegment(string $key,$value):self 
	{
		return $this->changeSegments([$key=>$value]);
	}


	// changeSegments
	// permet de changer la valeur de plusieurs segments de requête de l'objet
	// un objet changé vide le tableau valid et la propriété segment de routeRequestSegment
	// l'objet route et routeSegmentRequest sont cloné
	public function changeSegments(array $values):self 
	{
		$return = static::make($this);
		$return->routeSegmentRequest()->changeRequestSegments($values);
		
		return $return;
	}
	
	
	// keepSegments
	// retourne un nouvel objet route en conservant certains segments et en ramenenant les autres à leurs valeurs par défaut
	// un objet changé vide le tableau valid et la propriété segment de routeRequestSegment
	// l'objet route et routeSegmentRequest sont cloné
	public function keepSegments(string ...$values):self
	{
		$return = static::make($this);
		$return->routeSegmentRequest()->keepRequestSegments(...$values);
		
		return $return;
	}
	

	// isSegmentClass
	// retourne vrai si un chemin contient un segment
	public static function isSegmentClass():bool
	{
		$return = false;
		
		foreach (static::paths() as $path) 
		{
			if(is_string($path) && strpos($path,'[') !== false)
			{
				$return = true;
				break;
			}
		}
		
		return $return;
	}
	
	
	// checkSegmentClass
	// envoie une exception si ce n'est pas une classe avec segment
	public static function checkSegmentClass():bool 
	{
		$return = static::isSegmentClass();
		
		if($return === false)
		static::throw();
		
		return $return;
	}
	
	
	// makeRouteRequest
	// créer l'objet routeRequest pour la route
	public static function makeRouteRequest($request=null):RouteRequest 
	{
		$return = null;
		
		if(static::isSegmentClass())
		{
			$lang = static::session()->lang();
			$return = RouteSegmentRequest::newOverload(static::class,$request,$lang);
		}
		
		else
		$return = RouteRequest::newOverload(static::class,$request);

		return $return;
	}


	// allSegment
	// retourne tous les combinaisons de segments possible pour la route
	// par défaut retourne un tableau vide
	// n'est pas abstraite
	public static function allSegment()
	{
		return [];
	}
	
	
	// callableSegment
	// retourne la callable à utiliser pour le segment
	// envoie une exception si la callable n'existe pas
	public static function callableSegment(string $key):callable 
	{
		$return = null;
		$segments = static::$config['segment'];
		$callable = null;
		
		if(is_array($segments) && array_key_exists($key,$segments))
		$callable = $segments[$key];
		
		if(is_string($callable))
		$callable = [static::class,$callable];
		
		if(static::classIsCallable($callable))
		$return = $callable;
		
		else
		static::throw('segmentMethodNotFound',$key);
		
		return $return;
	}
	
	
	// getDefaultSegment
	// retourne le caractère de segment par défaut
	// pourrait être null, à ce moment pas défaut de segment
	public static function getDefaultSegment():?string 
	{
		return static::$config['defaultSegment'] ?? null;
	}
	
	
	// getReplaceSegment
	// retourne le pattern utilisé pour faire un remplacement sur un segment
	// pourrait être null, à ce moment pas de possibilité de remplace dans makeSegment
	public static function getReplaceSegment():?string 
	{
		return static::$config['replaceSegment'] ?? null;
	}
}
?>