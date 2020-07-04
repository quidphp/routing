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

// route
// abstract class for a route that acts as both a View and a Controller
abstract class Route extends Main\ArrObj implements Main\Contract\Meta
{
    // trait
    use Main\_attrPermission;


    // config
    protected static array $config = [
        'path'=>'undefined', // match path de la route, peut y avoir plusieurs, si il y a clé c'est une lang
        'match'=>[ // vérification lancé pour trouver le match
            'ssl'=>null, // si la requête passe via ssl ou non
            'ajax'=>null, // si la requête est ajax ou non
            'cli'=>null, // si la requête est cli ou non
            'host'=>null, // tous les hosts sont acceptés
            'method'=>'get', // toutes les méthodes sont acceptées
            'query'=>null, // validation sur contenu de query
            'post'=>null, // validation sur contenu de post
            'genuine'=>null, // validation que le champ genuine est vide
            'header'=>null, // validation sur le tableau des headers
            'lang'=>null, // toutes les langs sont acceptés
            'ip'=>null, // tous les ip sont acceptés
            'session'=>null, // validation sur contenu de session
            'role'=>null, // validation du code ou de la classe de permission
            'csrf'=>null, // validation que le champ csrf et le même que dans session
            'captcha'=>null, // validation que le champ captcha est le même que dans session
            'timeout'=>null], // défini les timeouts à vérifier
        'response'=>[
            'timeLimit'=>null, // limit de temps pour la route
            'code'=>200, // code de réponse
            'contentType'=>null, // contentType de la réponse
            'header'=>null], // tableau de header à sets à la réponse
        'timeout'=>null, // défini les timeouts à lier à la route trigger
        'query'=>null, // détermine les éléments de query conservés dans la route
        'replace'=>[ // permet de spécifier des callbacks pour les valeurs du tableau de remplacement
            'title'=>[Base\Html::class,'titleValue'],
            'metaTitle'=>[Base\Html::class,'titleValue'],
            'metaDescription'=>[Base\Html::class,'metaDescriptionValue'],
            'metaKeywords'=>[Base\Html::class,'metaKeywordsValue'],
            'metaUri'=>[Base\Html::class,'metaUriValue'],
            'htmlAttr'=>[Base\Attr::class,'arr'],
            'bodyAttr'=>[Base\Attr::class,'arr']],
        'docOpen'=>[ // utilisé pour l'ouverture du document
            'html'=>['lang'=>'%lang%','data-route'=>'%name%','data-group'=>'%group%','data-uri'=>'%uri%','data-navigation'=>'%navigation%','%htmlAttr%'],
            'head'=>[
                'title'=>'%title%',
                'meta'=>[
                    'description'=>'%metaDescription%',
                    'keywords'=>'%metaKeywords%',
                    'og:type'=>'website',
                    'og:title'=>'%title%',
                    'og:url'=>'%metaUri%',
                    'og:image'=>'%metaImage%',
                    'viewport'=>'width=device-width, initial-scale=1',
                    'msapplication-config'=>'none'],
                'link'=>[],
                'script'=>[],
                'css'=>[],
                'js'=>[]],
            'body'=>['%bodyAttr%']],
        'docClose'=>[ // utilisé pour la fermeture du document
            'script'=>[],
            'js'=>[]],
        'a'=>[ // attribut et option pour la tag a
            'attr'=>null,
            'option'=>null],
        'form'=>[ // attribut et option pour la tag form
            'method'=>'get', // method par défaut si pas de méthode a la route
            'attr'=>null,
            'option'=>null],
        'type'=>null, // type de la route
        'label'=>null, // nom de la route
        'description'=>null, // description de la route
        'jsInit'=>null, // s'il faut init le js en cas de requête non ajax
        'redirectable'=>null, // défini si la route est redirigable
        'sitemap'=>null, // la route fait parti de sitemap
        'uri'=>null, // attribut pour output, relative et absolut
        'parent'=>null, // classe parente de la route
        'priority'=>0, // priorité de la route
        'navigation'=>true, // active la navigation via history sur la route
        'group'=>'default', // groupe spécifique de la route, comme home ou error
        'menu'=>null, // détermine si la route fait partie d'un ou plusieurs menus
        'history'=>true, // la requête est ajouté à l'historique de session
        'uriAbsolute'=>null, // force toutes les uris générés via uri output dans la route à être absolute
        'errorCss'=>true, // ajoute le fichier css type à la classe erreur, lors du docopen
        'cliHtmlOverload'=>null, // force les méthodes cli à générer du html, seulement si c'est true et que cli est false
        'selectedUri'=>true, // ajoute l'uri de la route trigger comme uriSelected
        'jsonEncodePretty'=>false, // si le retour est un tableau, utilise jsonEncodePretty
        'permission'=>[ // tableau des permissions
            '*'=>['access'=>true]], // accorde accès de base
        'ignore'=>false, // si la route est ignoré pour routes
        'catchAll'=>false, // si true, le dernier segment attrape tout le reste du chemin dans le processus de match
        'debug'=>false, // active ou non le débogagge de match, en lien avec la méthode statique debug
        'defaultSegment'=>'-', // caractère pour un segment avec valeur par défaut
        'replaceSegment'=>'%%%', // pattern utilisé pour faire un remplacement sur un segment, cette valeur passe dans makSegment à tout coup
        'segment'=>[], // tableau qui permet de remplacer une clé de segment par un autre, utiliser dans methodSegment
    ];


    // dynamique
    protected RouteRequest $routeRequest; // variable qui contient l'objet routeRequest
    protected bool $trigger = false; // garde en mémoire si la route est trigger ou non


    // construct
    // construit l'objet route
    final public function __construct($request=null)
    {
        $this->attr =& static::$config;
        $this->setRouteRequest($request);
        $this->onMake();
    }


    // prepareTitle
    // prépare le titre après le onReplace
    abstract protected function prepareTitle($return,array $array):array;


    // host
    // retourne le host pour la route
    abstract public static function host():?string;


