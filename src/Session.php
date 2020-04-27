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
use Quid\Main;
use Quid\Routing;

// session
// extended class that adds session support for routes
class Session extends Main\Session
{
    // config
    protected static array $config = [
        'historyClass'=>Routing\RequestHistory::class, // classe de l'historique de requête
        'structure'=>[ // callables de structure additionnelles dans data, se merge à celle dans base/session
            'nav'=>'structureNav']
    ];


    // structureNav
    // gère le champ structure nav de la session
    // mode insert, update ou is
    final public function structureNav(string $mode,$value=null)
    {
        $return = $value;

        if($mode === 'insert')
        $return = Routing\Nav::newOverload();

        elseif($mode === 'is')
        $return = ($value instanceof Routing\Nav);

        return $return;
    }


    // nav
    // retourne l'objet nav
    final public function nav():Routing\Nav
    {
        return $this->get('nav');
    }


    // navEmpty
    // vide l'objet nav
    final public function navEmpty():self
    {
        $this->nav()->empty();

        return $this;
    }
}

// init
Session::__init();
?>