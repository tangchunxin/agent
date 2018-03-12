<?php
namespace bigcat\model;

use bigcat\inc\MutiStoreFactory;
use bigcat\inc\BaseFunction;
class AgentInfoMultiFactory extends MutiStoreFactory
{
    public $key = 'big_agent_agent_info_multi_';
    private $sql;
    private $log = './log/business.log';

    public function __construct($dbobj, $key_objfactory=null, $aid=null, $key_add='') 
    {
        if( !$key_objfactory && !$aid )
        {
            return false;
        }
        $this->key = $this->key.$key_add;
        $ids = '';
        if($key_objfactory) 
        {
            if($key_objfactory->initialize()) 
            {
                $key_obj = $key_objfactory->get();
                $ids = implode(',', $key_obj);
            }
        }
        $fields = "
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
            ";

        if( $aid != null )
        {
            $this->bInitMuti = false;
            $this->sql = "select $fields from agent_info where `aid`=".intval($aid)."";
        }
        else
        {
            $this->sql = "select $fields from agent_info ";
            if($ids)
            {
                $this->sql = $this->sql." where `aid` in ($ids) order by init_time asc";
            }
        }

        parent::__construct($dbobj, $this->key, $this->key, $key_objfactory, $aid);
        return true;
    }

    public function retrive() 
    {
        $records = BaseFunction::query_sql_backend($this->sql);
        if( !$records ) 
        {
            return null;
        }

        $objs = array();
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
            $objs[$this->objkey.'_'.$obj->aid] = $obj;
        }
        $records->free();
        unset($records);
        return $objs;
    }
}


