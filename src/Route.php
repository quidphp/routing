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

// route
// abstract class for a route that acts as both a View and a Controller
abstract class Route extends Main\Root implements Main\Contract\Meta
{
    // trait
    use _segment;


    // config
    public static $config = [
        'path'=>'undefined', // match path de la route, peut y avoir plusieurs, si il y a clé c'est une lang
        'match'=>[ // vérification lancé pour trouver le match
            'ssl'=>null, // ssl n'a pas d'importance pour le match, unique à match
            'ajax'=>null, // ajax n'a pas d'importance pour le match, unique à match
            'host'=>null, // tous les hosts sont acceptés, unique à match
            'method'=>null, // toutes les méthodes sont acceptées, unique à match
            'query'=>null, // validation sur contenu de query
            'post'=>null, // validation sur contenu de post
            'genuine'=>null, // validation que le champ genuine est vide
            'header'=>null, // validation sur le tableau des headers
            'lang'=>null, // toutes les langs sont acceptés
            'ip'=>null, // tous les ip sont acceptés
            'browser'=>null, // tous les browsers sont acceptés
            'session'=>null, // validation sur contenu de session
            'role'=>null, // validation du code ou de la classe de permission
            'csrf'=>null, // validation que le champ csrf et le même que dans session
            'captcha'=>null, // validation que le champ captcha est le même que dans session
            'timeout'=>null], // défini les timeouts à vérifier
        'verify'=>[ // vérification lancé après le match
            'query'=>null, // validation sur contenu de query
            'post'=>null, // validation sur contenu de post
            'genuine'=>null, // validation que post contient la clé genuine et que le contenu est vide
            'header'=>null, // validation sur le tableau des headers
            'lang'=>null, // toutes les langs sont acceptés
            'ip'=>null, // tous les ip sont acceptés
            'browser'=>null, // tous les browsers sont acceptés
            'session'=>null, // validation sur contenu de session
            'role'=>null, // validation du code ou de la classe de permission
            'csrf'=>null, // validation que le champ csrf et le même que dans session
            'captcha'=>null, // validation que le champ captcha et le même que dans session
            'timeout'=>null], // défini les timeouts à vérifier
        'response'=>[
            'timeLimit'=>null, // limit de temps pour la route
            'code'=>200, // code de réponse
            'contentType'=>'html', // contentType de la réponse
            'header'=>null], // tableau de header à sets à la réponse
        'timeout'=>null, // défini les timeouts à lier à la route trigger
        'query'=>null, // détermine les éléments de query conservés dans la route
        'replace'=>[ // permet de spécifier des callbacks pour les valeurs du tableau de remplacement
            'title'=>[Base\Html::class,'titleValue'],
            'metaTitle'=>[Base\Html::class,'titleValue'],
            'metaDescription'=>[Base\Html::class,'metaDescriptionValue'],
            'metaKeywords'=>[Base\Html::class,'metaKeywordsValue'],
            'metaUri'=>[Base\Html::class,'metaUriValue'],
            'bodyClass'=>[Base\Attr::class,'prepareClass'],
            'bodyStyle'=>[Base\Style::class,'str']],
        'docOpen'=>[ // utilisé pour l'ouverture du document
            'html'=>['lang'=>'%lang%','data-route'=>'%name%'],
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
            'body'=>['%name%','%parent%','%group%','%bodyClass%','style'=>'%bodyStyle%'],
            'wrapper'=>true],
        'docClose'=>[ // utilisé pour la fermeture du document
            'wrapper'=>true,
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
        'uri'=>['absolute'=>false], // attribut pour output, relative et absolut
        'parent'=>null, // classe parente de la route
        'priority'=>0, // priorité de la route
        'navigation'=>true, // active la navigation via history sur la route
        'group'=>'none', // groupe spécifique de la route, comme home ou error
        'menu'=>null, // détermine si la route fait partie d'un ou plusieurs menus
        'history'=>true, // la requête est ajouté à l'historique de session
        'ignore'=>false, // si la route est ignoré pour routes
        'catchAll'=>false, // si true, le dernier segment attrape tout le reste du chemin dans le processus de match
        'debug'=>false, // active ou non le débogagge de match, en lien avec la méthode statique debug
        'defaultSegment'=>'-', // caractère pour un segment avec valeur par défaut
        'replaceSegment'=>'%%%', // pattern utilisé pour faire un remplacement sur un segment, cette valeur passe dans makSegment à tout coup
        'segment'=>[] // tableau qui permet de remplacer une clé de segment par un autre, utiliser dans methodSegment
    ];


    // debug
    public static $debug = []; // permet de débogger le match des routes


    // dynamique
    protected $routeRequest = null; // variable qui contient l'objet routeRequest
    protected $trigger = false; // garde en mémoire si la route est trigger ou non


    // construct
    // construit l'objet route
    public function __construct($request=null)
    {
        $this->setRouteRequest($request);
        $this->onMake();

        return;
    }


    // getBaseReplace
    // retourne le tableau de remplacement de base
    abstract public function getBaseReplace():array;


    // prepareTitle
    // prépare le titre après le onReplace
    abstract protected function prepareTitle($return,array $array):array;


    // context
    // retourne le tableau de contexte de la classe
    abstract public function context(?array $option=null):array;


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
    // retourne l'objet routes de boot ou un nom de classe de route contenu dans l'objet
    abstract public static function routes(bool $active=false,$get=null);


    // lang
    // retourne l'objet lang
    abstract public static function lang():Main\Lang;


    // session
    // retourne l'objet session
    abstract public static function session():Main\Session;


    // services
    // retourne l'objet services
    abstract public static function services():Main\Services;


    // toString
    // retourne la valeur de la route sous forme de string
    public function __toString():string
    {
        return static::name();
    }


    // cast
    // retourne la valeur cast
    public function _cast():string
    {
        return $this->uri();
    }


    // onMake
    // permet d'avoir un callback lors de la construction de la route
    // méthode protégé
    protected function onMake():void
    {
        return;
    }


    // onBefore
    // méthode appelé au début de la méthode before
    // possible d'arrêter la route si onBefore retourne faux
    // méthode protégé
    protected function onBefore()
    {
        return;
    }


    // onAfter
    // méthode appelé à la fin de la méthode after
    // méthode protégé
    protected function onAfter()
    {
        return;
    }


    // onFallbackRedirect
    // méthode appelé lorsqu'il y a une route de redirection lors du fallback
    // permet par exemple de flashPost
    // méthode protégé
    protected function onFallbackRedirect()
    {
        return;
    }


    // onReplace
    // méthode à étendre pour changer le tableau de remplacement pour une route
    // méthode protégé
    protected function onReplace(array $return):array
    {
        return $return;
    }


    // onPrepareDoc
    // pour changer le tableau de prepareDoc, peut être étendu
    // méthode protégé
    protected function onPrepareDoc(string $type,array $return):array
    {
        if($type === 'docClose')
        $return = $this->prepareDocJsInit($return);

        $return = $this->prepareDocServices($type,$return);

        return $return;
    }


    // prepareDocJsInit
    // ajoute la méthode jsInit si jsInit est true et que ce n'est pas une requête ajax
    protected function prepareDocJsInit(array $return):array
    {
        if(!empty(static::$config['jsInit']) && $this->request()->isAjax() === false)
        {
            $callable = null;
            $jsInit = static::$config['jsInit'];

            if(static::classIsCallable($jsInit))
            $callable = $jsInit;
            else
            $callable = function() use($jsInit) {
                return $jsInit;
            };

            $return['script'] = Base\Arr::append($return['script'],[$callable]);
        }

        return $return;
    }


    // prepareDocServices
    // méthode utilisé après prepareDoc, lie les tags de services pour docOpen et docClose
    // si un des éléments est false dans le tableau de config, à ce moment n'append pas le service (ça vaut dire que la route n'a pas de js/css/script)
    // méthode protégé
    protected function prepareDocServices(string $type,array $return):array
    {
        $services = static::services();

        foreach ($services as $service)
        {
            $key = $service->getKey();

            if($type === 'docOpen')
            {
                $return['head']['js'] = $return['head']['js'] ?? null;
                if($return['head']['js'] !== false)
                {
                    $js = $service->docOpenJs();
                    if(!empty($js))
                    {
                        $append = (is_array($js))? $js:[$key=>$js];
                        $return['head']['js'] = Base\Arr::append($return['head']['js'] ?? [],$append);
                    }
                }

                $return['head']['script'] = $return['head']['script'] ?? null;
                if($return['head']['script'] !== false)
                {
                    $script = $service->docOpenScript();
                    if(!empty($script))
                    $return['head']['script'] = Base\Arr::append($return['head']['script'] ?? [],$script);
                }
            }

            elseif($type === 'docClose')
            {
                $return['script'] = $return['script'] ?? null;
                if($return['script'] !== false)
                {
                    $script = $service->docCloseScript();
                    if(!empty($script))
                    $return['script'] = Base\Arr::append($return['script'] ?? [],$script);
                }
            }
        }

        return $return;
    }


    // isTriggered
    // retourne vrai si la route est présentement triggé
    public function isTriggered():bool
    {
        return ($this->trigger === true)? true:false;
    }


    // allowed
    // retourne vrai si le role de la session courante permet d'accéder à la route
    // se base seulement sur match, pas verify
    public static function allowed(?Main\Role $role=null):bool
    {
        $return = false;
        $value = static::$config['match']['role'] ?? null;
        $class = static::routeRequestClass();

        if(empty($role))
        $role = static::session()->role();

        $return = $class::allowed($value,$role);

        return $return;
    }


    // getTimeoutObject
    // retourne l'objet timeout
    public static function getTimeoutObject():Main\Timeout
    {
        return static::session()->timeout();
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


    // run
    // lance tout le processus de lancement et output de la route
    // retourne un tableau avec bool, continue et output
    // possible de echo le output si echo est true
    public function run(bool $echo=false):array
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
                $bool = true;

                if($echo === true)
                static::echoOutput($output);
            }
        }

