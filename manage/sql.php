<?php
exit();
-- phpMyAdmin SQL Dump
-- version 4.6.2
-- https://www.phpmyadmin.net/
--
-- Host: 100.91.230.51:6504
-- Generation Time: 2017-08-31 09:52:40
-- 服务器版本： 5.6.28-cdb20160902-log
-- PHP Version: 5.6.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fair_agent`
--
CREATE DATABASE IF NOT EXISTS `fair_agent` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `fair_agent`;

-- --------------------------------------------------------

--
-- 表的结构 `agent_buy`
--

CREATE TABLE  `agent_buy` (
  `buy_aid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增长id',
  `aid` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '代理ID',
  `money` float(11,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '购钻金额',
  `buy_amount` int(11) NOT NULL DEFAULT '0' COMMENT '购钻数量',
  `buy_status` tinyint(2) UNSIGNED NOT NULL DEFAULT '0' COMMENT ' 购钻来源(9:代理扣钻 1:支付宝 2:官方手动充值钻 3:代理返利 4:活动奖励 5:充值奖励 6:微信充值)',
  `activity_info` varchar(512) NOT NULL DEFAULT '' COMMENT '详情(如有活动奖励钻,需说明详细情况)',
  `handler` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人id',
  `buy_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购钻时间',
  `month` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '月份',
  PRIMARY KEY (`buy_aid`),
  KEY `aid` (`aid`),
  KEY `handler_buy_status` (`handler`,`buy_status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=402 DEFAULT CHARSET=utf8 COMMENT='代理购钻表';

-- --------------------------------------------------------

--
-- 表的结构 `agent_info`
--

CREATE TABLE  `agent_info` (
  `aid` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '代理ID',
  `wx_id` char(32) NOT NULL DEFAULT '' COMMENT '微信ID',
  `name` char(32) NOT NULL DEFAULT '' COMMENT '代理姓名',
  `provinces` char(32) NOT NULL DEFAULT '' COMMENT '所在省份',
  `city` char(32) NOT NULL DEFAULT '' COMMENT '所在城市',
  `p_aid` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '推荐人ID',
  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '身份(9总公司8,总代理,1客服经理,2客服)',
  `opend_status` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '游戏账号',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '审核状态(1:待审核通过  2:通过  3:拒绝,直接删除 4:已封号)',
  `audit_eid` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '审核人id',
  `info` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
  `last_amount` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '剩余钻数',
  `close_down_info` varchar(200) NOT NULL DEFAULT '' COMMENT '查封原因',
  `p_num` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '推荐码',
  `close_down_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '解封时间',
  `init_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '加入时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `month` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '格式化时间201611',
  `shared` float(3,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '直属玩家充值比',
  `sub_shared` float(3,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '下属玩家充值比',
  `shared_info` char(70) NOT NULL DEFAULT ''  COMMENT  '代理分成占比' AFTER `sub_shared`,
  PRIMARY KEY (`aid`),
  KEY `p_aid` (`p_aid`) USING BTREE,
  KEY `type_status` (`type`,`status`,`p_num`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理信息表';

-- --------------------------------------------------------

--
-- 表的结构 `play_recharge`
--

CREATE TABLE  `play_recharge` (
  `play_rid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增长id\n',
  `play_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '玩家id\n',
  `play_name` char(32) NOT NULL DEFAULT '' COMMENT '玩家昵称 ',
  `last_amount` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '玩家剩余钻数',
  `recharge_amount` int(11) NOT NULL DEFAULT '0' COMMENT '玩家充值钻数',
  `play_sum_amount` int(11) UNSIGNED NOT NULL COMMENT '充值后钻数量',
  `activity_info` varchar(512) NOT NULL COMMENT '充值或扣钻原因',
  `aid` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '充钻的代理ID',
  `recharge_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '充值时间',
  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '充值类型 2 代理充值  23 后台红钻充值 35后台充值积分  43后台充值奖杯',
  PRIMARY KEY (`play_rid`),
  KEY `recharge_time` (`recharge_time`) USING BTREE,
  KEY `aid` (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=352 DEFAULT CHARSET=utf8 COMMENT='玩家充值表';


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE `income` (
  `out_trade_no` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单号',
  `uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户 id',
  `total_fee` float(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `start_time` int(11) DEFAULT NULL COMMENT '收益开始时间>=',
  `end_time` int(11) unsigned NOT NULL COMMENT '收益结束时间<',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0 未提取 1 提取成功',
  `notify_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '回调通知状态 0 未通知 1 通知成功 2通知失败 3SYSTEMERROR',
  `third_trade_no` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方交易号',
  `third_party_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '第三方支付平台类别： 1 支付宝 2 微信 3 银联 4企业付款等',
  `third_notify_info` varchar(2048) NOT NULL DEFAULT '' COMMENT '第三方回调信息',
  `expire_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单过期时间',
  `init_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`out_trade_no`),
  KEY `uid` (`uid`)
 ) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;


 CREATE TABLE `user` (
  `uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `key` char(8) NOT NULL DEFAULT '' COMMENT '登录key',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '审核状态(0:通过  1:已封号)',
  `wx_openid` char(64) NOT NULL COMMENT '微信openID',
  `wx_pic` varchar(256) NOT NULL DEFAULT '' COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `name` char(32) NOT NULL DEFAULT '' COMMENT '用户名字',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `province` char(32) NOT NULL DEFAULT '' COMMENT '省',
  `city` char(32) NOT NULL DEFAULT '' COMMENT '市',
  `init_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `real_name_reg` varchar(64) NOT NULL COMMENT '实名制登记',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `wx_openid` (`wx_openid`) USING BTREE,
  KEY `init_time` (`init_time`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';


ALTER TABLE `play_recharge` 
ADD `type` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT  '充值类型 2 代理充值  23 后台红钻充值 35后台充值积分  43后台充值奖杯' AFTER `recharge_time`;


ALTER TABLE `agent_info` 
ADD `shared` char(64) NOT NULL DEFAULT ''  COMMENT  '直属玩家充值' AFTER `month`;
ALTER TABLE `agent_info` 
ADD `sub_shared` char(64) NOT NULL DEFAULT ''  COMMENT  '下属玩家充值' AFTER `shared`;
ALTER TABLE `agent_info` 
ADD `shared_info` char(64) NOT NULL DEFAULT ''  COMMENT  '代理分成占比' AFTER `sub_shared`;

///////////////
update `agent_info` set shared='',sub_shared='' where 1;

ALTER TABLE`agent_info`
MODIFY `shared`  float(3,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '直属玩家充值比';

ALTER TABLE`agent_info`
MODIFY `sub_shared`  float(3,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '下属玩家充值比';




ALTER TABLE `agent_info` 
ADD `shared_info` char(70) NOT NULL DEFAULT ''  COMMENT  '代理分成占比' AFTER `sub_shared`;


/////
update `agent_info` set `opend_status`=0 where 1;
ALTER TABLE`agent_info`
MODIFY `opend_status`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '游戏账号';

20171128
ALTER TABLE`agent_info`
MODIFY  `shared_info` char(70)  NOT NULL DEFAULT '' COMMENT '代理分成占比';


20171212
ALTER TABLE `agent_info` 
ADD `third_shared` float(3,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT  '下级代理分成占比' AFTER `shared_info`;

ALTER TABLE`agent_info`
MODIFY  `shared_info` char(75)  NOT NULL DEFAULT '' COMMENT '代理分成占比';


