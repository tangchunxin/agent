<?php
namespace bigcat\model;

use bigcat\inc\BaseObject;
use bigcat\inc\BaseFunction;
class Income extends BaseObject
{
    const TABLE_NAME = 'income';

    public $out_trade_no;	//订单号
    public $uid = 0;	//用户 id
    public $total_fee = 0.0;	//订单金额
    public $start_time = '';	//开始时间
    public $end_time = '';	//结束时间
    public $status = 0;	//状态 0 提取中 1 提取成功  2 提取失败 

    public $notify_status = 0;	//回调通知状态 0 未通知 1 通知成功 2通知失败 3SYSTEMERROR
    public $third_trade_no = '';	//第三方交易号
    public $third_party_type = 0;	//第三方支付平台类别： 1 支付宝 2 微信 3 银联 4微信提现
    public $third_notify_info = '';	//第三方回调信息
    public $expire_time = 0;	//订单过期时间

    public $init_time = 0;	//创建时间
    public $update_time = 0;	//更新时间

    public function getUpdateSql() 
    {
        return "update `income` SET
            `uid`=".intval($this->uid)."
            , `total_fee`='".($this->total_fee)."'
            , `start_time`=".intval($this->start_time)."
            , `end_time`=".intval($this->end_time)."
            , `status`=".intval($this->status)."

            , `notify_status`=".intval($this->notify_status)."
            , `third_trade_no`='".BaseFunction::my_addslashes($this->third_trade_no)."'
            , `third_party_type`=".intval($this->third_party_type)."
            , `third_notify_info`='".BaseFunction::my_addslashes($this->third_notify_info)."'
            , `expire_time`=".intval($this->expire_time)."

            , `init_time`=".intval($this->init_time)."
            , `update_time`=".intval($this->update_time)."

            where `out_trade_no`=".intval($this->out_trade_no)."";
    }

    public function getInsertSql() 
    {
        return "insert into `income` SET

            `uid`=".intval($this->uid)."
            , `total_fee`='".($this->total_fee)."'
            , `start_time`=".intval($this->start_time)."
            , `end_time`=".intval($this->end_time)."
            , `status`=".intval($this->status)."

            , `notify_status`=".intval($this->notify_status)."
            , `third_trade_no`='".BaseFunction::my_addslashes($this->third_trade_no)."'
            , `third_party_type`=".intval($this->third_party_type)."
            , `third_notify_info`='".BaseFunction::my_addslashes($this->third_notify_info)."'
            , `expire_time`=".intval($this->expire_time)."

            , `init_time`=".intval($this->init_time)."
            , `update_time`=".intval($this->update_time)."
            ";
    }

    public function getDelSql() 
    {
        return "delete from `income`
            where `out_trade_no`=".intval($this->out_trade_no)."";
    }

    public function before_writeback() 
    {
        parent::before_writeback();
        return true;
    }

}

