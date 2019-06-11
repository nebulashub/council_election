<?php
namespace Phalcon\Crontab;

class Job
{
    private $_cli = null;

    private $_name = '';

    private $_task = null;

    private $_action = null;

    private $_params = [];

    private $_crontab = null;

    private $_logger = null;

    public function __construct(\Phalcon\Cli\Console $console, $task, $action, array $params = [])
    {
        $this->_task = $task;
        $this->_action = $action;
        $this->_params = $params;
        $this->_cli = $console;
    }

    public function setLogger(\Phalcon\Logger\Adapter $logger)
    {
        $this->_logger = $logger;
    }

    public function getLogger()
    {
        return $this->_logger;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function schedule($strConfig)
    {
        $this->_crontab = \Cron\CronExpression::factory($strConfig);
    }

    public function getNextRunDate()
    {
        if (!$this->_crontab) {
            return false;
        }
        return $this->_crontab->getNextRunDate();
    }

    public function getPreviousRunDate()
    {
        if (!$this->_crontab) {
            return false;
        }
        return $this->_crontab->getPreviousRunDate();
    }

    public function run(array $params = [])
    {
        $di = $this->_cli->getDI();
        $services = $di->getServices();
        if (!empty($services)) {
            foreach ($services as $name => $service) {
                $definition = $service->getDefinition();
                if ($definition instanceof \Phalcon\Db\Adapter) {
                    $definition->getEventsManager()->fire("db:reconnect", $definition);
                }
            }
        }

        $ret = $this->_cli->handle(['task' => $this->_task, 'action' => $this->_action, 'params' => !empty($params) ? $params : $this->_params]);

        $job = $this;
        go(
            function () use ($job, $ret) {
                $logger = $job->getLogger();
                if ($logger) {
                    if ($ret) {
                        $logger->info($job->getName().': job was finished (success)');
                    } else {
                        $logger->error($job->getName().': job was finished (fail)');
                    }
                }
            }
        );
        return $ret;
    }
}