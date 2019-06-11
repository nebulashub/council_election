<?php
namespace Phalcon\Acl\Adapter;

use Phalcon\Acl\Adapter\Memory as Memory;

class MemoryExt extends Memory
{
    public function isAllowed($role, $resource, $access, array$parameters = null)
    {
        if (is_string($role)) {
            return parent::isAllowed($role, $resource, $access, $parameters);
        } else if (is_array($role)) {
            foreach ($role as $item) {
                if (parent::isAllowed($item, $resource, $access, $parameters)) {
                    return true;
                }
            }
        }
        return false;
    }
}
