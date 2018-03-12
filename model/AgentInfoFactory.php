<?php
namespace bigcat\model;

use bigcat\inc\Factory;
use bigcat\inc\BaseFunction;
class AgentInfoFactory extends Factory
{
    const objkey = 'big_agent_agent_info_multi_';
    private $sql;
    public function __construct($dbobj, $aid) 
    {
        $serverkey = self::objkey;
        $objkey = self::objkey."_".$aid;
        $this->sql = "select
            `aid`
            , `wx_id`
            , `name`
            , `provinces`
            , `city`

            , `p_aid`
            , `type`
            , `opend_status`
            , `status`
            , `audit_eid`
            , `info`

            , `last_amount`
            , `close_down_info`
            , `close_down_time`
            , `p_num`
            , `init_time`
            , `update_time`
            , `month`
            , `shared`
            , `sub_shared`
            , `shared_info`
            , `third_shared`

            from `agent_info`
            where `aid`=".intval($aid)."";

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
            $obj = new AgentInfo;

            $obj->aid = intval($row[0]);
            $obj->wx_id = ($row[1]);
            $obj->name = ($row[2]);
            $obj->provinces = ($row[3]);
            $obj->city = ($row[4]);

            $obj->p_aid = intval($row[5]);
            $obj->type = intval($row[6]);
            $obj->opend_status = intval($row[7]);
            $obj->status = intval($row[8]);
            $obj->audit_eid = intval($row[9]);
            $obj->info = ($row[10]);

            $obj->last_amount = intval($row[11]);
            $obj->close_down_info = ($row[12]);
            $obj->close_down_time = intval($row[13]);
            $obj->p_num = intval($row[14]);
            $obj->init_time = intval($row[15]);
            $obj->update_time = intval($row[16]);
            $obj->month = intval($row[17]);
            $obj->shared = ($row[18]);
            $obj->sub_shared = ($row[19]);
            $obj->shared_info = ($row[20]);
            $obj->third_shared = ($row[21]);

            $obj->before_writeback();
            break;
        }
        $records->free();
        unset($records);
        return $obj;
    }
}

