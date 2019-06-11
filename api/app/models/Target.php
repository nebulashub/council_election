<?php
namespace App\Models;

class Target extends ModelBase
{
    const NAS_TOTAL_NEED = 100000;

    public function initialize()
    {
        parent::initialize();
        $this->hasMany('id', 'App\\Models\\TargetPeriod', 'target_id', array('alias' => 'Periods'));
    }

    public function beforeCreate()
    {
        $this->last_modify = date('Y-m-d H:i:s');
    }

    public function beforeUpdate()
    {
        $this->last_modify = date('Y-m-d H:i:s');
    }

    public function beforeSave()
    {
        $this->last_modify = date('Y-m-d H:i:s');
    }

    public function getPeriodByNum(int $num)
    {
        return TargetPeriod::findFirst('target_id = ' . $this->id . ' AND num = '.$num);
    }

    public function getTotalNat(?int $periodNum = null)
    {
        $total = 0;
        $condition = 'target_id = '.$this->id;
        if ($periodNum !== null) {
            $condition .= ' AND num <= '.$periodNum;
        }
        $periods = TargetPeriod::find($condition);

        foreach ($periods as $period) {
            $total += (float)$period->support;
            $total += (float)$period->against;
        }
        return $total;
    }
}