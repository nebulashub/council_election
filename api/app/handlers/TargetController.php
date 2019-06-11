<?php
namespace App\Handlers;

use App\Components\Neb;
use App\Models\Target;
use App\Models\TargetPeriod;
use App\Models\Project;

class TargetController extends ControllerBase
{
    public function info($name)
    {
        $neb = Neb::init();
        $target = Target::findFirstByName($name);
        if (!$target) {
            throw new \InvalidArgumentException("候选对象不存在");
        }
        $periodNum = $neb->getCurrentPeriodNum();
        $lastPeriodNum = $periodNum ? $periodNum - 1 : 8;
        $periods = $target->getPeriods();
        $ret = [
            'nas_fundraised' => gmp_strval(gmp_div($target->nas, pow(10, 18))) ?? '0',
            'nas_total_need' => Target::NAS_TOTAL_NEED,
            'this_week_vote' => [],
            'nat_reward_total' => $this->_getNatRewardTotal($lastPeriodNum, $target->getTotalNat($lastPeriodNum)),
            'every_week_vote' => []
        ];
        foreach ($periods as $period) {
            $range = $neb->getRangeByPeriodNum($period->num);
            if (!$range) {
                continue;
            }
            if ($period->num == $periodNum) {
                $ret['this_week_vote'] = [
                        'start_time' => $range->start,
                        'end_time'   => $range->end,
                        'support'    => (float)$period->support,
                        'against'    => (float)$period->against,
                        'id'         => Project::getKey($period->num)
                    ];
            }
            $ret['every_week_vote'][$period->num] = [
                'start_time' => $range->start,
                'end_time' => $range->end,
                'nat_total' => (float)$period->support + (float)$period->against,
                'support' => (float)$period->support,
                'against' => (float)$period->against,
                'id' => Project::getKey($period->num)
            ];
        }
        if (empty($ret['this_week_vote'])) {
            $range = $neb->getRangeByPeriodNum($periodNum);
            $ret['this_week_vote'] = [
                'start_time' => $range->start,
                'end_time'   => $range->end,
                'support'    => 0,
                'against'    => 0,
                'id'         => Project::getKey($periodNum)
            ];
        }
        for ($i = 0; $i < 8; $i++) {
            if (isset($ret['every_week_vote'][$i+1])) {
                continue;
            }
            $range = $neb->getRangeByPeriodNum($i+1);
            $ret['every_week_vote'][$i+1] = [
                'start_time' => $range->start,
                'end_time'   => $range->end,
                'nat_total'  => $i+1 >= $periodNum ? -1 : -2,
                'support'    => $i+1 >= $periodNum ? -1 : -2,
                'against'    => $i+1 >= $periodNum ? -1 : -2,
                'id'         => Project::getKey($i+1),
            ];
        }
        ksort($ret['every_week_vote']);
        $ret['every_week_vote'] = array_values($ret['every_week_vote']);
        return $ret;
    }

}
