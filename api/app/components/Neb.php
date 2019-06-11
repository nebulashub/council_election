<?php
namespace App\Components;

use Phalcon\Mvc\User\Component;

class Neb extends Component
{
    const PERIOD_1 = 1;

    const PERIOD_2 = 2;

    const PERIOD_3 = 3;

    const PERIOD_4 = 4;

    const PERIOD_5 = 5;

/*    const PERIOD_6 = 6;
    
    const PERIOD_7 = 7;

    const PERIOD_8 = 8;*/

    public static $projects = [
        self::PERIOD_1 => 'council_vote1',
        self::PERIOD_2 => 'council_vote2',
        self::PERIOD_3 => 'council_vote3',
        self::PERIOD_4 => 'council_vote4',
        self::PERIOD_5 => 'council_vote5',
/*        self::PERIOD_6 => 'council_vote6',
        self::PERIOD_7 => 'council_vote7',
        self::PERIOD_8 => 'council_vote8',*/
    ];

    private static $_instance = null;

    private $_neb = null;

    private $_from = 'n1i95x9i52AH1qCgbVX8SnHawTjxWkrwcJQ';

    public static function init()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setFrom($from)
    {
        $this->_from = $from;
    }

    public function getCurrentPeriodNum()
    {
        $ranges = $this->getDI()->get('config')->app->period;
        $now = time();
        foreach ($ranges as $k => $range) {
            $range = explode('|', $range);
            $start = $range[0];
            $end = $range[1];
            if (strtotime($start) <= $now && $now <= strtotime($end)) {
                return $k+1;
            }
        }
        return false;
    }

    public function getRangeByPeriodNum(int $num)
    {
        $ranges = $this->getDI()->get('config')->app->period;
        foreach ($ranges as $key => $range) {
            if ($key + 1 == $num) {
                $ret = new \stdClass();
                $range = explode('|', $range);
                $ret->start = strtotime($range[0]);
                $ret->end = strtotime($range[1]);
                return $ret;
            }
        }
        return false;
    }

    private function __construct()
    {
        $this->_neb = $this->getDI()->get('neb');
    }

    private function _call(string $hash, string $function, array $args = [])
    {
        $result = $this->_neb->api->call(
            $this->_from,
            $hash,
            0,
            1,
            20000000000,
            10000000,
            null,
            ['function' => $function, "args" => json_encode($args)],
            null
        );
        return $result ? json_decode($result, true) : false;
    }

    private function _vote($hash, $dataSource, $key, $value, $option)
    {
        return $this->_call($hash, 'vote', [$dataSource, $key, $option, $value]);
    }

    public function getProjectInfo($hash, $period)
    {
        if (!isset(self::$projects[$period])) {
            // @todo 
            return false;
        }
        return $this->_call($hash, 'getData', [self::$projects[$period]]);
    }

    public function voteSupport(string $hash, string $dataSource, string $key, $value)
    {
        return $this->_vote($hash, $dataSource, $key, $value, 'yes');
    }

    public function voteAgainst(string $hash, string $dataSource, string $key, $value)
    {
        return $this->_vote($hash, $dataSource, $key, $value, 'no');
    }

    public function getVoteResult(string $hash, string $dataSource, string $key)
    {
        $ret = $this->_call($hash, 'getVoteResult', [$dataSource, $key]);
        if (isset($ret['result']) && isset($ret['result']['result'])) {
            $ret = json_decode($ret['result']['result'], true);
        } else {
            $ret = false;
        }
        return $ret;
    }

    public function pledge(string $hash, string $target, $value)
    {
        return $this->_call($hash, 'pledge', [$target, $value]);
    }

    public function cancelPledge(string $hash, string $target)
    {
        return $this->_call($hash, 'cancelPledge', [$target]);
    }

    public function getTargets($hash)
    {
        return $this->_call($hash, 'targets');
    }

    public function getCandidatePledges($hash)
    {
        $ret = $this->_call($hash, 'getCandidatePledges', []);
        if (isset($ret['result']) && isset($ret['result']['result'])) {
            $ret = json_decode($ret['result']['result'], true);
        } else {
            $ret = false;
        }
        return $ret;
    }

    public function getPledgeByTarget(string $hash, string $pledge)
    {
        return $this->_call($hash, 'getPledgeByTarget', [$pledge]);
    }

    public function getPledgeByaddress(string $hash, string $address)
    {
        $ret = $this->_call($hash, 'getPledge', [$address]);
        if (isset($ret['result']) && isset($ret['result']['result'])) {
            $ret = json_decode($ret['result']['result'], true);
            if (is_array($ret)) {
                foreach ($ret as $target => &$row) {
                    $row['value'] = !empty($row['value']) ? gmp_strval(gmp_div($row['value'], pow(10, 18))) : '0';
                }
            }
            return $ret;
        }
        return false;
    }

}