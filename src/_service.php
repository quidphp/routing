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
            'public'=>null,
            'extra'=>null]
    ];


    // getBasename
    // retourne le basename pour la copie
    final public function getBasename():?string
    {
        return $this->getAttr(['paths','basename']);
    }


    // getCopyLinks
    // retourne les liens nécessaires au service
    // permet que des scripts soient copiés et accessibles publiquements
    final public function getCopyLinks():?array
    {
        $return = null;
        $serverFromPath = $this->getServerFromPath();

        if(!empty($serverFromPath))
        {
            $serverToPath = $this->getServerToPath();

            if(!empty($serverToPath))
            $return[$serverFromPath] = $serverToPath;

            $return = $this->getExtraCopyLinks($return);
        }

        return $return;
    }


    // getExtraCopyLinks
    // permet de lier des chemins de copie additionnel au service
    final protected function getExtraCopyLinks(array $return):array
    {
        $extra = $this->getAttr(['paths','extra']);
        if(is_array($extra) && !empty($extra))
        {
            foreach ($extra as $from => $to)
            {
                if(is_string($from) && is_string($to))
                {
                    $from = $this->makeServerPublicPath($from);
                    $return[$from] = $this->makeServerPublicPath($to);
                }
            }
        }

        return $return;
    }


    // makeServerPublicPath
    // méthode protégé utilisé pour obtenir server et public paths
    // il doit y avoir un basename, sinon retourne null
    final protected function makeServerPublicPath(string $value):?string
    {
        $return = null;
        $basename = $this->getBasename();

        if(!empty($basename))
        $return = Base\Str::replace(['%basename%'=>$basename],$value);

        return $return;
    }


    // getServerFromPath
    // retourne le chemin server source
    final public function getServerFromPath():?string
    {
        $path = $this->getAttr(['paths','serverFrom']);
        return (is_string($path))? $this->makeServerPublicPath($path):null;
    }


    // getServerToPath
    // retourne le chemin server target
    final public function getServerToPath():?string
    {
        $path = $this->getAttr(['paths','serverTo']);
        return (is_string($path))? $this->makeServerPublicPath($path):null;
    }


    // getPublicPath
    // retourne le public path du service
    // utilisé pour charger un script
    final public function getPublicPath():?string
    {
        $path = $this->getAttr(['paths','public']);
        return (is_string($path))? $this->makeServerPublicPath($path):null;
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