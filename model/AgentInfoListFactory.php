<?php
namespace bigcat\model;

use bigcat\inc\ListFactory;
class AgentInfoListFactory extends ListFactory
{
    public $key = 'big_agent_agent_info_list_';
    public function __construct($dbobj, $aid = null, $id_multi_str='',$p_aid = null,$type = null ,$status = null,$p_aid_no_order = null, $sql = null, $p_num =null)
    {
        //$id_multi_str 是用,分隔的字符串
        if($sql)
        {
            $this->key = $this->key.md5($sql);
            $this->sql = $sql;
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($aid && $id_multi_str == null && $p_aid == null && $type == null && $status == null )
        {
            $this->key = $this->key.$aid;
            $this->sql = "select `aid` from `agent_info` where aid=".intval($aid)."";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($p_aid && $aid == null && $id_multi_str == null ) 
        {
            $this->key = $this->key.$p_aid;
            $this->sql = "select `aid` from `agent_info` where p_aid=".intval($p_aid)." order by init_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($p_aid_no_order && $aid == null && $id_multi_str == null && $sql == null)
        {
            $this->key = $this->key.$p_aid_no_order;
            $this->sql = "select `aid` from `agent_info` where p_aid=".intval($p_aid_no_order)."";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($aid  && $id_multi_str == '' && $p_aid == null && $type  && $status   )
        {
            $this->key = $this->key.$aid;
            $this->sql = "select `aid` from `agent_info` where type=".intval($type)." and status = ".intval($status)." and p_num = ".intval($aid)." order by init_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($p_num && $aid == null && $id_multi_str == null && $p_aid == null && $type == null && $status == null && $sql == null)
        {
            $this->key = $this->key.$aid;
            $this->sql = "select `aid` from `agent_info` where status = 2 and p_num = ".intval($p_num)." order by init_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        elseif($aid ==null && $id_multi_str == null && $p_aid == null && $type  && $status == null  ) 
        {
            $this->key = $this->key.$aid;
            $this->sql = "select `aid` from `agent_info` where type=".intval($type)." order by init_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        }
/*        elseif($aid ==null && $id_multi_str == null && $p_aid == null && $type == null  && $status == null && $p_aid_no_order == null)
        {
            $this->key = $this->key.$aid;
            $this->sql = "select `aid` from `agent_info` ";
            parent::__construct($dbobj, $this->key);
            return true;
        }*/
        elseif ($id_multi_str) 
        {
            $this->key = $this->key.md5($id_multi_str);
            parent::__construct($dbobj, $this->key, null, $id_multi_str);
            return true;
        }
        elseif($aid ==null && $id_multi_str == null && $p_aid == null && $type == null  && $status == null )
        {
            $this->key = $this->key;
            $this->sql = "select `aid` from `agent_info` order by init_time desc";
            parent::__construct($dbobj, $this->key);
            return true;
        }
        return false;
    }
}

