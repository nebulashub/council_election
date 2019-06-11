<?php
namespace App\Handlers;

use App\Components\Neb;
use App\Models\Target;
use App\Models\Project;

class PledgeController extends ControllerBase
{
    public function getTargetByAddress($address, $target)
    {
        $t = Target::findFirstByName($target);
        if (!$t) {
            return ['status' => 3];
        }
        $config = $this->getDI()->get('config')->neb;
        $neb = Neb::init();
        $ret = $neb->getPledgeByaddress($config->support_nas_contract, $address);
        // 1： 查出东西了
        // 2：地址有误
        // 3：候选人有误
        // 4：该地址针对此候选人还未质押
        if (empty($ret)) {
            return ['status' => 5];
        }
        foreach ($ret as $name => $row) {
            if ($target == $name) {
                return $row;
            }
        }
        return ['status' => 4];
    }
}