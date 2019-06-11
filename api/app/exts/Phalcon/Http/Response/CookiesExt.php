<?php
namespace Phalcon\Http\Response;


class CookiesExt extends Cookies {

    public function getInternalCookies()
    {
        return $this->_cookies;
    }

    public function delete($name)
    {
        if (!$this->has($name)) {
            return false;
        }
        $cookie = $this->get($name);
        $cookie->useEncryption(false);
        $cookie->setValue(null);
        $cookie->setExpiration(time() - 691200);
        $this->_cookies[$name] = $cookie;
        return true;
    }

}
