<?php
namespace App\Models;

class ModelBase extends \Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->setConnectionService('db_nebulasio');
    }
}