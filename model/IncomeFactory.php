<?php
namespace bigcat\model;

use bigcat\inc\Factory;
use bigcat\inc\BaseFunction;
class IncomeFactory extends Factory
{
    const objkey = 'income_income_multi_';
    private $sql;
    public function __construct($dbobj, $out_trade_no) 
    {
        $serverkey = self::objkey;
        $objkey = self::objkey."_".$out_trade_no;
        $this->sql = "select
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

            from `income`
            where `out_trade_no`=".intval($out_trade_no)."";

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
            break;
        }
        $records->free();
        unset($records);
        return $obj;
    }
}