    // schemeHost
    // retourne le schemeHost pour la route
    abstract public static function schemeHost():?string;


    // type
    // retourne le type de la route
    abstract public static function type():string;


    // routes
    // retourne l'objet routes de boot du type dela route
    abstract public static function routes():Routes;


    // lang
    // retourne l'objet lang
    abstract public static function lang():Main\Lang;


    // session
    // retourne l'objet session
    abstract public static function session():Session;


    // services
    // retourne l'objet services
    abstract public static function services():Main\Services;


    // toString
    // retourne la valeur de la route sous forme de string
    final public function __toString():string
    {
        return static::name();
    }


    // cast
    // retourne la valeur cast
    final public function _cast():string
    {
        return $this->uri();
    }


    // clone
    // clone l'objet route et l'objet routeRequest
    final public function __clone()
    {
        $this->routeRequest = clone $this->routeRequest;
    }


    // onMake
    // permet d'avoir un callback lors de la construction de la route
    protected function onMake():void
    {
        return;
    }


    // onBefore
    // méthode appelé au début de la méthode before
    // possible d'arrêter la route si onBefore retourne faux
    // par défaut renvoie à canTrigger
    protected function onBefore()
    {
        return $this->canTrigger();
    }


    // onPrepared
    // callback appelé à la fin du processBefore
    protected function onPrepared()
    {
        return;
    }


    // onAfter
    // méthode appelé à la fin de la méthode after
    // possible de spécifier une redirection
    protected function onAfter()
    {
        return;
    }


    // onFallback
    // méthode appelé lorsqu'il y a un fallback
    // possible de spécifier une redirection
    // permet par exemple de flashPost ou retourner une redirection
    protected function onFallback($context=null)
    {
        return;
    }


    // onReplace
    // méthode à étendre pour changer le tableau de remplacement pour une route
    protected function onReplace(array $return):array
    {
        return $return;
    }


    // onPrepareDoc
    // pour changer le tableau de prepareDoc, peut être étendu
    final protected function onPrepareDoc(string $type,array $return):array
    {
        if($type === 'docClose')
        $return = $this->prepareDocJsInit($return);

        $return = $this->prepareDocServices($type,$return);

        return $return;
    }


    // onRolePermission
    // callback avant chaque appel à permission can, vérifie que la table à la permission access
    final protected function onRolePermission($key,array $array):bool
    {
        return array_key_exists('access',$array) && $array['access'] === true;
    }


    // arr
    // retourne le tableau de segments pour utiliser via this
    final protected function arr():array
    {
        return $this->segments();
    }


    // offsetSet
    // arrayAccess offsetSet n'est pas permis pour la classe
    final public function offsetSet($key,$value):void
    {
        static::throw('arrayAccess','notAllowed');
    }


    // offsetUnset
    // arrayAccess offsetUnset n'est pas permis pour la classe
    final public function offsetUnset($key):void
    {
        static::throw('arrayAccess','notAllowed');
    }


    // attrPermissionRolesObject
    // retourne les rôles courants
    final protected function attrPermissionRolesObject():Main\Roles
    {
        return static::session()->roles(true);
    }


    // getBaseReplace
    // retourne le tableau de remplacement de base
    public function getBaseReplace():array
    {
        $return = [];
        $parent = static::parent();
        $request = $this->request();
        $uri = (static::hasPath())? $this->uriRelative():$request->relative();

        $return['label'] = $this->title();
        $return['name'] = static::name(true);
        $return['type'] = static::type();
        $return['uri'] = $uri;
        $return['metaUri'] = $uri;
        $return['group'] = static::group();
        $return['parent'] = (!empty($parent))? $parent::name(true):null;
        $return['title'] = $return['label'];
        $return['navigation'] = (static::allowNavigation() === true)? 1:0;
        $return['htmlAttr'] = null;
        $return['bodyAttr'] = null;

        return $return;
    }


    // prepareDocJsInit
    // ajoute la méthode jsInit si jsInit est true et que ce n'est pas une requête ajax
    final protected function prepareDocJsInit(array $return):array
    {
        $jsInit = $this->getAttr('jsInit');

        if(!empty($jsInit) && $this->request()->isAjax() === false)
        {
            $callable = null;

            if(static::isCallable($jsInit))
            $callable = $jsInit;
            else
            $callable = fn() => $jsInit;

            $return['script'] = Base\Arr::merge($return['script'],[$callable]);
        }

        return $return;
    }


    // prepareDocServices
    // méthode utilisé après prepareDoc, lie les tags de services pour docOpen et docClose
    // si un des éléments est false dans le tableau de config, à ce moment n'append pas le service (ça vaut dire que la route n'a pas de js/css/script)
    final protected function prepareDocServices(string $type,array $return):array
    {
        $services = static::services();

        foreach ($services as $service)
        {
            $key = $service->getServiceKey(true);

            if($type === 'docOpen')
            {
                $return['head']['js'] = $return['head']['js'] ?? null;
                if($return['head']['js'] !== false)
                {
                    $js = $service->docOpenJs();
                    if(!empty($js))
                    {
                        $append = (is_array($js))? $js:[$key=>$js];
                        $return['head']['js'] = Base\Arr::merge($return['head']['js'] ?? [],$append);
                    }
                }

                $return['head']['script'] = $return['head']['script'] ?? null;
                if($return['head']['script'] !== false)
                {
                    $script = $service->docOpenScript();
                    if(!empty($script))
                    $return['head']['script'] = Base\Arr::merge($return['head']['script'] ?? [],$script);
                }
            }

            elseif($type === 'docClose')
            {
                $return['script'] = $return['script'] ?? null;
                if($return['script'] !== false)
                {
                    $script = $service->docCloseScript();
                    if(!empty($script))
                    $return['script'] = Base\Arr::merge($return['script'] ?? [],$script);
                }
            }
        }

        return $return;
    }


