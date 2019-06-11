<?php
namespace Phalcon\Http;

use Phalcon\Http\Response;
use Phalcon\Http\Response\HeadersExt;

class ResponseExt extends Response {

    private $_swooleResponse = null;

    private $_isDetach = false;

    public function setSwooleResponse(\Swoole\Http\Response $response)
    {
        $this->reset();
        $this->_swooleResponse = $response;
        return $this;
    }

    public function reset()
    {
        $this->setStatusCode(200);
        $this->resetHeaders();
        $this->setContent('');
        $this->_sent = false;
        $this->_isDetach = false;
        return $this;
    }

    public function detach()
    {
        $this->_isDetach = true;
        $fd = $this->_swooleResponse->fd;
        $this->_swooleResponse->detach();
        $this->_swooleResponse = \Swoole\Http\Response::create($fd);
        return $this;
    }

    public function isDetach()
    {
        return $this->_isDetach;
    }

    public function sendCookies()
    {
        // send cookies
        foreach ($this->getDI()->get('cookies')->getInternalCookies() as $cookie) {
            $this->_swooleResponse->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiration(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttpOnly());
        }
        return $this;
    }

    public function sendHeaders()
    {
        $headers = $this->_headers->toArray();
        $location = null;
        // send cookies
        foreach ($headers as $header => $value) {
            if ($value === null) {
                continue;
            }
            $string = $header . ": " . $value;
            $this->_swooleResponse->header($header, $value);
            if (trim(strtolower($header)) == 'location') {
                $location = $value;
            }
        }
        if (!empty($location)) {
            $this->_sent = true;
            $this->_swooleResponse->redirect($location, (int)$this->getStatusCode());
        }
        return $this;
    }

    public function send()
    {
        if ($this->_sent) {
            return true;
        }
        $this->sendHeadersAndCookies();
        /**
         * Output the response body
         */
        $content = $this->_content;
        if ($content != null) {
            $this->_swooleResponse->end($content);
        } else {
            $file = $this->_file;
            if (is_string($file) == "string" && strlen($file)) {
                $this->_swooleResponse->sendfile($file);
            }
        }
        $this->_sent = true;
        return $this;
    }

    public function sendHeadersAndCookies()
    {
        $this->_swooleResponse->status((int)$this->getStatusCode());
        $this->sendCookies();
        $this->sendHeaders();
        return $this;
    }

    public function write($content = null, ?int $chunk = null)
    {
        $this->sendHeadersAndCookies();
        if ($content === null && !$this->_sent) {
            return $this->_swooleResponse->end();
        }
        if (empty($chunk)) {
            return $this->_swooleResponse->write($content);
        }
        $num = (int)ceil((float)bcdiv(strlen($content), $chunk, 2));
        for ($i = 0; $i < $num; $i ++) {
            if ($i == $num - 1) {
                $chunk = (int)strlen($content) - ($i * $chunk);
            }
            $this->_swooleResponse->write(substr($content, ($i*$chunk), $chunk));
        }
        return true;
    }

    public function end()
    {
        $this->_sent = true;
        $this->_swooleResponse->end();
        return $this;
    }
}
