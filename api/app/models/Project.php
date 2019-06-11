<?php
namespace App\Models;

class Project extends ModelBase
{
    public static $keys = [
        1 => "council_election_week_1",
        2 => "council_election_week_2",
        3 => "council_election_week_3",
        4 => "council_election_week_4",
        5 => "council_election_week_5",
        6 => "council_election_week_6",
        7 => "council_election_week_7",
        8 => "council_election_week_8",
    ];

    public static function getKey(int $period)
    {
        if (!isset(self::$keys[$period])) {
            return false;
        }
        if (getAppEnv() == \Phalcon\Bootstrap::ENV_PRODUCTION) {
            return self::$keys[$period];
        } else {
            return "council_election_week_test_".$period;
        }
    }

    public static function getPeriod(string $key)
    {
        $tmp = array_reverse(self::$keys);
        if (!isset($tmp[$key])) {
            return false;
        }
        return $tmp[$key];
    }
}