    // isTriggered
    // retourne vrai si la route est présentement triggé
    final public function isTriggered():bool
    {
        return $this->trigger === true;
    }


    // allowed
    // retourne vrai si le role de la session courante permet d'accéder à la route
    final public static function allowed(?Main\Role $role=null):bool
    {
        $return = false;
        $value = static::$config['match']['role'] ?? null;
        $class = static::routeRequestClass();

        if(empty($role))
        $role = static::session()->role();

        $return = $class::allowed($value,$role);

        return $return;
    }


    // hasPath
    // retourne vrai si la route a un path pour la langue
    final public static function hasPath(?string $lang=null):bool
    {
        $return = false;
        $class = static::routeRequestClass();
        $lang = ($lang === null)? static::session()->lang():$lang;
        $path = $class::pathFromRoute(static::class,$lang);

        if($path !== null && $path !== false)
        $return = true;

        return $return;
    }


    // getTimeoutObject
    // retourne l'objet timeout
    final public static function getTimeoutObject():Main\Timeout
    {
        return static::session()->timeout();
    }


    // canTrigger
    // retourne vrai si la route peut être triggé
    // par défaut vérifie que la route est allowed (donc compatible au niveau du rôle)
    // méthode doit resté public
    public function canTrigger():bool
    {
        return static::allowed();
    }


    // trigger
    // lance la route
    // retourne faux, et passe à la prochaine route
    // retourne null, rien ne se passe
    // retourne string ou array echo
    // retourne objet, ça envoie une exception
    public function trigger()
    {
        return false;
    }


    // launch
    // lance tout le processus de lancement et output de la route
    // retourne un tableau avec bool, continue et output
    // output est toujours sous forme de string
    final public function launch():array
    {
        $return = ['bool'=>false,'continue'=>false,'output'=>null];
        $output = null;

        try
        {
            $bool = false;
            $continue = false;
            $output = $this->start();

            if($output === false)
            $continue = true;

            else
            {
                $encodePretty = $this->getAttr('jsonEncodePretty');
                $output = Base\Str::cast($output,null,false,$encodePretty);
                $bool = true;
            }
        }

        catch (Exception $e)
        {
            $e->catched();
            $continue = true;
        }

        catch (BreakException $e)
        {
            Base\Response::serverError();
            $e->catched();
        }

        $return['bool'] = $bool;
        $return['continue'] = $continue;
        $return['output'] = $output;

        return $return;
    }


    // fallback
    // méthode lancé après before si le match a échoué
    // gère le timeout, captcha, csrf, genuine et failedFileUpload
    // s'il y a redirection utilise le code 302
    final protected function fallback($context=null):bool
    {
        $log = null;
        $code = null;

        if(is_array($context) && current($context) === 'timeout')
        {
            $log = $context;
            static::sessionCom()->neg('timeout/retry');
        }

        elseif($context === 'captcha')
        {
            $log = $context;
            static::sessionCom()->neg('captcha');
        }

        elseif(in_array($context,['csrf','genuine'],true))
        {
            $code = 400;
            $log = $context;
            static::sessionCom()->neg([$context,'retry']);
        }

        elseif($this->request()->isFailedFileUpload())
        {
            $code = 400;
            $context = 'failedFileUpload';
            $log = $context;
            $maxFilesize = Base\Ini::uploadMaxFilesize(2);
            $replace = ['maxFilesize'=>$maxFilesize];
            static::sessionCom()->neg('fileUpload/maxFilesize',$replace);
            static::sessionCom()->neg('fileUpload/dataLost');
        }

        if(is_int($code))
        Base\Response::setCode(400);

        if(!empty($log))
        {
            $log = ['fallback'=>$log];
            $this->request()->setLogData($log);
        }

        $redirect = $this->onFallback($context);
        if(!empty($redirect))
        $this->processRedirect($redirect);

        return false;
    }


    // setRouteRequest
    // change la routeRequest de l'objet
    final protected function setRouteRequest($request=null):void
    {
        $return = null;

        if(static::isSegmentClass())
        {
            $lang = static::session()->lang();
            $routeRequest = RouteSegmentRequest::newOverload($this,$request,$lang);
        }

        else
        $routeRequest = RouteRequest::newOverload($this,$request);

        $this->routeRequest = $routeRequest;
    }


    // routeRequest
    // retourne l'objet routeRequest
    // si segment est true, envooe une exception si ce n'est pas un routeSegmentRequest
    final public function routeRequest(bool $segment=false):RouteRequest
    {
        $return = $this->routeRequest;

        if($segment === true && !$return instanceof RouteSegmentRequest)
        static::throw('routeHasNoSegment');

        return $return;
    }


    // request
    // retourne l'objet request de routeRequest
    final public function request():Main\Request
    {
        return $this->routeRequest()->request();
    }


    // makeTitle
    // fait le titre pour la route triggé
    // par défaut, retourne le label
    // n'est pas abstraite
    protected function makeTitle(?string $lang=null)
    {
        return static::label(null,$lang);
    }


    // init
    // lance isValid sur la route, retourne l'objet route
    // comme isValid mais retourne la route plutôt qu'un booléean
    final public function init(bool $exception=false):self
    {
        $this->isValid($exception);

        return $this;
    }


    // isValid
    // retourne vrai si la route et la requête match
    final public function isValid(bool $exception=false):bool
    {
        return $this->routeRequest()->isValid(static::session(),$exception);
    }


    // checkValid
    // envoie une exception si la route et la requête ne passent pas les tests match
    // si valid est false, le test n'est pas lancé et utilise le résultat courant
    final public function checkValid(bool $valid=true):self
    {
        if($valid === true)
        $this->isValid(true);

        $this->routeRequest()->checkValid();

        return $this;
    }


