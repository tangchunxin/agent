<?php
namespace bigcat\model;

use bigcat\inc\ListFactory;
use bigcat\inc\BaseFunction;
class AgentBuyListFactory extends ListFactory
{
    public $key = 'big_agent_agent_buy_list_';
    public function __construct($dbobj, $buy_aid = null, $id_multi_str='',$aid = null,$buy_status = null ,$handler = null, $start_time = null, $end_time = null) 
    {
        //$id_multi_str 是用,分隔的字符串
        if($buy_aid && $aid == null && $buy_status == null && $handler == null && $start_time == null && $end_time == null) 
        {
            $this->key = $this->key.$buy_aid;
            $this->sql = "select `buy_aid` from `agent_buy` where buy_aid=".intval($buy_aid)."";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($buy_aid == null && $aid && $buy_status && $handler == null && $start_time == null && $end_time == null) 
        {
            $this->key = $this->key.$buy_status;
            $this->sql = "select `buy_aid` from `agent_buy` where handler=".intval($aid)." and buy_status = ".intval($buy_status)." order by buy_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($buy_aid == null && $aid == null && $buy_status && $handler == null && $start_time  && $end_time ) 
        {
            $this->key = $this->key.$start_time;
            $this->sql = "select `buy_aid` from `agent_buy` where buy_status = ".intval($buy_status)." and ( `buy_time` between ".$start_time." and " .$end_time ." )";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($buy_aid == null && $aid == null && $buy_status == null && $handler == null && $start_time  && $end_time ) 
        {
            $this->key = $this->key.$start_time;
            $this->sql = "select `buy_aid` from `agent_buy` where `buy_time` between ".$start_time." and " .$end_time ."";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($buy_aid == null && $handler && $aid == null && $buy_status == null && $start_time == null && $end_time == null) 
        {
            $this->key = $this->key.$handler;
            $this->sql = "select `buy_aid` from `agent_buy` where handler = ".intval($handler)." order by buy_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        } 
        elseif($buy_aid == null && $handler && $aid == null && $buy_status == null && $start_time  && $end_time ) 
        {
            $this->key = $this->key.$handler;
            $this->sql = "select `buy_aid` from `agent_buy` where handler = ".intval($handler)." and ( `buy_time` between ".$start_time." and " .$end_time ." )";
            parent::__construct($dbobj, $this->key);
            return true;
        }       
        elseif($buy_aid == null && $aid && $buy_status == null && $handler == null && $start_time == null && $end_time == null) 
        {
            $this->key = $this->key.$aid;
            $this->sql = "select `buy_aid` from `agent_buy` where aid =".intval($aid)." order by buy_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        } 
        /*elseif($buy_aid == null && $aid == null && $buy_status == null && $id_multi_str == null ) 
        {
            $this->key = $this->key.$buy_aid;
            $this->sql = "select `buy_aid` from `agent_buy` ";
            parent::__construct($dbobj, $this->key);
            return true;
        }*/
        elseif ($id_multi_str) 
        {
            $this->key = $this->key.md5($id_multi_str);
            parent::__construct($dbobj, $this->key, null, $id_multi_str);
            return true;
        }
        return false;
    }
}

