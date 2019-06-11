<?php
namespace Phalcon\Logger;

use Phalcon\Logger\AdapterInterface;
use Phalcon\Logger\FormatterInterface;

class Files
{
    private $_path;

    private $_loggers;

    private function _renamePath($name)
    {
        return realpath($this->_path).'/'.$name.'_'.date('Y-m-d').'.log';
    }

    public function __construct($path)
    {
        $path = realpath($path);
        if (!$path) {
            throw new Exception("path does not exists.");
        }
        $this->_path = $path;
    }

    public function add(string $name, $autoRename = true, ?array $options = null)
    {
        $this->_loggers[$name] = [
                'logger' => new Adapter\FileExt(($autoRename ? $this->_renamePath($name) : realpath($this->_path).'/'.$name.'.log'), $options),
                'options' => $options,
                'date' => date('Y-m-d'),
                'rename' => $autoRename
            ];
        return $this;
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        $loggers = $this->_loggers;
        if (is_array($loggers)) {
            foreach ($this->_loggers as $confs) {
                $confs['logger']->setFormatter($formatter);
            }
        }
        $this->_formatter = $formatter;
        return $this;
    }

    public function setLogLevel(int $level)
    {
        $loggers = $this->_loggers;
        if (is_array($loggers)) {
            foreach ($this->_loggers as $confs) {
                $confs['logger']->setLogLevel($level);
            }
        }
        $this->_logLevel = $level;
        return $this;
    }

    public function get(string $name)
    {
        $confs = $this->_loggers[$name] ?? null;
        if (empty($confs)) {
            return null;
        }
        if ($confs['rename'] && $confs['date'] != date('Y-m-d')) {
            $confs['logger']->close();
            $this->_loggers[$name]['logger'] = new Adapter\File($this->_renamePath($name), $this->_loggers[$name]['options']);
        }
        return $this->_loggers[$name]['logger'];
    }

    public function call(string $name, string $operate, $message, ?array $context = null)
    {
        $logger = $this->get($name);
        if ($logger) {
            return $logger->$operate($message, $context);
        }
        return false;
    }
}