        catch (Exception $e)
        {
            $e->onCatched();
            $continue = true;
        }

        catch (BreakException $e)
        {
            Base\Response::serverError();
            $e->onCatched();
        }

        $return['bool'] = $bool;
        $return['continue'] = $continue;
        $return['output'] = $output;

        return $return;
    }


    // fallback
    // méthode lancé après before si le test verify a échoué
    // gère le timeout, captcha, csrf, genuine et failedFileUpload
    // s'il y a redirection utilise le code 302
    // méthode protégé
    protected function fallback($context=null)
    {
        $code = null;
        $log = null;

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
            $log = 'failedFileUpload';
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

        $redirect = $this->fallbackRouteRedirect($context);
        if(!empty($redirect))
        {
            $this->onFallbackRedirect();
            $this->processRedirect($redirect,false);
        }

        return false;
    }


    // fallbackRouteRedirect
    // retourne la route à rediriger dans le cas d'un fallback
    public function fallbackRouteRedirect($context=null)
    {
        return;
    }


    // setRouteRequest
    // change la routeRequest de l'objet
    // méthode protégé
    protected function setRouteRequest($request=null):self
    {
        $this->routeRequest = static::makeRouteRequest($request);

        return $this;
    }


    // routeRequest
    // retourne l'objet routeRequest
    public function routeRequest():RouteRequest
    {
        return $this->routeRequest;
    }


    // request
    // retourne l'objet request de routeRequest
    public function request():Main\Request
    {
        return $this->routeRequest()->request();
    }


    // getFallbackContext
    // retourne la raison du fallback
    public function getFallbackContext()
    {
        return $this->routeRequest()->fallback();
    }


    // selectedUri
    // retourne les uris supplémentaires qui doivent être marqués comme sélectionnés
    public function selectedUri():array
    {
        return [];
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
    public function init(bool $exception=false):self
    {
        $this->isValid($exception);

        return $this;
    }


    // isValid
    // retourne vrai si la route et la requête match et verify
    public function isValid(bool $exception=false):bool
    {
        return $this->routeRequest()->isValid(static::session(),$exception);
    }


    // checkValid
    // envoie une exception si la route et la requête ne passent pas les tests match et verify
    // si valid est false, le test n'est pas lancé et utilise le résultat courant
    public function checkValid(bool $valid=true):self
    {
        if($valid === true)
        $this->isValid(true);

        $this->routeRequest()->checkValid();

        return $this;
    }


    // start
    // lance la route
    // retourne le résultat de la route
    public function start()
    {
        $return = null;

        if(!$this->isValid())
        {
            $fallback = $this->getFallbackContext();
            $return = $this->fallback($fallback);
        }

        else
        $return = $this->launch();

        return $return;
    }


    // startEcho
    // lance la route
    // echo le résultat de la route
    // possible de tuer si kill est true
    public function startEcho(bool $kill=false)
    {
        $return = $this->start();
        static::echoOutput($return);

        if($kill === true)
        Base\Response::kill();

        return $return;
    }


    // triggerEcho
    // trigge la route
    // echo le résultat de la route
    // possible de tuer si kill est true
    public function triggerEcho(bool $kill=false)
    {
        $return = $this->trigger();
        static::echoOutput($return);

        if($kill === true)
        Base\Response::kill();

        return $return;
    }


    // launch
    // lance la route, ne fait pas le test isValid
    // retourne le résultat de la route
    public function launch()
    {
        $return = null;
        static::prepareTimeout();

        try
        {
            $return = $this->before();

            if($return !== false)
            {
                $timeout = static::timeout();
                if(array_key_exists('trigger',$timeout))
                static::timeoutIncrement('trigger');

                $this->trigger = true;
                $return = $this->trigger();

                if($return !== false)
                $this->after();
            }

            else
            $return = $this->fallback('before');
        }

        catch (Main\Contract\Catchable $e)
        {
            $e->onCatched();
            $return = $this->fallback($e);
        }

        return $return;
    }


    // before
    // avant la méthode trigger
    // appele onBefore, possible d'arrêter la route si onBefore retourne faux
    // refresh le csrf si il a été validé
    // met la uri selected
    // méthode protégé
    protected function before()
    {
        $return = $this->onBefore();

        if($return !== false)
        {
            $session = static::session();
            $response = static::$config['response'] ?? [];

            if(array_key_exists('timeLimit',$response) && is_int($response['timeLimit']))
            Base\Response::timeLimit($response['timeLimit']);

            if(static::hasCheck('csrf'))
            $session->refreshCsrf();

            if(static::hasCheck('captcha'))
            $session->emptyCaptcha();

            if($this->hasUri())
            {
                $selected = $this->selectedUri();
                $uri = $this->uri();
                $selected[$uri] = true;
                Base\Attr::addSelectedUri($selected);
            }
        }

        return $return;
    }


    // after
    // après la méthode trigger
    // met le code response, le contentType et des headers de response et ajoute la requête à l'historique
    // après le onAfter, vérifie s'il y a une route de spécifier dans afterRouteRedirect, si oui redirige (avec 301) et tue
    // méthode protégé
    protected function after():self
    {
        $response = static::$config['response'] ?? [];
        static::setResponseCode();

        if(!empty($response['contentType']) && is_string($response['contentType']))
        Base\Response::setContentType($response['contentType']);

        if(!empty($response['header']) && is_array($response['header']))
        Base\Response::setsHeader($response['header']);

        if(static::shouldKeepInHistory())
        {
            $history = static::$config['history'];
            $method = ($history === 'unique')? 'addUnique':'add';
            $request = $this->request();
            $history = static::session()->history();
            $history->$method($request);
        }

        $this->onAfter();

        $redirect = $this->afterRouteRedirect();
        if(!empty($redirect))
        $this->processRedirect($redirect,true);

        return $this;
    }


    // afterRouteRedirect
    // retourne la route à rediriger dans le onAfter
    public function afterRouteRedirect()
    {
        return;
    }


    // processRedirect
    // gère un redirect, par exemple pour after ou fallback
    // méthode protégé
    protected function processRedirect($value,$code=null,bool $kill=true):void
    {
        if(is_string($value))
        Base\Response::redirect($value,$code,$kill);

        elseif($value === true)
        Base\Response::redirectReferer(true,true,$code,$kill);

        elseif($value instanceof self)
        $value->redirect($code,$kill);

        return;
    }


    // getMetaFromContract
    // retourne un tableau avec les méta données pour un objet ayant l'interface meta
    // méthode protégé
    protected function getMetaFromContract(Main\Contract\Meta $meta,array $return):array
    {
        $array = [];

        $array['title'] = $meta->getMetaTitle($return['title'] ?? null);
        $array['metaKeywords'] = $meta->getMetaKeywords($return['metaKeywords'] ?? null);
        $array['metaDescription'] = $meta->getMetaDescription($return['metaDescription'] ?? null);
        $array['metaImage'] = $meta->getMetaImage($return['metaImage'] ?? null);
        $array['bodyClass'] = $meta->getBodyClass($return['bodyClass'] ?? null);
        $array['bodyStyle'] = $meta->getBodyStyle($return['bodyStyle'] ?? null);

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


    // getBodyClass
    // retourne les données pour la classe de body
    public function getBodyClass($value=null)
    {
        return;
    }


    // getBodyStyle
    // retourne les données pour le style de body
    public function getBodyStyle($value=null)
    {
        return;
    }


    // label
    // retourne le label de la route non triggé
    public static function label($pattern=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = static::lang();
        $path = static::$config['label'] ?? null;
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);

        if(!empty($path))
        $return = $obj->same($path,null,$lang,$option);
        else
        $return = $obj->routeLabel(static::name(),$lang,$option);

        return $return;
    }


    // description
    // retourne la description de la route non triggé
    public static function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = static::lang();
        $path = static::$config['description'] ?? null;
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);

        if(!empty($path))
        $return = $obj->same($path,$replace,$lang,$option);
        else
        $return = $obj->routeDescription(static::name(),$replace,$lang,$option);

        return $return;
    }


    // title
    // retourne le titre de la route triggé
    public function title($pattern=null,?string $lang=null,?array $option=null):?string
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
    public function docOpen(bool $default=true,?string $separator=null):string
    {
        return Base\Html::docOpen($this->prepareDoc('docOpen'),$default,$separator);
    }


    // docClose
    // génère la fermeture du document en html
    public function docClose(bool $default=true,bool $closeBody=true,?string $separator=null):string
    {
        return Base\Html::docClose($this->prepareDoc('docClose'),$default,$closeBody,$separator);
    }


    // getReplace
    // retourne le tableau de remplacement utilisé par docOpen et docClose
    public function getReplace():array
    {
        $return = $this->getBaseReplace();
        $return = $this->getMetaFromContract($this,$return);

        $otherMeta = $this->getOtherMeta();
        if(!empty($otherMeta))
        $return = $this->getMetaFromContract($otherMeta,$return);

        $return = $this->onReplace($return);
        $return['title'] = $this->prepareTitle($return['title'] ?? null,$return);

        $return = Base\Obj::cast($return);

        $replace = static::$config['replace'] ?? null;
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
    // méthode protégé
    protected function prepareDoc(string $type):array
    {
        $return = [];

        if(in_array($type,['docOpen','docClose'],true))
        {
            $doc = static::$config[$type] ?? null;

            if(is_array($doc))
            {
                $return = $doc;
                $replace = $this->getReplace($type);

                if(!empty($replace))
                {
                    $replace = Base\Arr::keysWrap('%','%',$replace);
                    $return = Base\Arrs::valuesReplace($replace,$return);
                }
            }

            $return = $this->onPrepareDoc($type,$return);
            $return = Base\Call::digStaticMethod($return);
        }

        else
        static::throw();

        return $return;
    }


    // hasUri
    // retourne vrai si la route peut générer une uri pour la langue
    public function hasUri(?string $lang=null,?array $option=null):bool
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
    // méthode protégé
    protected function uriMethod(string $method,?string $lang=null,?array $option=null):string
    {
        $return = '';
        $lang = ($lang === null)? static::session()->lang():$lang;
        $option = Base\Arr::plus($option,static::$config['uri'] ?? null);
        $return = $this->routeRequest()->$method($lang,$option);

        if(!is_string($return))
        static::throw('impossibleToMakeUri');

        return $return;
    }


    // uri
    // retourne l'uri pour l'objet route
    public function uri(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uri',$lang,$option);
    }


    // uriOutput
    // retourne l'uri formatté pour l'objet route
    // l'uri peut être relative ou absolut dépendamment des options
    public function uriOutput(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uriOutput',$lang,$option);
    }


    // uriRelative
    // retourne l'uri relative pour l'objet route
    public function uriRelative(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uriRelative',$lang,$option);
    }


    // uriAbsolute
    // retourne l'uri absolut pour l'objet route
    public function uriAbsolute(?string $lang=null,?array $option=null):string
    {
        return $this->uriMethod('uriAbsolute',$lang,$option);
    }


    // redirect
    // redirige la réponse courante vers l'uri absolute de la route
    public function redirect($code=null,$kill=true,?string $lang=null,bool $encode=true,?array $option=null):bool
    {
        return Base\Response::redirect($this->uriAbsolute($lang,$option),$code,$kill,$encode);
    }


    // isSelected
    // retourne vrai si l'uri de la route est sélectionné, tel que défini dans base/attr
    public function isSelected(?string $lang=null,?array $option=null):bool
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
    public function a($title=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $uri = $this->uri($lang,$option);
        $return = Base\Html::a($uri,$title,static::tagAttr('a',$attr),static::tagOption('a',$option));

        return $return;
    }


    // aOpen
    // ouvre un a tag pour la route
    // possible de spécifier des attr et option par défaut pour a dans static config
    // les options sont pour base/html a
    public function aOpen($title=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $uri = $this->uri($lang,$option);
        $return = Base\Html::aOpen($uri,$title,static::tagAttr('a',$attr),static::tagOption('a',$option));

        return $return;
    }


    // aLabel
    // génère un a tag pour la route, le label sera affiché
    // possible de spécifier un pattern de label
    public function aLabel($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->a(static::label($pattern,$lang),$attr,$lang,$option);
    }


    // aOpenLabel
    // ouvre un a tag pour la route, le label sera affiché
    // possible de spécifier un pattern de label
    public function aOpenLabel($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->aOpen(static::label($pattern,$lang),$attr,$lang,$option);
    }


    // aTitle
    // génère un a tag pour la route, le title sera affiché
    // possible de spécifier un pattern de title
    public function aTitle($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->a($this->title($pattern,$lang),$attr,$lang,$option);
    }


    // aOpenTitle
    // ouvre un a tag pour la route, le title sera affiché
    // possible de spécifier un pattern de title
    public function aOpenTitle($pattern=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        return $this->aOpen($this->title($pattern,$lang),$attr,$lang,$option);
    }


    // formOpen
    // ouvre un form tag pour la route
    // les options sont pour base/html formOpen
    public function formOpen($attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $uri = $this->uri($lang,$option);
        $attr = static::tagAttr('form',$attr);

        if(empty($attr['method']))
        {
            $method = static::$config['match']['method'] ?? static::$config['form']['method'] ?? null;
            if(!empty($method))
            $attr['method'] = $method;
        }

        $return = Base\Html::formOpen($uri,$attr,static::tagOption('form',$option));

        return $return;
    }


    // formSubmit
    // ouvre et ferme un formulaire avec un bouton submit sans label ou titre
    public function formSubmit($title=null,$submitAttr=null,$attr=null,?string $lang=null,?array $option=null):?string
    {
        $return = $this->formOpen($attr,$lang,$option);
        $return .= Base\Html::submit($title,$submitAttr);
        $return .= Base\Html::formClose();

        return $return;
    }


    // submitLabel
    // fait un tag submit avec label pour soumettre le formulaire
    // méthode statique
    public static function submitLabel($pattern=null,$attr=null,?string $lang=null):?string
    {
        return Base\Html::submit(static::label($pattern,$lang),$attr);
    }


    // submitTitle
    // fait un tag submit avec title pour soumettre le formulaire
    public function submitTitle($pattern=null,$attr=null,?string $lang=null):?string
    {
        return Base\Html::submit($this->title($pattern,$lang),$attr);
    }


    // childs
    // retourne toutes les enfants de la route courante
    public static function childs(bool $active=false):Routes
    {
        return static::routes($active)->childs(static::class);
    }


    // make
    // construit une instance de la route de façon statique
    public static function make($request=null,bool $overload=false):self
    {
        $class = ($overload === true)? static::getOverloadClass():static::class;
        $return = new $class($request);

        return $return;
    }


    // makeOverload
    // construit une instance de la route de façon statique
    // overload est true
    public static function makeOverload($request=null):self
    {
        return static::make($request,true);
    }


    // makeParent
    // retourne une instance la route parente
    // envoie une exception s'il n'y a pas de parent valide
    public static function makeParent($request=null,bool $overload=false):self
    {
        $return = null;
        $parent = static::parent();
        $target = current(static::routeBaseClasses());

        if(empty($parent) || !is_subclass_of($parent,$target,true))
        static::throw('invalidParent');

        $return = $parent::make($request,$overload);

        return $return;
    }


    // makeParentOverload
    // comme makeParent mais overload est à true
    public static function makeParentOverload($request=null):self
    {
        return static::makeParent($request,true);
    }


    // isIgnored
    // retourne vrai si la route est ignoré
    public static function isIgnored():bool
    {
        return ((static::$config['ignore'] ?? null) === true)? true:false;
    }


    // inMenu
    // retourne vrai si la route fait partie d'un menu donné en argument
    public static function inMenu(string $value):bool
    {
        $return = false;
        $menus = (array) (static::$config['menu'] ?? null);

        if(in_array($value,$menus,true))
        $return = true;

        return $return;
    }


    // isActive
    // retourne vrai si la route est active, elle n'est pas ignoré et le role possède la permission d'accès
    public static function isActive(?Main\Role $role=null):bool
    {
        return (!static::isIgnored() && static::allowed($role))? true:false;
    }


    // isGroup
    // retourne vrai si le groupe est celui spécifié
    public static function isGroup($value):bool
    {
        return ($value === static::group())? true:false;
    }


    // inSitemap
    // retourne vrai si la route fait partie de sitemap
    public static function inSitemap(?Role $role=null):bool
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
    public static function allowNavigation():bool
    {
        $return = static::$config['navigation'] ?? null;

        if(!is_bool($return))
        $return = false;

        return $return;
    }


    // setType
    // change le type de la route
    public static function setType(string $value,bool $dig=false):void
    {
        static::$config['type'] = $value;

        if($dig === true)
        {
            $parent = get_parent_class(static::class);
            if(!empty($parent) && !in_array($parent,static::routeBaseClasses(),true))
            $parent::setType($value,$dig);
        }

        return;
    }


    // group
    // retourne le group de la route, si existant
    // si notNone est true, ne retourne pas le nom de group si none
    public static function group(bool $notNone=false):?string
    {
        $return = static::$config['group'] ?? null;

        if($notNone === true && static::isGroup('none'))
        $return = null;

        return $return;
    }


    // name
    // retourne le nom de la route
    // est toujours le nom de la classe avec la première lettre lower case
    public static function name():string
    {
        return static::className(true);
    }


    // priority
    // retourne la priorité de la reoute
    public static function priority():int
    {
        return static::$config['priority'] ?? 0;
    }


    // setPriority
    // change la priorité de la route
    public static function setPriority(int $value):void
    {
        static::$config['priority'] = $value;

        return;
    }


    // parent
    // retourne la classe parente de la route
    public static function parent():?string
    {
        return static::$config['parent'] ?? null;
    }


    // setParent
    // change le parent de la route
    public static function setParent(string $value):void
    {
        $target = current(static::routeBaseClasses());

        if(is_subclass_of($value,$target,true))
        static::$config['parent'] = $value;

        else
        static::throw('invalidParentClass');

        return;
    }


    // hasPath
    // retourne vrai si la route a un path pour la langue
    public static function hasPath(?string $lang=null):bool
    {
        $return = false;
        $lang = ($lang === null)? static::session()->lang():$lang;
        $path = static::path($lang);

        if($path !== null && $path !== false)
        $return = true;

        return $return;
    }


    // paths
    // retourne tous les chemins de la route
    public static function paths():array
    {
        $return = static::$config['path'] ?? null;

        if(!is_array($return))
        $return = [$return];

        return $return;
    }


    // path
    // retourne le path de la route
    // si une lang est fourni, retourne le path compatible avec la langue
    public static function path(?string $lang=null,bool $null=false)
    {
        $return = false;
        $found = false;
        $path = static::paths();

        if(is_string($lang) && array_key_exists($lang,$path))
        $return = $path[$lang];

        else
        {
            foreach ($path as $key => $value)
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


    // isSsl
    // retourne la valeur ssl de match
    // peut retourner null
    public static function isSsl():?bool
    {
        return static::$config['match']['ssl'] ?? null;
    }


    // isAjax
    // retourne la valeur ajax de match
    // peut retourner null
    public static function isAjax():?bool
    {
        return static::$config['match']['ajax'] ?? null;
    }


    // isMethod
    // retourne vrai si la route utilise la méthode donné en argument
    public static function isMethod($value):bool
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
                $method = array_map('strtolower',$method);

                if(in_array($value,$method,true))
                $return = true;
            }
        }

        return $return;
    }


    // isRedirectable
    // retourne vrai si la route est redirigable
    // c'est à dire pas ignore, ni post, ni ajax, ni error, ni sitemap
    public static function isRedirectable(?Main\Role $role=null):bool
    {
        $return = false;
        $isRedirectable = static::$config['redirectable'] ?? null;
        $isSitemap = (static::name() === 'sitemap')? true:false;

        if($isRedirectable !== false && static::isActive($role))
        $return = (static::isGroup('error') || $isSitemap || static::isMethod('post') || static::isAjax())? false:true;

        return $return;
    }


    // shouldKeepInHistory
    // retourne vrai si la route devrait être gardé dasn l'history
    public static function shouldKeepInHistory():bool
    {
        return (!empty(static::$config['history']))? true:false;
    }


    // hasCheck
    // permet de vérifier si un élément de validation de la route se retrouve dans match ou verify
    public static function hasCheck(string $type):bool
    {
        $return = false;
        $match = static::$config['match'] ?? [];
        $verify = static::$config['verify'] ?? [];

        if(!empty($match[$type]) || !empty($verify[$type]))
        $return = true;

        return $return;
    }


    // timeout
    // retourne le tableau de timeout pour la route
    public static function timeout():array
    {
        return static::$config['timeout'] ?? [];
    }


    // prepareTimeout
    // ajoute les timeout définis dans la route dans l'objet timeout de la session
    public static function prepareTimeout():Main\Timeout
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
    protected static function makeTimeoutKey(string $key):array
    {
        return [static::class,$key];
    }


    // timeoutMethod
    // méthode protégé, fait appel à une méthode l'objet timeout
    protected static function timeoutMethod(string $method,$key)
    {
        $timeout = static::getTimeoutObject();
        $key = static::makeTimeoutKey($key);
        $return = $timeout->$method($key);

        return $return;
    }


    // isTimedOut
    // retourne vrai si l'entrée est timedOut
    public static function isTimedOut($key):bool
    {
        return static::timeoutMethod('isTimedOut',$key);
    }


    // timeoutGet
    // retourne le count d'une entrée dans l'objet de timeout
    public static function timeoutGet($key):?int
    {
        return static::timeoutMethod('getCount',$key);
    }


    // timeoutIncrement
    // increment le count de l'entrée dans l'objet timeout
    public static function timeoutIncrement($key):Main\Timeout
    {
        return static::timeoutMethod('increment',$key);
    }


    // timeoutBlock
    // met le maximum comme count de l'entrée dans l'objet timeout
    public static function timeoutBlock($key):Main\Timeout
    {
        return static::timeoutMethod('block',$key);
    }


    // timeoutReset
    // reset le count de l'entrée dans l'objet timeout
    public static function timeoutReset($key):Main\Timeout
    {
        return static::timeoutMethod('resetCount',$key);
    }


    // timeoutStamp
    // met le timestamp actuel à une entrée dans l'objet timeout
    public static function timeoutStamp($key):Main\Timeout
    {
        return static::timeoutMethod('setTimestamp',$key);
    }


    // tagAttr
    // retourne un tableau contenant les attributs à utiliser pour une tag
    public static function tagAttr(string $tag,$attr=null):?array
    {
        $return = null;

        if(array_key_exists($tag,static::$config))
        {
            if(!is_array($attr))
            $attr = [$attr];

            if(!empty(static::$config[$tag]['attr']))
            $return = Base\Attr::append(static::$config[$tag]['attr'],$attr);

            else
            $return = $attr;
        }

        else
        static::throw('tagNotDefined');

        if($tag === 'a' && empty(static::allowNavigation()))
        {
            $return = (array) $return;
            $return[] = 'http';
        }

        return $return;
    }


    // tagOption
    // retourne un tableau contenant les options à utiliser pour une tag
    public static function tagOption(string $tag,?array $option=null):?array
    {
        $return = null;

        if(array_key_exists($tag,static::$config))
        $return = (!empty(static::$config[$tag]['option']))? Base\Arr::plus(static::$config[$tag]['option'],$option):$option;

        else
        static::throw('tagNotDefined');

        return $return;
    }


    // setResponseCode
    // méthode qui permet d'appliquer le code de réponse de la route, tel que spécifié dans static config
    public static function setResponseCode():void
    {
        $response = static::$config['response'] ?? [];

        if(!Base\Response::isCodeError() && !empty($response['code']) && is_int($response['code']))
        Base\Response::setCode($response['code']);

        return;
    }


    // echoOutput
    // output les données qui sortent d'une route
    // le output est flush, méthode protégé
    protected static function echoOutput($value):string
    {
        $return = '';

        if(is_object($value) || is_resource($value))
        static::throw('notAllowed');

        Base\Buffer::startEchoEndFlushAllStart($value);

        return $return;
    }


    // routeBaseClasses
    // retourne les classes bases de routes (donc abstraite)
    public static function routeBaseClasses():array
    {
        return [self::class];
    }


    // isDebug
    // retourne vrai si la route est en mode débogagge
    public static function isDebug($value=null):bool
    {
        return (static::$config['debug'] === true || ($value !== null && static::$config['debug'] === $value))? true:false;
    }


    // debugStore
    // permet de débogger le processus de match
    public static function debugStore(...$args):void
    {
        if(static::isDebug(1))
        {
            $args = Base\Obj::cast($args);
            static::$debug[static::class][] = $args;
        }

        return;
    }


    // debugDead
    // dump les données debug de la route et tue la requête
    // possible aussi de output tout (pas seulement la route courante)
    public static function debugDead(bool $all=false):void
    {
        if($all === true)
        $array = static::$debug;

        else
        {
            $array = static::$debug[static::class] ?? [];
            $array[] = static::class;
            $array = array_reverse($array);
        }

        Base\Debug::dead($array);

        return;
    }
}
?>