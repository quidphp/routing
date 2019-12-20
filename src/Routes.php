<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 * Readme: https://github.com/quidphp/routing/blob/master/README.md
 */

namespace Quid\Routing;
use Quid\Base;
use Quid\Main;

// routes
// class for a collection of many untriggered routes
class Routes extends Main\Extender implements Main\Contract\Hierarchy
{
    // trait
    use Main\_inst;
    use Main\Map\_sort;


    // config
    public static $config = [
        'priorityIncrement'=>10, // incrémentation de la priorité lors de la méthode init
        'methodIgnore'=>'isIgnored',
        'subClass'=>Route::class
    ];


    // map
    protected static $allow = ['set','unset','remove','filter','sort','serialize','clone']; // méthodes permises
    protected static $sortDefault = 'priority'; // défini la méthode pour sort par défaut


    // dynamique
    protected $init = false;


    // onPrepareKey
    // prepare une clé pour les méthodes qui soumette une clé
    final protected function onPrepareKey($return)
    {
        if((is_string($return) && class_exists($return,false)) || is_object($return))
        {
            if(is_a($return,Route::class,true))
            $return = static::getKey($return);
        }

        return $return;
    }


    // isInit
    // retourne vrai si l'objet a été init
    final public function isInit():bool
    {
        return $this->init;
    }


    // type
    // retourne le type des routes
    final public function type():?string
    {
        $return = null;
        $first = $this->first();

        if(!empty($first))
        $return = $first::type();

        return $return;
    }


    // match
    // retourne toutes les routes qui match l'objet requête
    final public function match(Main\Request $request,bool $fallback=false,bool $debug=false):array
    {
        $return = [];

        foreach ($this->arr() as $key => $value)
        {
            $route = $value::matchOrFallbackDebug($request,$fallback,$debug);

            if(!empty($route))
            $return[$key] = $route;
        }

        return $return;
    }


    // route
    // retourne la première route qui match l'objet requête
    // possible de reprendre le loop au même endroit en fournissant le nom de la classe after
    final public function route(Main\Request $request,$after=null,bool $fallback=false,bool $debug=false):?Route
    {
        $return = null;

        if($after instanceof Route)
        $after = get_class($after);

        foreach ($this->arr() as $value)
        {
            if(is_string($after))
            {
                if($value === $after)
                $after = null;

                continue;
            }

            $route = $value::matchOrFallbackDebug($request,$fallback,$debug);

            if(!empty($route))
            {
                $return = $route;
                break;
            }
        }

        return $return;
    }


    // keyParent
    // retourne un tableau unidimensionnel avec le nom de la route comme clé et le nom du parent comme valeur
    // si aucun parent, la valeur est null
    final public function keyParent():array
    {
        $return = [];

        foreach ($this->arr() as $value)
        {
            $parent = $value::parent(true);
            $return[$value] = $parent;
        }

        return $return;
    }


    // hierarchy
    // retourne le tableau de la hiérarchie des éléments de l'objet
    // si existe est false, les parents de route non existantes sont conservés
    final public function hierarchy(bool $exists=true):array
    {
        return Base\Arrs::hierarchy($this->keyParent(),$exists);
    }


    // childsRecursive
    // retourne un tableau avec tous les enfants de l'élément de façon récursive
    // si existe est false, les parents de route non existantes sont conservés
    final public function childsRecursive($value,bool $exists=true):?array
    {
        $return = null;
        $value = $this->get($value);
        $hierarchy = $this->hierarchy($exists);

        if(!empty($value) && !empty($hierarchy))
        {
            $key = Base\Arrs::keyPath($value,$hierarchy);
            if($key !== null)
            $return = Base\Arrs::get($key,$hierarchy);
        }

        return $return;
    }


    // tops
    // retourne un objet des éléments n'ayant pas de parent
    // ne retourne pas les routes non existantes
    final public function tops():self
    {
        $return = new static();

        foreach ($this->arr() as $k => $v)
        {
            if($this->parent($v) === null)
            $return->add($v);
        }

        return $return;
    }


    // parent
    // retourne l'objet d'un élément parent ou null
    // ne retourne pas les routes non existantes
    final public function parent($value):?string
    {
        $return = null;

        if(!empty($value))
        {
            $parent = $value::parent(true);
            if(is_string($parent))
            $return = $this->get($parent);
        }

        return $return;
    }


    // top
    // retourne le plus haut parent d'un élément ou null
    // ne retourne pas les routes non existantes
    final public function top($value):?string
    {
        $return = null;
        $value = $this->get($value);

        if(!empty($value))
        {
            $target = $value;

            while ($parent = $this->parent($target))
            {
                $target = $parent;
            }

            if($target !== $value)
            $return = $target;
        }

        return $return;
    }


    // parents
    // retourne un objet avec tous les parents de l'élément
    // ne retourne pas les routes non existantes
    final public function parents($value):self
    {
        $return = new static();
        $value = $this->get($value);

        if(!empty($value))
        {
            while ($parent = $this->parent($value))
            {
                $return->add($parent);
                $value = $parent;
            }
        }

        return $return;
    }


    // breadcrumb
    // retourne un objet inversé de tous les parents de l'élément et l'objet courant
    // ne retourne pas les routes non existantes
    final public function breadcrumb($value):self
    {
        $return = $this->parents($value);

        if($return->isNotEmpty())
        $return = $return->reverse(true);

        $value = $this->get($value);
        if(!empty($value))
        $return->add($value);

        return $return;
    }


