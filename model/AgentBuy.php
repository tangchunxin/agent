<?php
namespace bigcat\model;

use bigcat\inc\BaseObject;
use bigcat\inc\BaseFunction;
class AgentBuy extends BaseObject
{
    const TABLE_NAME = 'agent_buy';

    public $buy_aid;	//自增长id
    public $aid = 0;	//代理ID
    public $money = 0.0;	//购钻金额
    public $buy_amount = 0;	//购钻数量
    public $buy_status = 0;	//购钻来源(1:支付宝   2:手动购钻 3:代理返利  4:活动奖励 5:充值奖励)

    public $activity_info = '';	//详情(如有活动奖励钻,需说明详细情况)
    public $handler = 0;	//操作人id
    public $buy_time = 0;	//购钻时间
    public $month = 0;	//月份

    public function getUpdateSql() 
    {
        return "update `agent_buy` SET
            `aid`=".intval($this->aid)."
            , `money`='".($this->money)."'
            , `buy_amount`=".intval($this->buy_amount)."
            , `buy_status`=".intval($this->buy_status)."

            , `activity_info`='".BaseFunction::my_addslashes($this->activity_info)."'
            , `handler`=".intval($this->handler)."
            , `buy_time`=".intval($this->buy_time)."
            , `month`=".intval($this->month)."

            where `buy_aid`=".intval($this->buy_aid)."";
    }

    public function getInsertSql() 
    {
        return "insert into `agent_buy` SET

            `aid`=".intval($this->aid)."
            , `money`='".($this->money)."'
            , `buy_amount`=".intval($this->buy_amount)."
            , `buy_status`=".intval($this->buy_status)."

            , `activity_info`='".BaseFunction::my_addslashes($this->activity_info)."'
            , `handler`=".intval($this->handler)."
            , `buy_time`=".intval($this->buy_time)."
            , `month`=".intval($this->month)."
            ";
    }

    public function getDelSql() 
    {
        return "delete from `agent_buy`
            where `buy_aid`=".intval($this->buy_aid)."";
    }

    public function before_writeback() 
    {
        parent::before_writeback();
        return true;
    }

}

