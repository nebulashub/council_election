<?php
namespace App\Components;

use App\Models\Ipfs\User as IpfsUser;

class User {

    private $_di = null;

    private $_expire = null;

    public function __construct(\Phalcon\Di\FactoryDefault $di)
    {
        $this->_di = $di;
    }

    public function login(array $info, int $expire)
    {
        $this->_di->get('session')->set('info', $info, (int)$expire - time());
        return true;
    }

    public function logout()
    {
        return $this->_di->get('session')->remove("info");
    }

    public function getInfo()
    {
        return $this->_di->get('session')->get('info');
    }

    public function updateInfo($key, $value)
    {
        $info = $this->getInfo();
        $info[$key] = $value;
        return  $this->_di->get('session')->set('info', $info);
    }
}