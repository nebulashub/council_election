<?php
namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\AdapterInterface;
use Phalcon\Logger\FormatterInterface;

class FileExt extends File
{
    public function logInternal($message, $type, $time, ?array $context = null)
    {
        if (!is_resource($this->_fileHandler)) {
            throw new Exception("Cannot send message to the log because it is invalid");
        }
        if (!file_exists($this->_path)) {
            $mode = $this->_options["mode"] ?? null;
            if ($mode === null) {
                $mode = "a";
            }
            $this->_fileHandler = fopen($this->_path, $mode);
            $this->logInternal($message, $type, $time, $context);
        }
        $innerMsg = $this->getFormatter()->format($message, $type, $time, $context);
        $handler = $this->_fileHandler;
        go(
            function () use ($handler, $innerMsg) {
                fwrite($handler, $innerMsg);
            }
        );
    }
}