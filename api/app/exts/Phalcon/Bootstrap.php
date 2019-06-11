<?php
namespace Phalcon;

use Phalcon\Mvc\Router;
use Phalcon\Config\Adapter\Yaml as ConfigYaml;
use Phalcon\Logger\Adapter\File as FileLogger;

class Bootstrap {

    const HTTP_PORT         = 8080;

    const ENV_DEV           = 'dev';
    
    const ENV_SANDBOX       = 'sandbox';
    
    const ENV_READY         = 'ready';

    const ENV_PRODUCTION    = 'production';

    private $_env           = self::ENV_PRODUCTION;

    private $_config        = null;

    private $_eventsManager = null;

    private $_dynamicServices    = [];

    public static $envs = [self::ENV_PRODUCTION, self::ENV_READY, self::ENV_SANDBOX, self::ENV_DEV];

    public function __construct(Config $config, $enviroment = self::ENV_PRODUCTION)
    {
        if (in_array($enviroment, self::$envs)) {
            $this->_env = $enviroment;
            if ($this->_env != self::ENV_PRODUCTION) {
                ini_set('display_errors', true);
                error_reporting(E_ALL);
            }
        }
        $this->_config = $config;
        $this->_eventsManager = new Events\Manager();
    }

    public function setDbServiceByConfigs(Di\FactoryDefault $di, string $name, array $config)
    {
        $logger = null;
        if (isset($config['log'])) {
            $log = (bool)$config['log'];
            unset($config['log']);
            $logger = $di->get('logger');
            if ($logger) {
                $logger->add('db_'.$name);
            }
        }
        if (empty($config['type'])) {
            $db = new Db\Adapter\Pdo\Mysql($config);
        } else {
            $dbClass = "\\Phalcon\\Db\\Adapter\\Pdo\\".ucfirst($config['type']);
            unset($config['type']);
            $db = new $dbClass($config);
        }
        $db->timeout = 300;
        $db->start = time();
        $eventsManager = new Events\Manager();
        $reconnect = function ($event, $db) use ($name, $logger) {
            $retry = false;
            $idle = time() - $db->start;

            $pdo = $db->getInternalHandler();
            if (empty($pdo)) {
                $retry = true;
            } else if ($idle > $db->timeout) {
                $retry = true;
            } else if ($event->getType() == 'reconnect') {
                try{
                    $pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
                } catch (\PDOException $e) {
                    if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                        if ($logger) {
                            $logger->call('db_'.$name, 'info', 'MySQL server has gone away');
                        }
                        $retry = true;
                    } else {
                        throw $e;
                    }
                }
            }
            if ($retry) {
                $db->close();
                $db->connect();
                $db->start = time();
            }
            return true;
        };
        //Listen all the database events
        $eventsManager->attach('db:beforeQuery', $reconnect);
        $eventsManager->attach('db:beginTransaction', $reconnect);
        $eventsManager->attach('db:reconnect', $reconnect);
        $eventsManager->attach(
            "db:beforeQuery",
            function ($event, $connection) use ($name, $logger) {
                if ($logger) {
                    go(
                        function () use ($name, $logger, $connection) {
                            $logger->call('db_'.$name, 'info', $connection->getSQLStatement());
                        }
                    );
                }
            }
        );
        $db->setEventsManager($eventsManager);
        $di->setShared('db_'.$name, $db);
        return $this;
    }

    public function getCronManager()
    {
        $configs = $this->_config->app->toArray();
        $cli = $this->buildCli();
        $manager = new \Phalcon\Crontab\Manager($cli);
        if (!isset($configs['crontab']) || !is_array($configs['crontab'])) {
            return $manager;
        }

        foreach ($configs['crontab'] as $name => $config) {
            if (isset($config['enable']) && $config['enable'] == false) {
                continue;
            }
            if (count($config) < 3) {
                if ($cli->getDI()->has('logger')) {
                    $cli->getDI()->get('logger')->get('cli')->warning("定时任务 ".$name.": 缺少参数");
                } else {
                    var_dump("定时任务 ".$name.": 缺少参数");
                }
                continue;
            }
            $manager->addJob($name, $config['schedule'], "\\App\\Cli\\".ucfirst($config['handler']), $config['action'], (isset($config['params']) ? $config['params'] : []));
            if ($cli->getDI()->has('logger') && !empty($config['log'])) {
                $logger = $cli->getDI()->get('logger');
                $logger->add('crontab_'.$name, false);
                $manager->getJob($name)->setLogger($logger->get('crontab_'.$name));
            }
        }
        return $manager;
    }

    public function buildMicro()
    {
        $micro = new \Phalcon\Mvc\Micro();
        $di = new Di\FactoryDefault();
        $di->remove('session');

        $config = $this->_config;
        $di->setShared('config', $config);

        $di->setShared(
            'cookies',
            function () {
                return new Http\Response\CookiesExt();
            }
        );
        $di->setRaw('request',  new \Phalcon\Di\Service("request",  "Phalcon\\Http\\RequestExt",  true));
        $di->setRaw('response', new \Phalcon\Di\Service("response", "Phalcon\\Http\\ResponseExt", true));
        $logConf = property_exists($config->app, 'log') ? $config->app->log : null;
        if (!empty($logConf) && !empty($logConf->path)) {
            $logger = new \Phalcon\Logger\Files($logConf->path);
            $logger->add('app');
            $di->setShared('logger', $logger);
        }

        foreach ($this->_dynamicServices as $name => $conf) {
            $di->set($name, $conf['definition'], $conf['shared']);
        }

        $micro->setDI($di);
        $micro['router']->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);
        $micro["view"] = function () {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir(APP_PATH."/views/");
            $view->disable();
            return $view;
        };
        $micro->setEventsManager(new Events\Manager);
        return $micro;
    }

    public function buildCli()
    {
        $boot = $this;
        $console = new \Phalcon\Cli\Console();
        $di = new Di\FactoryDefault\Cli();
        $config = $this->_config;
        $di->setShared('config', $config);
        $databases = $di->get('config')->database;

        $logConf = $config->app->log;
        if (!empty($logConf) && !empty($logConf->path)) {
            $logger = new \Phalcon\Logger\Files($logConf->path);
            $logger->add('cli');
            $di->setShared('logger', $logger);
        }
        if (!empty($databases)) {
            foreach ($databases as $name => $conf) {
                $boot->setDbServiceByConfigs($di, $name, $conf->toArray());
            }
        }
        foreach ($this->_dynamicServices as $name => $conf) {
            $di->set($name, $conf['definition'], $conf['shared']);
        }

        $console->setDI($di);
        return $console;
    }

    public function createHttpServer(string $host, int $port, array $config, Di\FactoryDefault $di)
    {
        $server = new \Swoole\Websocket\Server($host, $port);
        $server->set($config);

        $boot = $this;
        $server->on(
            'workerstart',
            function (\Swoole\Server $server, int $workerId) use ($boot, $di) {
                foreach ($di->get('config')->database as $name => $conf) {
                    $boot->setDbServiceByConfigs($di, $name, $conf->toArray());
                }
                $di->setShared(
                    'session',
                    function () {
                        return new \Phalcon\Session\Adapter\Files();
                    }
                );
            }
        );
        $server->on(
            'message',
            function (swoole_websocket_server $server, swoole_websocket_frame $frame) {
            }
        );
        return $server;
    }

    public function addService(string $name, object $service, $shared = true)
    {
        $this->_dynamicServices[$name] = ['definition' => $service, 'shared' => $shared];
        return $this;
    }

    public function getEventsManager()
    {
        return $this->_eventsManager;
    }
}
