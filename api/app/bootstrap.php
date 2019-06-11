<?php
define('APP_PATH', realpath(dirname(__FILE__)));
define('DOCUMENT_ROOT', APP_PATH.'/../public');
define('CONFIG_PATH', APP_PATH . "/../config");

use Phalcon\Loader;
use Phalcon\Config\Adapter\Yaml as ConfigYaml;

chdir(realpath(dirname(__FILE__)));


$paths = ["App\Models"     => APP_PATH.'/models/',
          "App\Cli"        => APP_PATH.'/cli/',
          "App\Components" => APP_PATH.'/components/',
          "App\Handlers"   => APP_PATH.'/handlers/',
          "App\Processes"   => APP_PATH.'/processes/',
          "App\Middleware" => APP_PATH.'/middleware/'];
$loader = new Loader();
$loader->registerNamespaces($paths);
$loader->registerDirs([APP_PATH.'/exts']);
$loader->register();

$isCrontab = false;
$env       = Phalcon\Bootstrap::ENV_PRODUCTION;

function getAppEnv()
{
    global $env;
    global $isCrontab;
    if (isset($_SERVER['argv'])) {
        foreach ($_SERVER['argv'] as $value) {
            if (is_int(strpos($value, 'env='))) {
                $strEnv = str_replace('env=', '', $value);
                if (in_array($strEnv, Phalcon\Bootstrap::$envs)) {
                    $env = $strEnv;
                }
            } else if (is_int(strpos($value, 'crontab'))) {
                $isCrontab = true;
            }
        }
    }
    return isset($env) ? $env : Phalcon\Bootstrap::ENV_PRODUCTION;
}

try {
    $env = getAppEnv();
    $config = new ConfigYaml(CONFIG_PATH."/production.yaml");
    foreach (Phalcon\Bootstrap::$envs as $val) {
        if ($val != Phalcon\Bootstrap::ENV_PRODUCTION && $env == $val) {
            $config->get('app')->offsetUnset('period');
            $config->merge(new ConfigYaml(CONFIG_PATH."/".strtolower($env).".yaml"));
        }
    }
    $boot = new Phalcon\Bootstrap($config, $env);
} catch (\Throwable $e) {
    var_dump($e->getMessage());
    exit();
}
