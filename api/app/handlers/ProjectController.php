<?php
namespace App\Handlers;

use Nebulas\Core\Account;
use Nebulas\Rpc\HttpProvider;
use Nebulas\Rpc\Neb;

class ProjectController extends ControllerBase
{
    public function getVotes()
    {

        $nebService = $this->getDI()->get('neb');
/*
        return json_decode($nebService->api->getAccountState('n1H2Yb5Q6ZfKvs61htVSV4b1U2gr2GA9vo6'));*/

        return $nebServic->api->getData('trustee_council_week1');
    }

}