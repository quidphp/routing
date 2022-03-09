<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/routing/blob/master/LICENSE
 */

namespace Quid\Routing;
use Quid\Base;

// _service
// trait that provides methods to link a service to a route
trait _service
{
    // config
    protected static array $configService = [
        'serviceType'=>'route', // permet d'identifier les services pour une route
        'paths'=>[ // différents chemins
            'serverFrom'=>null,
            'serverTo'=>null,
            'public'=>null]
    ];


    // getBasename
    // retourne le basename pour la copie
    final public function getBasename():?string
    {
        return $this->getAttr(['paths','basename']);
    }


    // getCopyLink
    // retorune les liens nécessaires au service
    // permet que des scripts soient copiés et accessibles publiquements
    final public function getCopyLink():?array
    {
        $return = null;
        $serverFromPath = $this->getServerFromPath();

        if(!empty($serverFromPath))
        {
            $serverToPath = $this->getServerToPath();

            if(!empty($serverToPath))
            $return[$serverFromPath] = $serverToPath;
        }

        return $return;
    }


    // makeServerPublicPath
    // méthode protégé utilisé pour obtenir server et public paths
    final protected function makeServerPublicPath(string $type):?string
    {
        $return = null;
        $basename = $this->getBasename();

        if(!empty($basename))
        {
            $return = $this->getAttr(['paths',$type]);
            $return = Base\Str::replace(['%basename%'=>$basename],$return);
        }

        return $return;
    }


    // getServerFromPath
    // retourne le chemin server source
    final public function getServerFromPath():?string
    {
        return $this->makeServerPublicPath('serverFrom');
    }


    // getServerToPath
    // retourne le chemin server target
    final public function getServerToPath():?string
    {
        return $this->makeServerPublicPath('serverTo');
    }


    // getPublicPath
    // retourne le public path du service
    // utilisé pour charger un script
    final public function getPublicPath():?string
    {
        return $this->makeServerPublicPath('public');
    }


    // docOpenJs
    // js à ajouter dans le docOpen
    public function docOpenJs()
    {
        return;
    }


    // docOpenScript
    // script à ajouter dans le docOpen
    public function docOpenScript()
    {
        return;
    }


    // docOpenCss
    // css à ajouter dans le docOpen
    public function docOpenCss()
    {
        return;
    }


    // docCloseJs
    // js à ajouter avant la fermeture du body
    public function docCloseJs()
    {
        return;
    }


    // docCloseScript
    // script à ajouter avant la fermeture du body
    public function docCloseScript()
    {
        return;
    }
}
?>