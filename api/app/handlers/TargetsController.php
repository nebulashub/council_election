<?php
namespace App\Handlers;

use App\Components\Neb;
use App\Models\Target;
use App\Models\Project;

class TargetsController extends ControllerBase
{
    public function info()
    {
        $neb = Neb::init();
        $periodNum = $neb->getCurrentPeriodNum();
        $lastPeriodNum = $periodNum ? $periodNum - 1 : 8;

        $ret = ['this_week_vote' => Project::getKey($periodNum), 'list' => []];
        $targets = Target::find();
        foreach ($targets as $target) {
            $targetPeriod = $target->getPeriodByNum($periodNum);
            $total = $targetPeriod ? ((float)$targetPeriod->support + (float)$targetPeriod->against) : 0;
            $ret['list'][] = [
                'uid' => (int)$target->id,
                'name' => $target->name,
                'current' => [
                    'support'      => $targetPeriod ? (float)$targetPeriod->support : 0,
                    'against'      => $targetPeriod ? (float)$targetPeriod->against : 0,
                    'total'        => (float)$total,
                    'support_rate' => $total ? (float)bcdiv($targetPeriod->support, $total, 4) : 0,
                    'against_rate' => $total ? (float)bcdiv($targetPeriod->against, $total, 4) : 0,
                ],
                'nas' => gmp_strval(gmp_div($target->nas, pow(10, 18))) ?? '0',
                'nat' => (float)$target->getTotalNat(),
                'nat_reward_total' => $this->_getNatRewardTotal($lastPeriodNum, $target->getTotalNat($lastPeriodNum)),
            ];
        }
        return $ret;
    }
}