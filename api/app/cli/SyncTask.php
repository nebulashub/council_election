<?php
namespace App\Cli;

use App\Components\Neb;
use App\Models\Target;
use App\Models\TargetPeriod;
use App\Models\Project;

class SyncTask extends \Phalcon\Cli\Task
{
    public function statAction()
    {
        $config = $this->getDI()->get('config')->neb;
        $neb = Neb::init();
        $periodNum = $neb->getCurrentPeriodNum();
        // sync targets
        $ret = $neb->getCandidatePledges($config->support_nas_contract);
        if (!empty($ret)) {
            foreach ($ret as $row) {
                $target = Target::findFirstByName($row['candidate']);
                if (!$target) {
                    $target = new Target;
                    $target->name = $row['candidate'];
                }
                $target->nas = $row['value'];
                $target->save();
            }
        }

        for ($i = 1; $i <= 5; $i ++) {
            $periodNum = $i;
            $ret = $neb->getVoteResult($config->vote_contract, $config->vote_nat_contract, Project::getKey($periodNum));
            if (!empty($ret['result'])) {
                foreach ($ret['result'] as $key => $value) {
                    $info = explode(',', $key);
                    $name = $info[0];
                    $option = $info[1];
                    $target = Target::findFirstByName($name);
                    $period = TargetPeriod::findFirst('target_id = '.$target->id.' AND num = '.$periodNum);
                    if (!$period) {
                        $period = new TargetPeriod();
                        $period->target_id = $target->id;
                        $period->num = $periodNum;
                    }
                    if ($option == 'support') {
                        $period->support = $value;
                    }
                    if ($option == 'against') {
                        $period->against = $value;
                    }
                    $period->save();
                }
            }
        }

    }
}