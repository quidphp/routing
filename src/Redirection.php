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

// redirection
// class managing a URI redirection array
class Redirection extends Main\Map
{
    // trait
    use Main\_inst;
    use Main\Map\_readOnly;


    // config
    protected static array $config = [];


    // dynamique
    protected ?array $mapAllow = ['set','unset','remove','empty','overwrite','serialize']; // méthodes permises


    // onPrepareKey
    // prépare une clé
    // les uri sont conservés dans l'objet en absolut, sans le scheme, et en version non encodés
    final protected function onPrepareKey($return)
    {
        if($return instanceof Request)
        $return = $return->absolute(['encode'=>false]);

        elseif(is_string($return))
        $return = Base\Uri::absolute($return,null,['encode'=>false]);

        if(is_string($return))
        $return = Base\Uri::remove('scheme',$return);

        return $return;
    }


    // onPrepareValue
    // prépare une valeur
    // les uri sont conservés dans l'objet en absolute et en version non encodés
    final protected function onPrepareValue($return)
    {
        if($return instanceof Request)
        $return = $return->absolute(['encode'=>false]);

        elseif(is_string($return))
        $return = Base\Uri::absolute($return,null,['encode'=>false]);

        return $return;
    }


    // onPrepareReplace
    // méthode appelé avec le contenu des méthodes de remplacement comme overwrite
    // ajoute le scheme au clé si le replace est un objet redirection
    final protected function onPrepareReplace($return)
    {
        if($return instanceof self)
        {
            $array = $return->toArray();
            $return = [];

            foreach ($array as $key => $value)
            {
                $key = Base\Uri::changeScheme(true,$key);
                $return[$key] = $value;
            }
        }

        return $return;
    }


    // exists
    // retourne vrai si toutes les uri ont une redirection
    final public function exists(...$keys):bool
    {
        return Base\Arr::every($keys,fn($key) => is_string($this->get($key)));
    }


    // get
    // trouve la redirection de l'uri
    // support pour les uri avec un *, tel que défini dans base/uri
    final public function get($key)
    {
        $return = null;
        $key = $this->onPrepareKey($key);

        if(is_string($key))
        $return = $this->onPrepareReturn(Base\Uri::redirection($key,$this->toArray()));

        return $return;
    }


    // gets
    // trouve la redirection des uris
    // support pour les uri avec un *, tel que défini dans base/uri
    final public function gets(...$keys):array
    {
        $return = [];

        foreach ($keys as $key)
        {
            $key = $this->onPrepareKey($key);

            if(is_string($key))
            $return[$key] = $this->get($key);
        }

        return $return;
    }


    // trigger
    // redirige vers l'uri valeur si trouvé, sinon retourne null
    // par défaut encode l'uri valeur et tue la réponse immédiatement après
    // le code par défaut utilisé est 301
    final public function trigger($key,$code=301,$kill=true,bool $encode=true):void
    {
        $value = $this->get($key);
        $response = Route::response();

        if(!empty($value))
        $response->redirect($value,$code,$kill,$encode);
    }


    // set
    // ajoute une nouvelle entrée dans le tableau de redirection
    final public function set($key,$value):self
    {
        $value = $this->onPrepareValue($value);

        if(!is_string($value) || empty($value))
        static::throw('invalidValue');

        return parent::set($key,$value);
    }


    // makeOverwrite
    // remplace le contenu de l'objet de redirection par un tableau
    // possible de remplacer par une autre instance de map
    final protected function makeOverwrite($value):void
    {
        $value = $this->onPrepareReplace($value);

        if(!is_array($value))
        static::throw('requireArray');

        $this->checkBefore(false,...array_values($value));
        $this->sets($value);
        $this->checkAfter();
    }
}
?>