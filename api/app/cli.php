<?php
require_once realpath(dirname(__FILE__))."/bootstrap.php";
require_once APP_PATH."/../vendor/autoload.php";

use Nebulas\Rpc\Neb;
use Nebulas\Rpc\HttpProvider;

$console = $boot->buildCli();
/**
 * Process the console arguments
 */
$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = "\\App\\Cli\\" . ucfirst($arg);
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3 && !is_numeric(strpos($arg, 'env='))) {
        $arguments['params'][] = $arg;
    }
}
$di = $console->getDI();
$di->setShared('neb', new Neb(new HttpProvider($di->get('config')->neb->provider)));

// Define global constants for the current task and action
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    go(function () use ($console, $arguments) {
        // Handle incoming arguments
        $console->handle($arguments);
    });
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}