    // start
    // lance la route
    // retourne le résultat de la route
    final public function start()
    {
        $return = null;
        static::prepareTimeout();

        try
        {
            $return = $this->onBefore();

            if($return !== false)
            {
                $this->processBefore();

                $timeout = static::timeout();
                if(array_key_exists('trigger',$timeout))
                static::timeoutIncrement('trigger');

                $this->trigger = true;
                $return = $this->trigger();

                if($return !== false)
                $this->processAfter();

                else
                $return = $this->fallback('trigger');
            }

            else
            $return = $this->fallback('onBefore');
        }

        catch (Main\Contract\Catchable $e)
        {
            $e->catched();
            $return = $this->fallback($e);
        }

        return $return;
    }


    // processBefore
    // avant la méthode trigger
    // refresh le csrf si il a été validé, met la uri selected etc
    protected function processBefore():void
    {
        $session = static::session();
        $response = $this->getAttr('response') ?? [];

        if(array_key_exists('timeLimit',$response) && is_int($response['timeLimit']))
        Base\Response::timeLimit($response['timeLimit']);

        if(static::hasMatch('csrf'))
        $session->refreshCsrf();

        if(static::hasMatch('captcha'))
        $session->emptyCaptcha();

        $selectedUri = $this->getAttr('selectedUri');
        if(!empty($selectedUri))
        $this->addSelectedUri($selectedUri);

        $uriAbsolute = $this->getAttr('uriAbsolute');
        if(is_bool($uriAbsolute))
        Base\Uri::setAllAbsolute($uriAbsolute);

        $cliHtmlOverload = $this->getAttr('cliHtmlOverload');
        if($cliHtmlOverload === true && !Base\Server::isCli())
        Base\Cli::setHtmlOverload($cliHtmlOverload);

        $this->prepareResponse();
        $this->onPrepared();
    }


    // processAfter
    // après la méthode trigger
    // met le code response, le contentType et des headers de response et ajoute la requête à l'historique
    // gère le onAfter qui peut rediriger
    final protected function processAfter():void
    {
        if(static::shouldKeepInHistory())
        {
            $history = $this->getAttr('history');
            $method = ($history === 'unique')? 'addUnique':'add';
            $request = $this->request();
            $history = static::session()->history();
            $history->$method($request);
        }

        $redirect = $this->onAfter();
        if(!empty($redirect))
        $this->processRedirect($redirect);
    }


    // processRedirect
    // gère un redirect, par exemple pour after ou fallback
    // le code utilisé par défaut est 302
    final protected function processRedirect($value,$code=true,bool $kill=true):void
    {
        if(is_string($value) && is_subclass_of($value,self::class,true))
        $value = $value::make();

        if(is_string($value))
        Base\Response::redirect($value,$code,$kill);

        elseif($value === true)
        {
            $routes = static::routes();
            static::session()->history()->previousRedirect($routes,true,true,['code'=>$code,'kill'=>$kill]);
        }

        elseif($value instanceof self)
        Base\Response::redirect($value->uriAbsolute(),$code,$kill);
    }


    // getMetaFromContract
    // retourne un tableau avec les méta données pour un objet ayant l'interface meta
    // pour meta description, si la valeur est -, remplace par null (donc le défaut va prendre le dessus)
    final protected function getMetaFromContract(Main\Contract\Meta $meta,array $return):array
    {
        $array = [];

        $array['title'] = $meta->getMetaTitle($return['title'] ?? null);
        $array['metaKeywords'] = $meta->getMetaKeywords($return['metaKeywords'] ?? null);
        $array['metaDescription'] = $meta->getMetaDescription($return['metaDescription'] ?? null);
        $array['metaImage'] = $meta->getMetaImage($return['metaImage'] ?? null);
        $array['htmlAttr'] = $meta->getHtmlAttr($return['htmlAttr'] ?? null);
        $array['bodyAttr'] = $meta->getBodyAttr($return['bodyAttr'] ?? null);

        if($array['metaImage'] instanceof Main\File)
        $array['metaImage'] = $array['metaImage']->pathToUri();

        if(Base\Obj::cast($array['metaDescription']) === '-')
        $array['metaDescription'] = null;

        foreach ($array as $key => $value)
        {
            if($value !== null)
            $return[$key] = $value;
        }

        return $return;
    }


    // getMetaTitle
    // retourne les données pour le metaTitle
    public function getMetaTitle($value=null)
    {
        return;
    }


    // getMetaKeywords
    // retourne les données pour le metaKeywords
    public function getMetaKeywords($value=null)
    {
        return;
    }


    // getMetaDescription
    // retourne les données pour la metaDescription
    public function getMetaDescription($value=null)
    {
        return;
    }


    // getMetaImage
    // retourne les données pour la metaImage
    public function getMetaImage($value=null)
    {
        return;
    }


    // getHtmlAttr
    // retourne les données pour les attributs de html
    public function getHtmlAttr($value=null)
    {
        return;
    }


    // getBodyAttr
    // retourne les données pour les attributs de body
    public function getBodyAttr($value=null)
    {
        return;
    }


    // label
    // retourne le label de la route non triggé
    final public static function label($pattern=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = static::lang();
        $path = static::$config['label'] ?? null;
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);

        if(!empty($path))
        $return = $obj->same($path,null,$lang,$option);
        else
        $return = $obj->routeLabel(static::name(true),$lang,$option);

