<?php
namespace App\Components;

use Phalcon\Mvc\User\Component;

class Curl {

    private $_handle = null;

    public function __construct()
    {
        $ch = $this->_handle = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    }

    public function header(string ...$headers)
    {
        curl_setopt($this->_handle, CURLOPT_HTTPHEADER, $headers);
    }

    public function setTimeout(int $timeout)
    {
        curl_setopt($this->_handle, CURLOPT_TIMEOUT, $timeout);
        return $this;
    }

    public function query($url, array $data = [], $method = "GET")
    {
        if (!empty($data)) {
            $url = $url.'?'.http_build_query($data);
        }
        curl_setopt($this->_handle, CURLOPT_URL, $url);
        curl_setopt($this->_handle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->_handle, CURLOPT_HTTPGET, 1);
        $ret = curl_exec($this->_handle);
        $errorno = curl_errno($this->_handle);
        curl_close($this->_handle);
        if ($errorno) {
            return false;
        }
        $arr = json_decode($ret, true);
        return $arr !== null ? $arr : $ret;
    }

    public function post($url, $data, $jsonEncode = JSON_UNESCAPED_UNICODE)
    {
        curl_setopt($this->_handle, CURLOPT_URL, $url);
        curl_setopt($this->_handle, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->_handle, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($this->_handle, CURLOPT_POSTFIELDS, $jsonEncode ? json_encode($data, $jsonEncode) : $data); // Post提交的数据包
        $ret = curl_exec($this->_handle);
        if (!$ret) {
            return false;
        }
        return json_decode($ret, true);
    }
}