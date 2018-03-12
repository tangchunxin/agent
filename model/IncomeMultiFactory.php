<?php
namespace bigcat\model;

use bigcat\inc\MutiStoreFactory;
use bigcat\inc\BaseFunction;
class IncomeMultiFactory extends MutiStoreFactory
{
    public $key = 'income_income_multi_';
    private $sql;

    public function __construct($dbobj, $key_objfactory=null, $out_trade_no=null, $key_add='') 
    {
        if( !$key_objfactory && !$out_trade_no )
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
            `out_trade_no`
            , `uid`
            , `total_fee`
            , `start_time`
            , `end_time`
            , `status`

            , `notify_status`
            , `third_trade_no`
            , `third_party_type`
            , `third_notify_info`
            , `expire_time`

            , `init_time`
            , `update_time`
            ";

        if( $out_trade_no != null )
        {
            $this->bInitMuti = false;
            $this->sql = "select $fields from income where `out_trade_no`=".intval($out_trade_no)."";
        }
        else
        {
            $this->sql = "select $fields from income ";
            if($ids)
            {
                $this->sql = $this->sql." where `out_trade_no` in ($ids) ";
            }
        }
        parent::__construct($dbobj, $this->key, $this->key, $key_objfactory, $out_trade_no);
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
            $obj = new Income;

            $obj->out_trade_no = intval($row[0]);
            $obj->uid = intval($row[1]);
            $obj->total_fee = ($row[2]);
            $obj->start_time = ($row[3]);
            $obj->end_time = ($row[4]);
            $obj->status = intval($row[5]);

            $obj->notify_status = intval($row[6]);
            $obj->third_trade_no = ($row[7]);
            $obj->third_party_type = intval($row[8]);
            $obj->third_notify_info = ($row[9]);
            $obj->expire_time = intval($row[10]);

            $obj->init_time = intval($row[11]);
            $obj->update_time = intval($row[12]);

            $obj->before_writeback();
            $objs[$this->objkey.'_'.$obj->out_trade_no] = $obj;
        }
        $records->free();
        unset($records);
        return $objs;
    }
}


