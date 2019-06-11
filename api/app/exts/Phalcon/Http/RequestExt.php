<?php
namespace Phalcon\Http;

use Phalcon\Http\Request;

class RequestExt extends Request
{
    private $_swooleRequest = null;

    public function setSwooleRequest(\Swoole\Http\Request $request)
    {
        $this->_swooleRequest = $request;

        $_GET = $_POST = $_COOKIE = $_REQUEST = $_SERVER = $_FILES = [];
        if (!empty($request->get)) {
            $_GET = $request->get;
            $_REQUEST += $_GET;
        }
        if (!empty($request->post)) {
            if (!($_POST = json_decode($request->rawContent(), true))) {
                $_POST = $request->post;
            }
            $_REQUEST += $_POST;
        }
        if (!empty($request->files)) {
            $_FILES = $request->files;
        }
        if (!empty($request->server)) {
            foreach ($request->server as $key => $value) {
                $_SERVER[strtoupper($key)] = $value;
            }
        }
        if (!empty($request->header)) {
            foreach ($request->header as $key => $value) {
                $_SERVER['HTTP_'. str_replace('-', '_', strtoupper($key))] = $value;
            }
        }
        if (!empty($request->cookie)) {
            $_COOKIE = $request->cookie;
        }
        $this->setRawBody($request->rawContent());
        return $this;
    }

    public function setRawBody($rawBody)
    {
        $this->_rawBody = $rawBody;
        $this->_putCache = null;
        return $this;
    }
    
    public function getToken()
    {
        $token = '';
        $httpHeader = $this->getHeaders();
        if (!empty($httpHeader['Authorization'])) {
            $ret = sscanf($httpHeader['Authorization'], "Bearer %s");
            if (!empty($ret)) {
                $token = $ret[0];
            }
        }
        return $token;
    }
}