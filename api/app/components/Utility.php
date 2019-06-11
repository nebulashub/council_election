<?php
namespace App\Components;

use App\Components\Constant as Constant;

/**
 * 工具类
 */
class Utility {

    /*
     * -转驼峰法
     */
    public static function toCamelCase($str, $ucfirst = false)
    {
        $toStr = preg_replace_callback("|\-\w|",
                        function ($matches) {
                            return strtoupper(trim($matches[0], '-'));
                        }, $str);
        return $ucfirst ? ucfirst($toStr) : $toStr;
    }
    
    /**
     * 对象转数组
     * @param object $obj 对象
     * @return array
     */
    public static function objectToArray($obj)
    {
        $obj = (array) $obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array) self::objectToArray($v);
            }
        }
        return $obj;
    }

    public static function encrypt($data, $key) {
        return base64_encode(openssl_encrypt($data, 'aes-128-cbc', $key, true, 'mediav0000000000'));
    }

    public static function decrypt($str, $key) {
        return openssl_decrypt(base64_decode($str), 'aes-128-cbc', $key, true, 'mediav0000000000');
    }

    public static function getIp()
    {
        $ip = '';
        $ipKey = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($ipKey as $v) {
            if (!empty($_SERVER[$v])) {
                $ip = $_SERVER[$v];
                break;
            }
        }
        return $ip;
    }
}