    // siblings
    // retourne un objet des éléments ayant le même parent que la valeur donnée
    // ne retourne pas les routes non existantes
    final public function siblings($value):self
    {
        $return = new static();
        $value = $this->get($value);

        if(!empty($value))
        {
            $parent = $this->parent($value);

            foreach ($this->arr() as $k => $v)
            {
                if($v !== $value && $this->parent($v) === $parent)
                $return->add($v);
            }
        }

        return $return;
    }


    // childs
    // retourne un objet avec les enfants de l'élément donné en argument
    // ne retourne pas les routes non existantes
    final public function childs($value):self
    {
        $return = new static();
        $value = $this->get($value);

        if(!empty($value))
        {
            foreach ($this->arr() as $k => $v)
            {
                if($this->parent($v) === $value)
                $return->add($v);
            }
        }

        return $return;
    }


    // withSegment
    // retourne un objet avec toutes les routes avec segment
    final public function withSegment():self
    {
        $return = new static();

        foreach ($this->arr() as $key => $value)
        {
            if($value::isSegmentClass())
            $return->add($value);
        }

        return $return;
    }


    // withoutSegment
    // retourne un objet avec toutes les routes sans segment
    final public function withoutSegment():self
    {
        $return = new static();

        foreach ($this->arr() as $key => $value)
        {
            if(!$value::isSegmentClass())
            $return->add($value);
        }

        return $return;
    }


    // allowed
    // retourne un objet avec toutes les routes allowed
    final public function allowed():self
    {
        $return = new static();

        foreach ($this->arr() as $key => $value)
        {
            if($value::allowed())
            $return->add($value);
        }

        return $return;
    }


    // all
    // retourne toutes les routes triggés
    // toutes les différentes versions de segment sont aussi triggés pour routeSegment
    final public function all():array
    {
        $return = [];

        foreach ($this->arr() as $key => $value)
        {
            if($value::isSegmentClass())
            {
                foreach ($value::allSegment() as $segment)
                {
                    $route = $value::make($segment);
                    $return[] = $route;
                }
            }

            else
            {
                $route = $value::make();
                $return[] = $route;
            }
        }

        return $return;
    }


    // sitemap
    // retourne un tableau avec les uris absoluts de toutes les routes
    // ceci inclut les uris des routes avec segment
    // possible de retourner plusieurs langues
    final public function sitemap($langs,?array $option=null):array
    {
        $return = [];

        if(!is_array($langs))
        $langs = [$langs];

        foreach ($langs as $lang)
        {
            if(is_string($lang))
            {
                foreach ($this->arr() as $key => $value)
                {
                    if($value::allowed() && $value::inSitemap())
                    {
                        $uri = null;

                        if($value::isSegmentClass())
                        {
                            foreach ($value::allSegment() as $segment)
                            {
                                $uri = $value::make($segment)->uriAbsolute($lang,$option);

                                if(!in_array($uri,$return,true))
                                $return[] = $uri;
                            }
                        }

                        else
                        {
                            $uri = $value::make()->uriAbsolute($lang,$option);

                            if(!in_array($uri,$return,true))
                            $return[] = $uri;
                        }
                    }
                }
            }

            else
            static::throw('invalidLang');
        }

        return $return;
    }


    // init
    // init l'objet routes, envoie dans setType, setPriority et setParent
    // aussi sort par défaut
    final public function init(string $type):self
    {
        $this->setType($type,true);
        $this->setPriority();
        $this->sortDefault();
        $this->setParent();
        $this->init = true;

        return $this;
    }


    // setType
    // applique le type aux différentes routes de l'objet
    // possibilité de dig (et d'appliquer aux prents)
    final public function setType(string $type,bool $dig=false):self
    {
        foreach ($this->arr() as $key => $value)
        {
            $value::setType($type,$dig);
        }

        return $this;
    }


    // setPriority
    // donne une priorité pour les routes qui n'ent ont pas et sort par défaut
    final public function setPriority():self
    {
        $increment = $this->getAttr('priorityIncrement');
        $i = $increment;
        foreach ($this->arr() as $key => $value)
        {
            $priority = $value::priority();

            if(empty($priority))
            {
                $value::setPriority($i);
                $i += $increment;
            }
        }

        return $this;
    }


    // setParent
    // trouve et change les parents par défaut, seulement pour les routes qui n'ont pas de parent
    final public function setParent():self
    {
        $keys = $this->keys();
        foreach (Base\Arr::camelCaseParent($keys) as $key => $value)
        {
            if(is_string($value))
            {
                $key = $this->get($key);
                $value = $this->get($value);

                if($key::parent() === null)
                $key::setParent($value);
            }
        }

        return $this;
    }


    // makeBreadcrumbs
    // génère une chaîne breadcrumbs à partir d'un separator et d'une série de route triggé
    final public static function makeBreadcrumbs(string $separator,$pattern=null,Route ...$routes):string
    {
        $return = '';
        $array = [];

        foreach ($routes as $route)
        {
            $array[] = $route->aTitle($pattern);
        }

        if(!empty($array))
        $return = implode($separator,$array);

        return $return;
    }
}

// init
Routes::__init();
?>