<?php
namespace bigcat\model;

use bigcat\inc\BaseObject;
use bigcat\inc\BaseFunction;
class AgentInfo extends BaseObject
{
    const TABLE_NAME = 'agent_info';

    public $aid;	//代理ID
    public $wx_id = '';	//微信ID
    public $name = '';	//代理姓名
    public $provinces = '';	//所在省份
    public $city = '';	//所在城市

    public $p_aid = 0;  //推荐人ID
    public $type = 0;	//身份  身份(9总公司,1客服经理,2客服)
    public $opend_status = 0;	//开通代理的方式(1:默认,审核通过方式  2:直接开通代理)
    public $status = 0;	//审核状态(1:待审核通过  2:通过  3:拒绝,直接删除 4:已封号)
    public $audit_eid = 0;	//审核人id
    public $info = '';	//备注

    public $last_amount = 0;	//剩余钻数
    public $close_down_info = '';	//查封原因
    public $close_down_time = 0;	//解封时间
    public $init_time = 0;  //加入时间
    public $update_time = 0;	//更新加入时间
    public $month = 0;	//格式化时间201611
    public $shared = '' ;	//直属玩家充值
    public $sub_shared = '' ;	//下属玩家充值
    public $shared_info = '{"9":0,"8":0,"1":0,"2":0}' ;	//代理分成占比
    public $third_shared = '' ;	//下下属玩家充值

    public function getUpdateSql() 
    {
        return "update `agent_info` SET
            `wx_id`='".BaseFunction::my_addslashes($this->wx_id)."'
            , `name`='".BaseFunction::my_addslashes($this->name)."'
            , `provinces`='".BaseFunction::my_addslashes($this->provinces)."'
            , `city`='".BaseFunction::my_addslashes($this->city)."'

            , `p_aid`=".intval($this->p_aid)."
            , `type`=".intval($this->type)."
            , `opend_status`=".intval($this->opend_status)."
            , `status`=".intval($this->status)."
            , `audit_eid`=".intval($this->audit_eid)."
            , `info`='".BaseFunction::my_addslashes($this->info)."'

            , `last_amount`=".intval($this->last_amount)."
            , `close_down_info`='".BaseFunction::my_addslashes($this->close_down_info)."'
            , `close_down_time`=".intval($this->close_down_time)."
            , `p_num`=".intval($this->p_num)."
            , `init_time`=".intval($this->init_time)."
            , `update_time`=".intval($this->update_time)."
            , `month`=".intval($this->month)."
            , `shared`='".($this->shared)."'
            , `sub_shared`='".($this->sub_shared)."'
            , `shared_info`='".BaseFunction::my_addslashes($this->shared_info)."'
            , `third_shared`='".($this->third_shared)."'

            where `aid`=".intval($this->aid)."";
    }
    public function getUpdateSql_Id() 
    {
        return "update `agent_info` SET        
            `aid`='".intval($this->aid)."'
            ,`wx_id`='".BaseFunction::my_addslashes($this->wx_id)."'
            , `name`='".BaseFunction::my_addslashes($this->name)."'
            , `provinces`='".BaseFunction::my_addslashes($this->provinces)."'
            , `city`='".BaseFunction::my_addslashes($this->city)."'

            , `p_aid`=".intval($this->p_aid)."
            , `type`=".intval($this->type)."
            , `opend_status`=".intval($this->opend_status)."
            , `status`=".intval($this->status)."
            , `audit_eid`=".intval($this->audit_eid)."
            , `info`='".BaseFunction::my_addslashes($this->info)."'

            , `last_amount`=".intval($this->last_amount)."
            , `close_down_info`='".BaseFunction::my_addslashes($this->close_down_info)."'
            , `close_down_time`=".intval($this->close_down_time)."
            , `p_num`=".intval($this->p_num)."
            , `init_time`=".intval($this->init_time)."
            , `update_time`=".intval($this->update_time)."
            , `month`=".intval($this->month)."
            , `shared`='".BaseFunction::my_addslashes($this->shared)."'
            , `sub_shared`='".($this->sub_shared)."'
            , `shared_info`='".($this->shared_info)."'
            , `third_shared`='".($this->third_shared)."'
             where `aid`=".intval(substr($this->aid,2,11))."";
    }

    public function getInsertSql() 
    {
        return "insert into `agent_info` SET

            `aid`=".intval($this->aid)."
            , `wx_id`='".BaseFunction::my_addslashes($this->wx_id)."'
            , `name`='".BaseFunction::my_addslashes($this->name)."'
            , `provinces`='".BaseFunction::my_addslashes($this->provinces)."'
            , `city`='".BaseFunction::my_addslashes($this->city)."'

            , `p_aid`=".intval($this->p_aid)."
            , `type`=".intval($this->type)."
            , `opend_status`=".intval($this->opend_status)."
            , `status`=".intval($this->status)."
            , `audit_eid`=".intval($this->audit_eid)."
            , `info`='".BaseFunction::my_addslashes($this->info)."'

            , `last_amount`=".intval($this->last_amount)."
            , `close_down_info`='".BaseFunction::my_addslashes($this->close_down_info)."'
            , `close_down_time`=".intval($this->close_down_time)."
            , `p_num`=".intval($this->p_num)."
            , `init_time`=".intval($this->init_time)."
            , `update_time`=".intval($this->update_time)."
            , `month`=".intval($this->month)."
            , `shared`='".($this->shared)."'
            , `sub_shared`='".($this->sub_shared)."'
            , `third_shared`='".($this->third_shared)."'
            , `shared_info`='".BaseFunction::my_addslashes($this->shared_info)."'
            ";
    }

    public function getDelSql() 
    {
        return "delete from `agent_info`
            where `aid`=".intval($this->aid)."";
    }

    public function before_writeback() 
    {
        parent::before_writeback();
        return true;
    }

}

