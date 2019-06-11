<?php
namespace Phalcon\Mvc;

class ViewExt extends View
{
    public function reset()
    {
        $this->_viewParams = [];
        $this->_pickView = null;
        $this->_actionName = null;
        $this->_controllerName = null;
        $this->_cache = null;
        $this->_cacheLevel = 0;
        return parent::reset();
    }
}