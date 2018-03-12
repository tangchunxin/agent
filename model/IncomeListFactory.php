<?php
namespace bigcat\model;

use bigcat\inc\ListFactory;
class IncomeListFactory extends ListFactory
{
    public $key = 'income_income_list_';
    public function __construct($dbobj, $out_trade_no = null, $id_multi_str='',$sql=null) 
    {
        //$id_multi_str 是用,分隔的字符串
        if($out_trade_no && $id_multi_str == null && $sql == null ) 
        {
            $this->key = $this->key.$out_trade_no;
            $this->sql = "select `out_trade_no` from `income` where out_trade_no=".intval($out_trade_no)."";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($sql && $out_trade_no == null && $id_multi_str == null  )
        {
            $this->key = $this->key.md5($sql);
            $this->sql = $sql;
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif ($id_multi_str && $out_trade_no == null  && $sql == null ) 
        {
            $this->key = $this->key.md5($id_multi_str);
            parent::__construct($dbobj, $this->key, null, $id_multi_str);
            return true;
        }
        return false;
    }
}

