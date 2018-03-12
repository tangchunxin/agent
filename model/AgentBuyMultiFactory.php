<?php
namespace bigcat\model;

use bigcat\inc\MutiStoreFactory;
use bigcat\inc\BaseFunction;
class AgentBuyMultiFactory extends MutiStoreFactory
{
    public $key = 'big_agent_agent_buy_multi_';
    private $sql;

    public function __construct($dbobj, $key_objfactory=null, $buy_aid=null, $key_add='') 
    {
        if( !$key_objfactory && !$buy_aid )
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
            `buy_aid`
            , `aid`
            , `money`
            , `buy_amount`
            , `buy_status`

            , `activity_info`
            , `handler`
            , `buy_time`
            , `month`
            ";

        if( $buy_aid != null )
        {
            $this->bInitMuti = false;
            $this->sql = "select $fields from agent_buy where `buy_aid`=".intval($buy_aid)."";
        }
        else
        {
            $this->sql = "select $fields from agent_buy ";
            if($ids)
            {
                $this->sql = $this->sql." where `buy_aid` in ($ids) ";
            }
        }
        parent::__construct($dbobj, $this->key, $this->key, $key_objfactory, $buy_aid);
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
            $objs[$this->objkey.'_'.$obj->buy_aid] = $obj;
        }
        $records->free();
        unset($records);
        return $objs;
    }
}


