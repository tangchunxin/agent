<?php
namespace bigcat\model;

use bigcat\inc\Factory;
use bigcat\inc\BaseFunction;
class AgentBuyFactory extends Factory
{
    const objkey = 'big_agent_agent_buy_multi_';
    private $sql;
    public function __construct($dbobj, $buy_aid) 
    {
        $serverkey = self::objkey;
        $objkey = self::objkey."_".$buy_aid;
        $this->sql = "select
            `buy_aid`
            , `aid`
            , `money`
            , `buy_amount`
            , `buy_status`

            , `activity_info`
            , `handler`
            , `buy_time`
            , `month`

            from `agent_buy`
            where `buy_aid`=".intval($buy_aid)."";

        parent::__construct($dbobj, $serverkey, $objkey);
        return true;
    }

    public function retrive() 
    {
        $records = BaseFunction::query_sql_backend($this->sql);
        if( !$records ) 
        {
            return null;
        }

        $obj = null;
        while ( ($row = $records->fetch_row()) != false ) 
        {
            $obj = new AgentBuy;

            $obj->buy_aid = intval($row[0]);
            $obj->aid = intval($row[1]);
            $obj->money = ($row[2]);
            $obj->buy_amount = intval($row[3]);
            $obj->buy_status = intval($row[4]);

            $obj->activity_info = ($row[5]);
            $obj->handler = intval($row[6]);
            $obj->buy_time = intval($row[7]);
            $obj->month = intval($row[8]);

            $obj->before_writeback();
            break;
        }
        $records->free();
        unset($records);
        return $obj;
    }
}

