<?php
namespace Phalcon\Crontab;

class Manager
{
    private $_console = null;

    private $_jobs = [];

    private $_checkIntervalSecond = 30;

    private $_logger = null;

    public function __construct(\Phalcon\Cli\Console $console)
    {
        $this->_console = $console;
    }

    public function log($msg)
    {
        if ($this->_console->getDI()->has('logger')) {
            $this->_console->getDI()->get('logger')->get('cli')->info($msg);
        }
    }

    public function AddJob(string $name, string $schedule, $handleName, $actionName, array $params = [])
    {
        $job = new Job($this->_console, $handleName, $actionName, $params);
        $job->setName($name);
        $job->schedule($schedule);
        $this->_jobs[$name] = $job;
        return $this;
    }

    public function getJob(string $name)
    {
        if (isset($this->_jobs[$name])) {
            return $this->_jobs[$name];
        }
        return null;
    }

    public function removeJob(string $name)
    {
        if (isset($this->_jobs[$name])) {
            unset($this->_jobs[$name]);
        }
        return $this;
    }

    public function getCheckIntervalSecond()
    {
        return $this->_checkIntervalSecond;
    }

    public function hasJobs()
    {
        return !empty($this->_jobs);
    }

    public function runNextJob(...$params)
    {
        $exec = false;
        foreach ($this->_jobs as $job) {
            $time = $job->getNextRunDate()->getTimestamp();
            $current = time();
            if ($time > $current && ($time - $current <= $this->_checkIntervalSecond)) {
                $exec = true;
                swoole_timer_after(
                    ($time - $current) * 1000,
                    function () use ($job, $params) {
                        $logger = $job->getLogger();
                        if ($logger) {
                            $logger->info($job->getName().' job start');
                        }
                        $job->run($params);
                    }
                );
            }
        }
        return $exec;
    }

}