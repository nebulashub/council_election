<?php
namespace App\Middleware;

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use App\Components\HttpStatusCode;
use App\Components\Utility;
use Phalcon\Http\Response\Exception as ResponseException;

/**
 * RequestMiddleware
 *
 * Check incoming payload
 */
class Request implements MiddlewareInterface
{
    protected static $_handlerPath = APP_PATH."/handlers/";

    public static $prefix = '/v1';

    protected $_app = null;

    protected $_whiteList = [];

    protected $_routers = [
        'target' =>
            [
                'info'      => ['GET'   => ['/info/{name:[0-9a-zA-Z\-]+}']],
            ],
        'targets' => 
            [
                'info'      => ['GET'   => ['/info']]
            ],
        'pledge' =>
            [
                'getTargetByAddress' => 
                    ['GET'   => 
                        ['/address/{address:[0-9a-zA-Z\-]+}/target/{target:[0-9a-zA-Z\-]+}']
                    ],
            ],
    ];

    protected $_wsRouters = [];

    public static function lookupModule($moduleName)
    {
        return file_exists(self::$_handlerPath.Utility::toCamelCase($moduleName, true));
    }

    public static function lookupHandler($handlerName, $moduleName = '')
    {
        $moduleName = Utility::toCamelCase($moduleName, true);
        $handlerName = Utility::toCamelCase($handlerName, true).'Controller';
        $class = $moduleName ? "\\App\\Handlers\\".$moduleName."\\".$handlerName : "\\App\\Handlers\\".$handlerName;
        return class_exists($class) ? $class : false;
    }

    protected function _generateCollection(Controller $controller)
    {
        $controller->setDI($this->_app->getDI());
        $collection = new MicroCollection();
        $collection->setHandler($controller);
        return $collection;
    }

    protected function _mountCollection($routers, $moduleName = '')
    {
        $urls = [];
        foreach ($routers as $key => $router) {
            if (($handler = self::lookupHandler($key, $moduleName))) {
                $collection = $this->_generateCollection(new $handler);
                foreach ($router as $action => $methodes) {
                    foreach ($methodes as $method => $rules) {
                        foreach ($rules as $rule) {
                            $collection->setPrefix(self::$prefix.($moduleName?'/'.$moduleName:'').'/'.$key);
                            $collection->{strtolower($method)}($rule, Utility::toCamelCase($action));
                            $urls[] = self::$prefix.($moduleName?'/'.$moduleName:'').'/'.$key.$rule;
                        }
                    }
                }
                $this->_app->mount($collection);
            } else if (self::lookupModule($key)) {
                $urls = array_merge($urls, $this->_mountCollection($router, $key));
            }
        }
        return $urls;
    }

    public function __construct(Micro $app) {
        $this->_app = $app;
        $this->_app->getSharedService("router")->setDefaultAction('index');
        $c = $this->_mountCollection($this->_routers);
    }

    public function beforeExecuteRoute(Event $event)
    {
        $app = $this->_app;
        $url = trim($app['router']->getRewriteUri());

        foreach ($this->_whiteList as $whiteUrl) {
            if (preg_match($whiteUrl, $url)) {
                return true;
            }
        }
        return true;
    }

    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @returns bool
     */
    public function call(Micro $application)
    {
        return true;
    }

}