        return $return;
    }


    // description
    // retourne la description de la route non triggé
    final public static function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = static::lang();
        $path = static::$config['description'] ?? null;
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);

        if(!empty($path))
        $return = $obj->same($path,$replace,$lang,$option);
        else
        $return = $obj->routeDescription(static::name(true),$replace,$lang,$option);

        return $return;
    }


    // title
    // retourne le titre de la route triggé
    final public function title($pattern=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $title = $this->makeTitle($lang);
        $title = Base\Obj::cast($title);

        if(is_string($title))
        {
            if(is_scalar($pattern))
            {
                $obj = static::lang();
                $option = Base\Arr::plus($option,['pattern'=>$pattern]);
                $return = $obj->textAfter($title,$option);
            }

            elseif($pattern === null)
            $return = $title;
        }

        else
        static::throw('requiresString');

        return $return;
    }


    // docOpen
    // génère l'ouverture du document en html
    // si l'ouverture c'est fait sans erreur et qu'il y a un fichier css type, met le comme css par défaut pour les erreurs
    final public function docOpen(bool $default=true,?string $separator=null):string
    {
        $return = '';
        $prepare = $this->prepareDoc('docOpen');
        $return = Base\Html::docOpen($prepare,$default,$separator,true);

        $css = $prepare['head']['css']['type'] ?? null;
        if($this->getAttr('errorCss') && is_string($css))
        {
            $class = Main\Error::classOverload();
            $css = Base\Html::css($css);
            if(!empty($css))
            $class::setDocHead($css);
        }

        return $return;
    }


    // docClose
    // génère la fermeture du document en html
    final public function docClose(bool $default=true,bool $closeBody=true,?string $separator=null):string
    {
        return Base\Html::docClose($this->prepareDoc('docClose'),$default,$closeBody,$separator,true);
    }


    // getReplace
    // retourne le tableau de remplacement utilisé par docOpen et docClose
    final public function getReplace():array
    {
        $return = $this->getBaseReplace();
        $return = $this->getMetaFromContract($this,$return);

        $otherMeta = $this->getOtherMeta();
        if(!empty($otherMeta))
        $return = $this->getMetaFromContract($otherMeta,$return);

        $return = $this->onReplace($return);
        $return['title'] = $this->prepareTitle($return['title'] ?? null,$return);

        $return = Base\Obj::cast($return);

        $replace = $this->getAttr('replace');
        if(!empty($replace))
        {
            foreach ($return as $key => $value)
            {
                if(array_key_exists($key,$replace))
                $return[$key] = $replace[$key]($value);
            }
        }

        return $return;
    }


    // getOtherMeta
    // retourne un objet qui implémente l'interface meta
    // doit être étendu, est utilisé dans getReplace
    public function getOtherMeta():?Main\Contract\Meta
    {
        return null;
    }


    // prepareDoc
    // méthode utilisé par docOpen et docClose
    final protected function prepareDoc(string $type):array
    {
        $return = [];

        if(in_array($type,['docOpen','docClose'],true))
        {
            $doc = $this->getAttr($type);

            if(is_array($doc))
            {
                $return = $doc;
                $replace = $this->getReplace($type);

                if(!empty($replace))
                {
                    $replace = Base\Arr::keysWrap('%','%',$replace);
                    $return = Base\Arrs::valuesReplace($replace,$return);

                    $append = [];
                    foreach ($replace as $key => $value)
                    {
                        if(is_array($value))
                        $append[$key] = $value;
                    }

                    if(!empty($append))
                    $return = Base\Arrs::valuesMerge($append,$return);
                }
            }

            $return = $this->onPrepareDoc($type,$return);
            $return = Base\Call::dig(true,$return);
        }

        else
        static::throw();

        return $return;
    }


    // hasUri
    // retourne vrai si la route peut générer une uri pour la langue
    final public function hasUri(?string $lang=null,?array $option=null):bool
    {
        $return = false;
        $lang = ($lang === null)? static::session()->lang():$lang;
        $uri = $this->routeRequest()->uri($lang,$option);

        if(is_string($uri))
        $return = true;

        return $return;
    }


    // uriMethod
    // la variable lang filtre le tableau de chemin avec seulement les paths compatibles pour la langue, si null prend la langue de session
    // une exception est envoyé si retour n'est pas string
    final protected function uriMethod(string $method,?string $lang=null,?array $option=null):string
    {
        $return = '';
        $lang = ($lang === null)? static::session()->lang():$lang;
        $option = Base\Arr::plus($option,$this->getAttr('uri'));
        $return = $this->routeRequest()->$method($lang,$option);

        if(!is_string($return))
        static::throw('impossibleToMakeUri');

        return $return;
    }


    // uri
    // retourne l'uri pour l'objet route
    final public function uri(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uri',$lang,$option);
    }


    // uriOutput
    // retourne l'uri formatté pour l'objet route
    // l'uri peut être relative ou absolut dépendamment des options
    final public function uriOutput(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uriOutput',$lang,$option);
    }


    // uriRelative
    // retourne l'uri relative pour l'objet route
    final public function uriRelative(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uriRelative',$lang,$option);
    }


    // uriAbsolute
    // retourne l'uri absolut pour l'objet route
    final public function uriAbsolute(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uriAbsolute',$lang,$option);
    }


    // addSelectedUri
    // permet d'ajouter l'uri de la route comme uri sélectionné
    final public function addSelectedUri($class=true,?string $lang=null,?array $option=null):bool
    {
        $return = false;

        if($this->hasUri() && $this->canTrigger())
        {
            $uri = $this->uri($lang,$option);
            $selected = [$uri=>$class];
            Base\Attr::addSelectedUri($selected);
        }

        return $return;
    }


    // isSelectedUri
    // retourne vrai si l'uri de la route est sélectionné, tel que défini dans base/attr
    final public function isSelectedUri(?string $lang=null,?array $option=null):bool
    {
        $return = false;
        $uri = $this->uri($lang,$option);

        if(is_string($uri) && Base\Attr::isSelectedUri($uri))
        $return = true;

        return $return;
    }


    // a
    // génère un a tag pour la route
    // possible de spécifier des attr et option par défaut pour a dans static config
    // les options sont pour base/html a
    final public function a($title=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $uri = $this->uri($lang,$option);
        $return = Base\Html::a($uri,$title,$this->tagAttr('a',$attr),$this->tagOption('a',$option));

        return $return;
    }


    // aOpen
    // ouvre un a tag pour la route
    // possible de spécifier des attr et option par défaut pour a dans static config
    // les options sont pour base/html a
    final public function aOpen($title=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $uri = $this->uri($lang,$option);
        $return = Base\Html::aOpen($uri,$title,$this->tagAttr('a',$attr),$this->tagOption('a',$option));

        return $return;
    }


    // aLabel
    // génère un a tag pour la route, le label sera affiché
    // possible de spécifier un pattern de label
    final public function aLabel($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->a(static::label($pattern,$lang),$attr,$lang,$option);
    }


    // aOpenLabel
    // ouvre un a tag pour la route, le label sera affiché
    // possible de spécifier un pattern de label
    final public function aOpenLabel($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->aOpen(static::label($pattern,$lang),$attr,$lang,$option);
    }


    // aTitle
    // génère un a tag pour la route, le title sera affiché
    // possible de spécifier un pattern de title
    final public function aTitle($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->a($this->title($pattern,$lang),$attr,$lang,$option);
    }


    // aOpenTitle
    // ouvre un a tag pour la route, le title sera affiché
    // possible de spécifier un pattern de title
    final public function aOpenTitle($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->aOpen($this->title($pattern,$lang),$attr,$lang,$option);
    }


    // formOpen
    // ouvre un form tag pour la route
    // les options sont pour base/html formOpen
    final public function formOpen($attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $uri = $this->uri($lang,$option);
        $attr = $this->tagAttr('form',$attr);

        if(empty($attr['method']))
        {
            $method = $this->getAttr(['match','method']) ?? $this->getAttr(['form','method']);
            if(!empty($method))
            $attr['method'] = $method;
        }

        if(!empty($attr['data-confirm']))
        $attr['data-confirm'] = $this->getFormText($attr['data-confirm'],'confirm');

        if(!empty($attr['data-unload']))
        $attr['data-unload'] = $this->getFormText($attr['data-unload'],'unload');

        $option = (array) $this->tagOption('form',$option);

        if(!array_key_exists('csrf',$option))
        $option['csrf'] = static::hasMatch('csrf');

        if(!array_key_exists('genuine',$option))
        $option['genuine'] = static::hasMatch('genuine');

        $return = Base\Html::formOpen($uri,$attr,$option);

        return $return;
    }


    // getFormText
    // retourne le texte à utiliser pour form
    // gère unload et confirm
    protected function getFormText($value,string $type):string
    {
        return static::lang()->text($value);
    }


    // formSubmit
    // ouvre et ferme un formulaire avec un bouton submit sans label ou titre
    final public function formSubmit($title=null,$submitAttr=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = $this->formOpen($attr,$lang,$option);
        $return .= Base\Html::submit($title,$submitAttr);
        $return .= Base\Html::formClose();

        return $return;
    }


    // submitLabel
    // fait un tag submit avec label pour soumettre le formulaire
    // méthode statique
    final public static function submitLabel($pattern=null,$attr=null,?string $lang=null):?string
    {
        return Base\Html::submit(static::label($pattern,$lang),$attr);
    }


    // submitTitle
    // fait un tag submit avec title pour soumettre le formulaire
    final public function submitTitle($pattern=null,$attr=null,?string $lang=null):?string
    {
        return Base\Html::submit($this->title($pattern,$lang),$attr);
    }


    // tagAttr
    // retourne un tableau contenant les attributs à utiliser pour une tag
    final public function tagAttr(string $tag,$attr=null):?array
    {
        $return = null;
        $tagConfig = $this->getAttr($tag);

        if($tagConfig !== null)
        {
            if(!is_array($attr))
            $attr = [$attr];

            if(!empty($tagConfig['attr']))
            $return = Base\Attr::append($tagConfig['attr'],$attr);

            else
            $return = $attr;
        }

        else
        static::throw('tagNotDefined');

        if(!static::allowNavigation())
        {
            $return = (array) $return;
            $return['data-navigation'] = false;
        }

        return $return;
    }


    // tagOption
    // retourne un tableau contenant les options à utiliser pour une tag
    final public function tagOption(string $tag,?array $option=null):?array
    {
        $return = null;
        $tagConfig = $this->getAttr($tag);

        if($tagConfig !== null)
        $return = (!empty($tagConfig['option']))? Base\Arr::plus($tagConfig['option'],$option):$option;

        else
        static::throw('tagNotDefined');

        return $return;
    }


    // prepareResponse
    // méthode qui permet de préparer la réponse
    // selon les configurations spécifié dans static config
    final protected function prepareResponse():void
    {
        $response = $this->getAttr('response') ?? [];

        if(!Base\Response::isCodeError() && !empty($response['code']) && is_int($response['code']))
        Base\Response::setCode($response['code']);

        if(!empty($response['contentType']) && is_string($response['contentType']))
        Base\Response::setContentType($response['contentType']);

        if(!empty($response['header']) && is_array($response['header']))
        Base\Response::setsHeader($response['header']);
    }


    // remake
    // cette méthode permet de reconstruire la route
    // utile si la route a des segments, et qu'on veut que les segments deviennent requestSegments dans routeRequest
    // ceci est utilisé pour permettre le changement de langue
    final public function remake(bool $overload=true):self
    {
        $request = (static::isSegmentClass())? $this->segments():null;
        return static::make($request,$overload);
    }


    // childs
    // retourne toutes les enfants de la route courante
    final public static function childs():Routes
    {
        return static::routes()->childs(static::class);
    }


    // make
    // construit une instance de la route de façon statique
    final public static function make($request=null,bool $overload=true):self
    {
        $class = ($overload === true)? static::classOverload():static::class;
        $return = new $class($request);

        return $return;
    }


    // makeParent
    // retourne une instance la route parente
    // envoie une exception s'il n'y a pas de parent valide
    final public static function makeParent($request=null,bool $overload=true):self
    {
        $return = null;
        $parent = static::parent();
        $target = current(static::routeBaseClasses());

        if(empty($parent) || !is_subclass_of($parent,$target,true))
        static::throw('invalidParent');

        $return = $parent::make($request,$overload);

        return $return;
    }


    // isIgnored
    // retourne vrai si la route est ignoré
    final public static function isIgnored():bool
    {
        return (static::$config['ignore'] ?? null) === true;
    }


    // inMenu
    // retourne vrai si la route fait partie d'un menu donné en argument
    final public static function inMenu(string $value):bool
    {
        $return = false;
        $menus = (array) (static::$config['menu'] ?? null);

        if(in_array($value,$menus,true))
        $return = true;

        return $return;
    }


    // isGroup
    // retourne vrai si le groupe est celui spécifié
    final public static function isGroup($value):bool
    {
        return $value === static::group();
    }


    // inSitemap
    // retourne vrai si la route fait partie de sitemap
    final public static function inSitemap(?Role $role=null):bool
    {
        $return = static::$config['sitemap'] ?? null;

        if(!is_bool($return))
        {
            if(static::isSegmentClass())
            $return = false;

            else
            $return = static::isRedirectable($role);
        }

        return $return;
    }


    // allowNavigation
    // retourne vrai si la route permet la navigation
    final public static function allowNavigation():bool
    {
        $return = static::$config['navigation'] ?? null;

        if(!is_bool($return))
        $return = false;

        return $return;
    }


    // setType
    // change le type de la route
    final public static function setType(string $value,bool $dig=false):void
    {
        static::$config['type'] = $value;

        if($dig === true)
        {
            $parent = get_parent_class(static::class);
            if(!empty($parent) && !in_array($parent,static::routeBaseClasses(),true))
            $parent::setType($value,$dig);
        }
    }


    // group
    // retourne le group de la route
    final public static function group():string
    {
        return static::$config['group'];
    }


    // name
    // retourne le nom de la route
    // possible de retourner avec la première lettre lower case
    final public static function name(bool $lcfirst=false):string
    {
        return static::className($lcfirst);
    }


    // priority
    // retourne la priorité de la reoute
    final public static function priority():int
    {
        return static::$config['priority'] ?? 0;
    }


    // setPriority
    // change la priorité de la route
    final public static function setPriority(int $value):void
    {
        static::$config['priority'] = $value;
    }


    // parent
    // retourne la classe parente de la route
    // possible de retourner la classe overload
    final public static function parent(bool $overload=false):?string
    {
        $return = static::$config['parent'] ?? null;

        if(is_string($return))
        $return = $return::classOverload();

        return $return;
    }


    // setParent
    // change le parent de la route
    final public static function setParent(string $value):void
    {
        $target = current(static::routeBaseClasses());

        if(is_subclass_of($value,$target,true))
        static::$config['parent'] = $value;

        else
        static::throw('invalidParentClass');
    }


    // paths
    // retourne tous les chemins de la route
    final public static function paths():array
    {
        $return = static::$config['path'] ?? null;

        if(!is_array($return))
        $return = [$return];

        return $return;
    }


    // isAjax
    // retourne la valeur ajax de match
    // peut retourner null
    final public static function isAjax():?bool
    {
        return static::$config['match']['ajax'] ?? null;
    }


    // isMethod
    // retourne vrai si la route utilise la méthode donné en argument
    final public static function isMethod($value):bool
    {
        $return = false;
        $method = static::$config['match']['method'] ?? null;

        if(is_string($value))
        {
            if($method === true)
            $return = true;

            elseif(is_string($method))
            $method = [$method];

            if(is_array($method) && !empty($method))
            {
                $method = Base\Arr::map($method,fn($v) => strtolower($v));

                if(in_array($value,$method,true))
                $return = true;
            }
        }

        return $return;
    }


    // isRedirectable
    // retourne vrai si la route est redirigable
    // c'est à dire pas ignore, ni post, ni ajax, ni error, ni sitemap
    final public static function isRedirectable(?Main\Role $role=null):bool
    {
        $return = false;
        $isRedirectable = static::$config['redirectable'] ?? null;
        $isSitemap = (static::name(true) === 'sitemap');

        if($isRedirectable !== false && static::allowed($role) && static::hasPath())
        $return = ($isSitemap || static::isMethod('post') || static::isAjax())? false:true;

        return $return;
    }


    // shouldKeepInHistory
    // retourne vrai si la route devrait être gardé dasn l'history
    final public static function shouldKeepInHistory():bool
    {
        return !empty(static::$config['history']);
    }


    // hasMatch
    // permet de vérifier si un élément de validation de la route se retrouve dans match
    final public static function hasMatch(string $type):bool
    {
        $return = false;
        $match = static::$config['match'] ?? [];

        if(!empty($match[$type]))
        $return = true;

        return $return;
    }


    // timeout
    // retourne le tableau de timeout pour la route
    final public static function timeout():array
    {
        return static::$config['timeout'] ?? [];
    }


    // prepareTimeout
    // ajoute les timeout définis dans la route dans l'objet timeout de la session
    final public static function prepareTimeout():Main\Timeout
    {
        $return = static::getTimeoutObject();
        $timeout = static::timeout();

        foreach ($timeout as $key => $value)
        {
            $key = static::makeTimeoutKey($key);
            $return->change($key,$value);
        }

        return $return;
    }


    // makeTimeoutKey
    // génère la clé à utiliser pour l'objet timeout
    // tableau avec nom de la classe + clé
    final protected static function makeTimeoutKey(string $key):array
    {
        return [static::class,$key];
    }


    // timeoutMethod
    // méthode protégé, fait appel à une méthode l'objet timeout
    final protected static function timeoutMethod(string $method,$key)
    {
        $timeout = static::getTimeoutObject();
        $key = static::makeTimeoutKey($key);
        $return = $timeout->$method($key);

        return $return;
    }


    // isTimedOut
    // retourne vrai si l'entrée est timedOut
    final public static function isTimedOut($key):bool
    {
        return static::timeoutMethod('isTimedOut',$key);
    }


    // timeoutGet
    // retourne le count d'une entrée dans l'objet de timeout
    final public static function timeoutGet($key):?int
    {
        return static::timeoutMethod('getCount',$key);
    }


    // timeoutIncrement
    // increment le count de l'entrée dans l'objet timeout
    final public static function timeoutIncrement($key):Main\Timeout
    {
        return static::timeoutMethod('increment',$key);
    }


    // timeoutBlock
    // met le maximum comme count de l'entrée dans l'objet timeout
    final public static function timeoutBlock($key):Main\Timeout
    {
        return static::timeoutMethod('block',$key);
    }


    // timeoutReset
    // reset le count de l'entrée dans l'objet timeout
    final public static function timeoutReset($key):Main\Timeout
    {
        return static::timeoutMethod('resetCount',$key);
    }


    // timeoutStamp
    // met le timestamp actuel à une entrée dans l'objet timeout
    final public static function timeoutStamp($key):Main\Timeout
    {
        return static::timeoutMethod('setTimestamp',$key);
    }


    // routeBaseClasses
    // retourne les classes bases de routes (donc abstraite)
    public static function routeBaseClasses():array
    {
        return [self::class];
    }


    // matchOrFallbackDebug
    // retourne vrai si la route match à la requête
    // sinon gère fallback et/out debug
    final public static function matchOrFallbackDebug(Main\Request $request,bool $fallback=false,bool $debug=false):?self
    {
        $return = null;
        $route = static::make($request);
        $debug = ($debug === true && static::isDebug());

        if($route->isValid($debug))
        $return = $route;

        elseif($fallback === true)
        {
            $context = $route->routeRequest()->fallback();
            if(!empty($context))
            $route->fallback($context);
        }

        return $return;
    }


    // isDebug
    // retourne vrai si la route est en mode débogagge
    final public static function isDebug($value=null):bool
    {
        return static::$config['debug'] === true || ($value !== null && static::$config['debug'] === $value);
    }


    // isValidSegment
    // retourne vrai si la requête et les segments de route match
    final public function isValidSegment(bool $exception=false):bool
    {
        return $this->routeRequest(true)->isValidSegment(static::session(),$exception);
    }


    // checkValidSegment
    // envoie une exception si la requête et la route ne passent pas le test segment
    // si valid est false, le test n'est pas lancé et utilise le résultat courant
    final public function checkValidSegment(bool $valid=true):self
    {
        if($valid === true)
        $this->isValidSegment(true);

        else
        $this->routeRequest(true)->checkValidSegment();

        return $this;
    }


    // segments
    // retourne le tableau des data de segment
    // peut envoyer une exception si le segment demandé n'existe pas
    final public function segments(bool $exception=false):array
    {
        return $this->routeRequest(true)->segment(static::session(),$exception);
    }


    // segment
    // retourne une valeur de segment via la clé fournie en argument
    // peut aussi retourner un segment via index si un int est fourni
    // peut envoyer une exception si le segment demandé n'existe pas
    final public function segment($key,bool $exception=false)
    {
        $return = null;
        $segments = $this->segments($exception);

        if(is_scalar($key))
        {
            if(is_string($key) && array_key_exists($key,$segments))
            $return = $segments[$key];

            elseif(is_int($key) && Base\Arr::indexExists($key,$segments))
            $return = Base\Arr::index($key,$segments);

            else
            static::throw('doesNotExist',$key);
        }

        elseif(is_array($key))
        {
            $return = Base\Arr::gets($key,$segments);

            if(count($return) !== count($key))
            static::throw('doesNotExist');
        }

        return $return;
    }


    // hasSegment
    // retourne vrai si l'objet contient le ou les segments de requête données en argument
    final public function hasSegment(string ...$values):bool
    {
        return $this->routeRequest(true)->hasRequestSegment(...$values);
    }


    // checkSegment
    // envoie une exception si un des segments de requête n'existent pas
    final public function checkSegment(string ...$values):bool
    {
        return $this->routeRequest(true)->checkRequestSegment(...$values);
    }


    // changeSegment
    // permet de changer la valeur d'un des segments de requête de l'objet
    // un objet changé vide le tableau valid et la propriété segment de routeRequestSegment
    // l'objet route et routeSegmentRequest sont cloné
    final public function changeSegment(string $key,$value):self
    {
        return $this->changeSegments([$key=>$value]);
    }


    // changeSegments
    // permet de changer la valeur de plusieurs segments de requête de l'objet
    // un objet changé vide le tableau valid et la propriété segment de routeRequestSegment
    // l'objet route et routeSegmentRequest sont cloné
    final public function changeSegments(array $values):self
    {
        $return = $this->clone();
        $return->routeRequest(true)->changeRequestSegments($values);

        return $return;
    }


    // keepSegments
    // retourne un nouvel objet route en conservant certains segments et en ramenenant les autres à leurs valeurs par défaut
    // un objet changé vide le tableau valid et la propriété segment de routeRequestSegment
    // l'objet route et routeSegmentRequest sont cloné
    final public function keepSegments(string ...$values):self
    {
        $return = $this->clone();
        $return->routeRequest(true)->keepRequestSegments(...$values);

        return $return;
    }


    // isSegmentClass
    // retourne vrai si un chemin contient un segment
    final public static function isSegmentClass():bool
    {
        return Base\Arr::some(static::paths(),fn($path) => is_string($path) && strpos($path,'[') !== false);
    }


    // routeRequestClass
    // retourne la classe overload pour routeRequest
    final public static function routeRequestClass():string
    {
        $return = null;

        if(static::isSegmentClass())
        $return = RouteSegmentRequest::classOverload();

        else
        $return = RouteRequest::classOverload();

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


    // getDefaultSegment
    // retourne le caractère de segment par défaut
    // pourrait être null, à ce moment pas défaut de segment
    final public static function getDefaultSegment():?string
    {
        return static::$config['defaultSegment'] ?? null;
    }


    // getReplaceSegment
    // retourne le pattern utilisé pour faire un remplacement sur un segment
    // pourrait être null, à ce moment pas de possibilité de remplace dans makeSegment
    final public static function getReplaceSegment():?string
    {
        return static::$config['replaceSegment'] ?? null;
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    final public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Route':null;
    }
}
?>