<?php
require_once realpath(dirname(__FILE__))."/bootstrap.php";
require_once APP_PATH."/../vendor/autoload.php";

use App\Components\User as UserComponent;
use App\Components\WebSocket;
use Nebulas\Rpc\HttpProvider;
use Nebulas\Rpc\Neb;

$app = $boot->buildMicro();
$di = $app->getDI();

$responseMiddleware = new App\Middleware\Response();
$em = $app->getEventsManager();
$em->attach('micro:beforeNotFound', new App\Middleware\NotFound());
$em->attach('micro', new App\Middleware\Request($app));
$em->attach('micro', $responseMiddleware);
$app->after($responseMiddleware);

try {
    $di->setShared('neb', new Neb(new HttpProvider($di->get('config')->neb->provider)));
    $http = $boot->createHttpServer(
        "0.0.0.0", 8080,
        [
            'document_root' => realpath(DOCUMENT_ROOT),
            'enable_static_handler' => true,
            'daemonize'=> false,
            'package_max_length' => 3145728,
            'dispatch_mode' => 3,
            'log_level' => 5,
            'socket_buffer_size' => 128 * 1024 *1024,
            'send_yield' => true
        ], $di
    );
} catch (\Throwable $e) {
    var_dump($e->getMessage());
    exit;
}

$http->on(
    'request',
    function (swoole_http_request $request, swoole_http_response $response) use ($app, $di, $responseMiddleware) {
        try {
            foreach ($di->getServices() as $service) {
                if ($service->getDefinition() instanceof \Phalcon\Db\Adapter) {
                    $di->get($service->getName())->getEventsManager()->fire('db:reconnect', $service->getDefinition());
                }
            }
            $app->request->setSwooleRequest($request);
            $app->response->setSwooleResponse($response)->reset()->setContentType("text/html", "UTF-8");
            $app->session->start();
            if (empty($_COOKIE['SESSION_ID'])) {
                $app->cookies->set('SESSION_ID', $app->session->getId(), 0, '/', false, "", true);
            }
            $di->set(
                'user',
                function () {
                    return (new UserComponent($this));
                }
            );
            $app->response->setHeader("Access-Control-Allow-Origin", '*');
            $app->handle($request->server['request_uri']);
        } catch (\Throwable $e) {
            if ($di->has('logger')) {
                $di->get('logger')->get('app')->error($request->server['request_uri'].': '.$e->getMessage());
            }
            $responseMiddleware->handleException($app, $e);
        }
        if (!$app->response->isDetach()) {
            $app->response->send();
        }
    }
);

$cronManager = $boot->getCronManager();
if ($isCrontab && $cronManager->hasJobs()) {
    $crontab = new \Swoole\Process(
        function ($process) use ($cronManager, $boot, $di, $config) {
            foreach ($config->database as $name => $conf) {
                $boot->setDbServiceByConfigs($di, $name, (array)$conf);
            }
            $cronManager->log('crontab start');
            swoole_timer_tick(
                30000,
                function () use ($cronManager) {
                    $cronManager->log('heart beat');
                    $cronManager->runNextJob();
                }
            );
        }
    );
    $http->addProcess($crontab);
}
// WebSocket::fork($http, $app);
$http->start();