<?php
/**
 * @author tangchunxin
 * @final 20170812
 */

exit();

//天津
http://test.linfiy.com/mahjong/game_agent/city_agent_tianjin/index.php?randkey=&c_version=0.0.1&parameter={"mod":"Business","platform":"gfplay","act":"boss_set_shared","aid":8618911554496,"key":"NCBDpay","shop":"20171018","shared":{"9":"0.4","8":"0.6","1":"0.45","2":"0.30"}} 
//保定
http://test.linfiy.com/mahjong/game_agent/city_agent_baodingnew/index.php?randkey=&c_version=0.0.1&parameter={"mod":"Business","platform":"gfplay","act":"boss_set_shared","aid":8618911554496,"key":"NCBDpay","shop":"20171018","shared":{"9":"0.35","8":"0.65","1":"0.50","2":"0.35"}} 
//廊坊
http://test.linfiy.com/mahjong/game_agent/city_agent_langfang/index.php?randkey=&c_version=0.0.1&parameter={"mod":"Business","platform":"gfplay","act":"boss_set_shared","aid":8618911554496,"key":"NCBDpay","shop":"20171018","shared":{"9":"0.2","8":"0.8","1":"0.6","2":"0.40"}} 
//沧州
http://test.linfiy.com/mahjong/game_agent/city_agent/index.php?randkey=&c_version=0.0.1&parameter={"mod":"Business","platform":"gfplay","act":"boss_set_shared","aid":8618911554496,"key":"NCBDpay","shop":"20171018","shared":{"9":"0.4","8":"0.6","1":"0.45","2":"0.30"}} 
http://test.linfiy.com/mahjong/game_agent/city_agent/index.php?randkey=&c_version=0.0.1&parameter={"mod":"Business","platform":"gfplay","act":"boss_set_shared","aid":8618911554496,"key":"NCBDpay","shop":"20171018","shared":{"9":"0.15","8":"0.85","1":"0.65","2":"0.50"}} 

//地址(公网)，如果是服务端调用可以用内网地址
http://test2.gfplay.cn/mahjong/game_agent/city_agent/index.php

//权限地址
http://test2.gfplay.cn/power_control/index.php

//用户登录
http://test2.gfplay.cn/user_php70/index.php
////////////////////////////////////////////////

//协议规则
urlencode的格式用户信息（源格式json的）


//例子
$data = array('mod'=>'Business', 'act'=>'login', 'platform'=>'game', 'uid'=>'13671301110');
$randkey = encryptMD5($data);
$_REQUEST = array('randkey'=>$randkey, 'c_version'=>'0.0.1', 'parameter'=>json_encode($data) );

////////////////系统协议//////////////////////////
//1.配置数据  get_conf  ok
//2.登录 获取人员基本信息  ok
//3.添加客服经理 或客服（ 姓名 电话 手机验证码 ）  ok
//4.自己下级人员列表   {9公司 -> 8城市合伙人   ,8城市合伙人 -> 1会长,    1会长 -> 2副会长  }  ok
//5.特殊权限  直接删除人员信息   ok
//6.代理搜索   ok
//7.修改下级代理信息   ok


//11.玩家列表  按条件查询  (1.按照代理手机号  2.按照玩家id   3.按照时间) ok
//12.特殊权限  给玩家充值  ok
//13.玩家充值记录  (1.按照代理手机号  2.按照玩家id   3.按照时间) ok
//14.把玩家踢出工会   远程调用协议  ok
//15.给玩家拉入黑名单   远程调用协议   ok
//16.玩家黑名单列表   远程调用协议 ok  (搜索最后统一加)
//17.代理收益统计表   远程调用协议
//18.特殊权限 给玩家充值的时候  无限制的  玩家搜索  ok
//19.更改代理  ok
//20.查询玩家消耗情况  ok
//221.查询玩家录像播放码  ok


//21.获得支付模块的订单号等信息
//22.支付成功回调 充钻  -----凯哥 你不需要这个协议
//23.读取kpi信息(新增  ,活跃 ,耗钻 ,总用户 ,日牌局 ,平局牌局  ,平局时长)


/////////////////开始/////////////////////////

//1.配置数据  get_conf  ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		act: 'get_conf'
		platform: 'tocar'
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 , 2
	sub_desc	//sub_code 描述	   1 登录错误, 2没有记录
	data:


//2.登录 获取人员基本信息 ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'agent_login_info'
		platform: 'tocar'
        aid:18911554499              //账号id
        key:
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2您还未开通代理身份  8,您的代理身份正在审核中 9,您的账号已被查封
	sub_desc	//sub_code 描述
	data:
	 "agent_info": [
      {
        "aid": 18911554496,     //账号id
        "wx_id": "0",
        "name": "唐纯鑫",          //姓名
        "provinces": "",
        "city": "",
        "p_aid": 0,               //上级客户经理
        "type": 9,     //身份  身份(9总公司,1客服经理,2客服)
        "opend_status": 2,
        "status": 2,
        "audit_eid": 18911554496,      //操作人id
        "info": "",
        "last_amount": 10000000,        //剩余钻数
        "close_down_info": "",
        "close_down_time": 0,
        "init_time": 1479144471,
        "month": 201611,
        "date_init_time": "2016-11-15"      //加入时间
      }
    ]

//3.添加客服经理 或客服（ 姓名 电话 手机验证码 ）ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'add_agent'
		platform: 'gfplay'
        aid:18911554499     //登录的客服(经理) aid(即:手机号)
        key:           //登录key
        *num:123456//公司添加城市合伙人 不需要短信验证码   (城市合伙人添加会长; 会长添加副会长需要这个字段)
        children_aid:     //被开通客服(经理)手机号
        name:    //姓名
        wx_id:   //   微信号
        city:
        provinces:
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1登录错误    2无此权限  3  该账号已存在  4  手机验证码错误 5添加失败或已经超过人员数量限制 6 您的账号不存在
	sub_desc	//sub_code 描述
	data:


//4.自己下级人员列表   {9公司 -> 8城市合伙人   ,8城市合伙人 -> 1会长,    1会长 -> 2副会长  }  ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'agent_info_list'
		platform: 'tocar'
        aid:18911554499     //登录的客服(经理) aid(即:手机号)
        key:           //登录key
        page:1
        p_aid://   查询下级人员的 aid
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2您还未开通代理身份 6无此权限   8您的身份正在审核中
	sub_desc	//sub_code 描述
		"data": {
	    "data_count": 2,
	    "page_count": 10,
	    "agent_info":
	    [
	      {
	        "aid": 18911552222,
	        "wx_id": "",
	        "name": "唐客服经理A",
	        "provinces": "",
	        "city": "",
	        "p_aid": 18911554496,
	        "type": 1,
	        "opend_status": 0,
	        "status": 2,
	        "audit_eid": 18911554496,
	        "info": "",
	        "last_amount": 0,
	        "close_down_info": "",
	        "close_down_time": 0,
	        "init_time": 1482232757,
	        "month": 201612
	      },

	    ]
	  },


//5.特殊权限  直接删除人员信息   ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		act: 'delete_agent_info'
		platform: 'tocar'
		shop:'sichuan0001'
		aid:
		key:
		del_aid://被删除代理
		type:1    //1删除
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 ,1,2
	sub_desc	//sub_code 描述	 1 登录错误, 2无此权限   3.无此记录

//6.代理搜索
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'search_agent'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:
        search_aid:   //被充值id号
        type:2   //表示可以全部代理,,,,(可以不传,表示只能查询下级代理)
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误 2记录不存在 4 扣钻失败 5余额不足 6人员关系错误
	sub_desc	//sub_code 描述
	"data":
	{
	    "list": [
	      {
	        "big_agent_agent_info_multi__18911551111": {
	          "aid": 18911551111,
	          "wx_id": "",
	          "name": "唐客服经理",
	          "provinces": "",
	          "city": "",
	          "p_aid": 18911552222,
	          "type": 1,
	          "opend_status": 0,
	          "status": 2,
	          "audit_eid": 18911552222,
	          "info": "",
	          "last_amount": 2086,
	          "close_down_info": "",
	          "close_down_time": 0,
	          "init_time": 1482226218,
	          "update_time": 0,
	          "month": 201612,
	          "p_num": 18911552222
	        }
	      }
	    ]
  },

//7.修改下级代理信息   ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'chmod_agent_info'
		platform: 'gfplay'
        aid:8618911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        children_aid:8618911551111   //下级代理id
        name:   //名字  为空时候不做修改
        wx_id:   //微信id    为空时候不做修改

response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2非下级成员  3.下级成员不存  4.操作失败
	sub_desc	//sub_code 描述
	data:

//11. 玩家列表  按条件查询  (1.按照代理手机号  2.按照玩家id   3.按照时间)  OK
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'play_list'
		platform: 'gfplay'
        aid:8618911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        *agent_id:10200          //被查询代理手机号  可以为空
        page:  1        //分页页码
        *play_id:    //玩家id  可为空
        *start_time:  20170101   //起止时间  可为空
        *end_time:  20170808   //终止时间  可为空
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2没有记录
	sub_desc	//sub_code 描述
	data:
	[
		{
	      "uid": 10055,    //玩家id
	      "currency": 2458,   //当前剩余钻
	      "room": 0,       //当前所在房间号
	      "is_room_owner": 1,  //是否为房主
	      "update_time": "2017-05-10 10:21:38",   //注册时间
	      "last_game_time": 1497346801,     //最后修改时间
	      "currency2": 0,       //第二种货币剩余个数
	      "agent_id": 8618310721990,   //绑定的推广员
	      "sum_money": 0,    //共计充值金额
	      "sum_currency": 0,   //共计消耗钻石数量
	      "status": 0,      //状态  0正常  1黑名单中
	      "name": "方问春",   //名字
	      "wx_pic": "https://wx.qlogo.cn/mmopen/OxUBpiaYgpHg0gjXEIscHkja5LFicxDeRpHILClC86zf9eM73najlswllMkIhSc0y4upOuh2ZgMPWPSS53yRx9b5L0cMNT5DuK/132"  //头像地址
    	}
    ]


//12 给玩家充值
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'play_recharge'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        shop:'city_agent0001'    //权限验证
        p_aid:10200          //玩家id
        recharge_amount:100   //客服(经理)给  玩家充的钻数量
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 ,  1登录错误  2记录不存在  3余额不足   4扣钻不成功
	sub_desc	//sub_code 描述
	data:

//13.玩家充值记录  OK  但 还不能查询
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'play_recharge_list_new'
		platform: 'gfplay'
        aid:18911554499     //登录的客服(经理) aid(即:手机号)
        key:           //登录key
        page: 1,2   //页码
        *keywords:     //搜索关键字,推广号id或者玩家id 有则传,没有则不传
        *start_time:  2017-01-01   //起止时间  可为空
        *end_time:  2017-08-08   //终止时间  可为空
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2请先开通客服(经理)身份,在新增下级客服(经理) 3客服(经理)已经存在,请勿重复添加   4短信验证码错误
	sub_desc	//sub_code 描述
	"data_count": 336,
    "page_count": 20,
	"data":
	[
		{
	        "id": 91,      //表的主键
		    "uid": 10055,    //玩家id
		    "old_currency": 200,   //充值之前的钻石
		    "currency": 3,    //钻石变化
		    "type": 4,            //支付方式  1 消费扣钻  2 代理充值   3 微信分享充值   4微信支付
		    "time": "2017-05-12 09:11:45",
		    "money": 21,    //充值金额
		    "name": "方问春"
		},

    ]


//14.把玩家踢出工会   远程调用协议  ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'bind_out_agent_id'
		platform: 'gfplay'
        aid:18911554499          //代理账号id
        key:
        play_uid:hb0001          //玩家id
        agent_id:                //工会id
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 3 解绑失败
	sub_desc	//sub_code 描述


//15.给玩家拉入黑名单   远程调用协议   需要在user_game表里面新增字段
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'pull_blacklist'
		platform: 'gfplay'
        aid:18911554499          //代理账号id
        uid:10010          //玩家id
        agent_id:                //代理推广号
        key:
        status:            //  1 拉入黑名单 0 解除黑名单
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 3 解绑失败
	sub_desc	//sub_code 描述

//16.黑名单列表   远程调用协议
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'blacklist'
		platform: 'gfplay'
        aid:18911554499          //代理账号id
        key:
        page:  1        //分页页码
        *keywords: 搜索关键字(包含玩家id和代理推广号)

response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 3 解绑失败
	sub_desc	//sub_code 描述
    "data": {
        "list": [
          {
              "uid": 10052,
            "currency": 28,
            "room": 0,
            "is_room_owner": 1,
            "update_time": "2017-05-09 22:41:00",
            "last_game_time": 1501686485,
            "currency2": 0,
            "agent_id": 8613263163111,
            "sum_money": 0,
            "sum_currency": 0,
            "status": 1,
            "name": "天",
            "wx_pic": "https://wx.qlogo.cn/mmopen/OxUBpiaYgpHg0gjXEIscHkja5LFicxDeRpHILClC86zf9eM73najlswllMkIhSc0y4upOuh2ZgMPWPSS53yRx9b5L0cMNT5DuK/132"
          },
          {
              "uid": 10051,
            "currency": 128,
            "room": 0,
            "is_room_owner": 1,
            "update_time": "2017-05-04 11:27:10",
            "last_game_time": 1495708339,
            "currency2": 0,
            "agent_id": 8613263163112,
            "sum_money": 0,
            "sum_currency": 0,
            "status": 1,
            "name": "UnDaily",
            "wx_pic": "https://wx.qlogo.cn/mmopen/yCAYGEzGPC1DSvfhXMiaEKtJzzoJj8TermUMVGlRbibVh2eRnZzkDcOQZBUabevRrn49FN4eL1bKteCRfNkBTQDCTYDV0rFLRQ/132"
          }
    ],
    "data_count": 2,
    "page_count": 20


//17.代理收益统计表   远程调用协议
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'income_statistics'
		platform: 'gfplay'
        aid:18911554499          //代理账号id
        key:
        page:
        *p_aid                 //被点击人的agent_id
        *keywords:            //按推广员ID搜索
        *start_time:         //开始时间
        *end_time:           //结束时间

response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 3 解绑失败
	sub_desc	//sub_code 描述
      "data": {
            "list": [
          {
              "aid": 8613263163001,
            "wx_id": "",
            "name": "合伙人1",
            "provinces": "河北省",
            "city": "沧州市",
            "p_aid": 8613263163888,
            "type": 8,
            "opend_status": 2,
            "status": 2,
            "audit_eid": 8618911554496,
            "info": "",
            "last_amount": 58,
            "close_down_info": "",
            "close_down_time": 0,
            "init_time": 1498546992,
            "update_time": 1501487843,
            "month": 201706,
            "p_num": 8613263163001,
            "total_recharge": "5.00",   //总充值
            "income_percent": "5.00", //分成占比
            "total_income": 2.9250000000000003, //应得收益
            "subordinate_income": 0.65,       //下属贡献
          }
        ],
        "data_count": 4,
        "page_count": 20,
        "total_recharge": 5,
        "my_total_income": 4.275,
        "my_percent": 0.65,
        "subordinate_income"      //下属贡献
      },

//18.特殊权限 给玩家充值的时候  无限制的  玩家搜索  ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'play_find_by_id'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        p_aid:10200          //玩家id
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2没有记录
	sub_desc	//sub_code 描述
	data:
		"play_user":
		[
	      {
		      "userId": 10200,  //玩家id
		      "nickName": "峥",  //玩家昵称
		      "specialGold": 100  //剩余钻数
	      }
	    ]
//19.更改代理
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'change_agent'
		platform: 'tocar'
        aid_of_operator:18911554499              //操作人id
        aid_before_change:      //更改前代理id
        aid_after_change:       //更改后代理id
        key:                    //登录key
        name:                   //姓名
        wx_id:                  //微信id
        num:                    //验证码
        provinces:              //省份
        city:                   //城市
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2没有记录
	sub_desc	//sub_code 描述
	data:
		"list":
		[]

//20.查询玩家消耗情况  ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'find_play_recharge'
		platform: 'gfplay'
		aid:8618911554496
		key:123456
		play_uid:10068
		shop:city0001
		page:  1

response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2没有记录
	sub_desc	//sub_code 描述
	"data": {
	    "data_count": 75,
	    "page_count": 20,
	    "list": [
	      {
	        "id": 3255,       //主键id
	        "uid": 10145,			//玩家id
	        "old_currency": 296,    //充值之前钻石数量
	        "currency": -2,          //充值数量
	        "type": "开房消耗",       //充值类型   1开房消费  2代理充值   3微信分享  4微信支付
	        "time": "2017-09-07 10:01:54",   //充值时间
	        "money": "0.00"       //充值金额
	      }
	   ]
	}
//221.查询玩家录像播放码  ok
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'find_play_video'
		platform: 'gfplay'
		aid:8618911554496
		key:123456
		play_uid:10068
		shop:city0001
		page:  1

response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2没有记录
	sub_desc	//sub_code 描述
	"data": {
	    "data_count": 75,
	    "page_count": 20,
	     "list": [
			      {
			        "id": 1427,  //播放码
			        "rid": 56553,  //房间号
			        "uid": 10076,  //房主uid
			        "time": "2017_09-08 15:38:29",  //时间
			        "game_type": 126,    //游戏类别
			        "play_time": 138   //本局时间
			      },
			      {
			        "id": 1425,
			        "rid": 56553,
			        "uid": 10076,
			        "time": "2017_09-08 15:36:04",
			        "game_type": 126,
			        "play_time": 281
			      }
			    ]
	      }
	   ]
	}

//23 读取kpi信息
request:
	randkey
	c_version
	parameter
		mod: 'Business'
		act: 'kpi_get'
		platform: 'tocar'	//或者 gfplay_ios
		aid:   //代理id
        key:
response:
	code //是否成功 0成功
	desc	//描述
    "data": {
        "list": {
            "kpi_kpi_multi__269": {
                "id": 269,
        "id_time": "2017-08-24",
        "all_user": 80,
        "new_user": 0,
        "active_user": 11,
        "game_num": 0,
        "hour_user": "{\"13\":11}",
        "recharge_direct": "0.00",
        "recharge_subordinate": "0.00",
        "currency_direct": -14,
        "currency_subordinate": 0,
        "play_time": 0,
        "agent_id": 8600000000000
      },
      "kpi_kpi_multi__280": {
                "id": 280,
        "id_time": "2017-08-23",
        "all_user": 80,
        "new_user": 0,
        "active_user": 11,
        "game_num": 0,
        "hour_user": "{\"13\":11}",
        "recharge_direct": "0.00",
        "recharge_subordinate": "0.00",
        "currency_direct": -14,
        "currency_subordinate": 0,
        "play_time": 0,
        "agent_id": 8600000000000
      }
    }
  },





///////////////////////////////////////////////////////
///////////////////旧版微信登录版协议////////////////////////////////
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'chmod_p_aid'
		platform: 'gfplay'
        aid:18911554499              //账号id
        key:
        shop:hb0001          //权限
        search_aid:   //查找要被替换的手机号
        chmod_aid:     //替换成新的手机号        info:           //备注信息   可为空
        type:         //1:更换上级代理   2:  Ａ下级代理合并给Ｂ
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2权限错误
	sub_desc	//sub_code 描述


request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'chmod_agent_id'
		platform: 'gfplay'
        aid:18911554499              //账号id
        key:
        shop:hb0001          //权限
        search_aid:   //查找要被替换的手机号
        chmod_aid:     //替换成新的手机号
        info:           //备注信息   可为空
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2权限错误
	sub_desc	//sub_code 描述


request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'kpi_get_all'
		platform: 'tocar'
        aid:18911554499              //账号id
        key:
        shop:kpi0001
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2您还未开通代理身份  8,您的代理身份正在审核中 9,您的账号已被查封
	sub_desc	//sub_code 描述
	"data":
	{
	    "sichuan": [
	      {
	        "id_time": "2017-04-10",
	        "all_user": 1559,
	        "new_user": 31,
	        "active_user": 151,
	        "game_num": 1656,
	        "hour_user": {
	          "10": "46",
	          "11": "49",
	          "12": "56",
	          "13": "59",
	          "14": "64",
	          "15": "56",
	          "00": "23",
	          "01": "5",
	          "02": "4",
	          "03": "0",
	          "04": "0",
	          "05": "0",
	          "06": "7",
	          "07": "5",
	          "08": "34",
	          "09": "38"
	        },
	        "currency": -512
	      },
	      {
	        "id_time": "2017-04-09",
	        "all_user": 1528,
	        "new_user": 67,
	        "active_user": 262,
	        "game_num": 4104,
	        "hour_user": {
	          "10": "64",
	          "11": "63",
	          "12": "67",
	          "13": "75",
	          "14": "63",
	          "15": "65",
	          "16": "68",
	          "17": "62",
	          "18": "68",
	          "19": "89",
	          "20": "116",
	          "21": "125",
	          "22": "88",
	          "23": "48",
	          "00": "28",
	          "01": "7",
	          "02": "0",
	          "03": "0",
	          "04": "0",
	          "05": "0",
	          "06": "6",
	          "07": "9",
	          "08": "21",
	          "09": "38"
	        },
	        "currency": -1384
	      }
	    ],
	    "chengde": [
	      {
	        "id_time": "2017-04-10",
	        "all_user": 1559,
	        "new_user": 31,
	        "active_user": 151,
	        "game_num": 1656,
	        "hour_user": {
	          "10": "46",
	          "11": "49",
	          "12": "56",
	          "13": "59",
	          "14": "64",
	          "15": "56",
	          "00": "23",
	          "01": "5",
	          "02": "4",
	          "03": "0",
	          "04": "0",
	          "05": "0",
	          "06": "7",
	          "07": "5",
	          "08": "34",
	          "09": "38"
	        },
	        "currency": -512
	      },
	      {
	        "id_time": "2017-04-09",
	        "all_user": 1528,
	        "new_user": 67,
	        "active_user": 262,
	        "game_num": 4104,
	        "hour_user": {
	          "10": "64",
	          "11": "63",
	          "12": "67",
	          "13": "75",
	          "14": "63",
	          "15": "65",
	          "16": "68",
	          "17": "62",
	          "18": "68",
	          "19": "89",
	          "20": "116",
	          "21": "125",
	          "22": "88",
	          "23": "48",
	          "00": "28",
	          "01": "7",
	          "02": "0",
	          "03": "0",
	          "04": "0",
	          "05": "0",
	          "06": "6",
	          "07": "9",
	          "08": "21",
	          "09": "38"
	        },
	        "currency": -1384
	      }
	    ]
 	},
  "module": "Business",
  "action": "kpi_get"

//特殊权限 下载全部代理购钻和消耗明细
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'all_agent_buy_list'
		platform: 'tocar'
        aid:8618911554499              //账号id
        key:
        time_end:20170702    //今天时间
        shop:'fair0001'  //权限验证
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型
	sub_desc	//sub_code 描述	   1登录错误   2无此权限  3暂无数据
	data:


//特殊权限 boss添加公司人员
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'boss_add_agent'
		platform: 'tocar'
        aid:18911554499              //账号id
        key:
        children_aid:   //被添加的代理
        name: //名字
        type: 9  //9公司身份,必须是9
        shop:'fair0001'  //权限验证
        provinces:  //省份
        city:   //城市
        last_amount:0  //充值钻数
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型
	sub_desc	//sub_code 描述	   1登录错误   2无此权限  3被添加人员身份错误  4添加失败
	data:

//特殊权限  直接删除人员信息
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		act: 'delete_agent_info'
		platform: 'tocar'
		shop:'sichuan0001'
		aid:
		key:
		del_aid://被删除代理
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 ,1,2
	sub_desc	//sub_code 描述	 1 登录错误, 2无此权限   3.无此记录


//  公司人员直接给代理充值 ,有权限
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'boss_recharge_agent'
		platform: 'tocar'
        aid:18911554499              //账号id
        key:
        shop:fair0001  //权限验证
        recharge_aid:   //被充值的代理
        last_amount:    //充值钻数
        type:   //1:支付宝 2:官方手动充值钻 3:代理返利 4:活动奖励 6:微信充值  9:代理扣钻
        activity_info:            //官方充值 原因备注
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型
	sub_desc	//sub_code 描述	   1登录错误   2无此权限  3充值数量错误  4该用户不存在
	data:


//   公司人员给代理或玩家扣钻 ,有权限
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'del_agent_amount'
		platform: 'tocar'
        aid:18911554499              //账号id
        key:
        shop:fair0001  //权限验证
        recharge_aid:   //被充值的代理
        last_amount:    //充值钻数
        type:   //    1玩家   2代理
        activity_info:            //官方充值 原因备注
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2您还未开通代理身份  8,您的代理身份正在审核中 9,您的账号已被查封
	sub_desc	//sub_code 描述
	data:


//   登录 获取人员(客服经理,客服)基本信息
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'agent_login_info'
		platform: 'tocar'
        aid:18911554499              //账号id
        key:
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2您还未开通代理身份  8,您的代理身份正在审核中 9,您的账号已被查封
	sub_desc	//sub_code 描述
	data:
	 "agent_info": [
      {
        "aid": 18911554496,     //账号id
        "wx_id": "0",
        "name": "唐纯鑫",          //姓名
        "provinces": "",
        "city": "",
        "p_aid": 0,               //上级客户经理
        "type": 9,     //身份  身份(9总公司,1客服经理,2客服)
        "opend_status": 2,
        "status": 2,
        "audit_eid": 18911554496,      //操作人id
        "info": "",
        "last_amount": 10000000,        //剩余钻数
        "close_down_info": "",
        "close_down_time": 0,
        "init_time": 1479144471,
        "month": 201611,
        "date_init_time": "2016-11-15"      //加入时间
      }
    ]


//(1) 下级人员列表<包括 客服和客服经理>  （ 姓名  电话  type职位（客服 or 经理） 日期  钻数 ）
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'agent_info_list'
		platform: 'tocar'
        aid:18911554499     //登录的客服(经理) aid(即:手机号)
        key:           //登录key
        page:1
        p_aid://   查询下级人员的 aid
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2您还未开通代理身份 6无此权限   8您的身份正在审核中
	sub_desc	//sub_code 描述
		"data": {
	    "data_count": 2,
	    "page_count": 10,
	    "agent_info":
	    [
	      {
	        "aid": 18911552222,
	        "wx_id": "",
	        "name": "唐客服经理A",
	        "provinces": "",
	        "city": "",
	        "p_aid": 18911554496,
	        "type": 1,
	        "opend_status": 0,
	        "status": 2,
	        "audit_eid": 18911554496,
	        "info": "",
	        "last_amount": 0,
	        "close_down_info": "",
	        "close_down_time": 0,
	        "init_time": 1482232757,
	        "month": 201612
	      },

	    ]
	  },


//(2) 添加客服经理 或客服（ 姓名 电话 手机验证码 ）
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'add_agent'
		platform: 'tocar'
		shop:'sichuan0001'   //权限验证
        aid:18911554499     //登录的客服(经理) aid(即:手机号)
        key:           //登录key
        children_aid:     //被开通客服(经理)手机号
        num:     //被开通客服(经理)手机验证码
        name:    //姓名
        type:1  // 9 总公司 8总代理 1客服经理  2客服
        city:
        provinces:
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2请先开通客服(经理)身份,在新增下级客服(经理) 3客服(经理)已经存在,请勿重复添加   4短信验证码错误
	sub_desc	//sub_code 描述
	data:


//(3) 审核申请成为客服经理--列表<待审核  审核通过  审核不通过> （ 姓名 电话 直接上级代理（电话））
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'chmod_agent_list'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:
        shop:'sichaun0001'
        page:      //当前页
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误 2 无此权限  3  无此记录
	sub_desc	//sub_code 描述
 	"data_count": 1,
    "page_count": 10,
	"list": [
      {
        "aid": 18911554422,
        "wx_id": "",
        "name": "唐客服B",
        "provinces": "",
        "city": "",
        "p_aid": 18911551111,
        "type": 1,
        "opend_status": 0,
        "status": 1,
        "audit_eid": 18911551111,
        "info": "",
        "last_amount": 0,
        "close_down_info": "",
        "close_down_time": 0,
        "init_time": 1482281316,
        "update_time": 1482285199,
        "month": 201612,
        "p_num": 18911552222
      }
    ]


//    审核  客服经理
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'chmod_agent_pass'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:
        chmod_aid:   //被审核客服id号
        shop:'sichuan0001'
        status:  //2通过  3不通过
        info: //  可空  不通过原因
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误 2 无此权限  3  无此记录  4 status参数错误
	sub_desc	//sub_code 描述


//(5) 给客服经理充值<充值记录>  搜索 （ 电话 ）  充值？（ 活的吗？ ）
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'recharge_agent_manager'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:
        recharge_aid:   //被充值id号
        last_amount:  //充值钻数
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误 2记录不存在 4 扣钻失败 5余额不足 6人员关系错误
	sub_desc	//sub_code 描述


//    给客服经理 或 客服 充值记录
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'recharge_agent_amount_list'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:
        page:      //当前页
        p_aid: //
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误   3  无此记录
	sub_desc	//sub_code 描述
	"data": {
    "data_count": 2,
    "page_count": 10,
    "list": [
      {
        "buy_aid": 81,
        "aid": 18911551111,
        "money": "0.00",
        "buy_amount": 20,
        "buy_status": 2,
        "activity_info": "",
        "handler": 18911552222,
        "buy_time": 1482312248,
        "month": 201612
      },
      {
        "buy_aid": 78,
        "aid": 18911551111,
        "money": "0.00",
        "buy_amount": 2000,
        "buy_status": 2,
        "activity_info": "",
        "handler": 18911552222,
        "buy_time": 1482311898,
        "month": 201612
      }
    ]
  },


//    搜索客服 或 客服经理
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'search_agent'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:
        search_aid:   //被充值id号
        type:2   //表示可以全部代理,,,,(可以不传,表示只能查询下级代理)
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误 2记录不存在 4 扣钻失败 5余额不足 6人员关系错误
	sub_desc	//sub_code 描述
	"data":
	{
	    "list": [
	      {
	        "big_agent_agent_info_multi__18911551111": {
	          "aid": 18911551111,
	          "wx_id": "",
	          "name": "唐客服经理",
	          "provinces": "",
	          "city": "",
	          "p_aid": 18911552222,
	          "type": 1,
	          "opend_status": 0,
	          "status": 2,
	          "audit_eid": 18911552222,
	          "info": "",
	          "last_amount": 2086,
	          "close_down_info": "",
	          "close_down_time": 0,
	          "init_time": 1482226218,
	          "update_time": 0,
	          "month": 201612,
	          "p_num": 18911552222
	        }
	      }
	    ]
  },


//(3) 替客服申请开通 客服经理身份
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'chmod_agent'
		platform: 'tocar'
        aid:18911554499     //登录的客服(经理) aid(即:手机号)
        key:           //登录key
        chmod_aid:     //被开通客服(经理)手机号
        num:     //被开通客服(经理)手机验证码
        type:1  //1客服经理
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 ,  1 登录错误, 3人员关系错误 4 记录不存在
	sub_desc	//sub_code 描述
	data:


//(2) 玩家搜索
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'play_find_by_id'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        p_aid:10200          //玩家id
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误, 2没有记录
	sub_desc	//sub_code 描述
	data:
		"play_user":
		[
	      {
		      "userId": 10200,  //玩家id
		      "nickName": "峥",  //玩家昵称
		      "specialGold": 100  //剩余钻数
	      }
	    ]


//(1) 给玩家充值
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'play_recharge'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        p_aid:10200          //玩家id
        recharge_amount:100   //客服(经理)给  玩家充的钻数量
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 ,  1登录错误  2记录不存在  3余额不足   4扣钻不成功
	sub_desc	//sub_code 描述
	data:


//(1) 给玩家<充值记录>
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'play_recharge_list'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        page:      //当前页
        p_aid:    //
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1登录错误  2记录不存在
	sub_desc	//sub_code 描述
		"data_count": 2,
	    "page_count": 5,
	    "list":
	    [
	      {
	        "play_rid": 1,
	        "play_id": 10200,  //玩家id
	        "play_name": "峥",  //玩家昵称
	        "last_amount": 100,   //玩家充值前数量
	        "recharge_amount": 0,  //玩家充值数量
	        "play_sum_amount": 100,  //玩家充值后数量
	        "aid": 18911554496,     //给玩家充值的客服(经理)id
	        "recharge_time": "2016-11-09"   //充值时间
	      },
	      {
	        "play_rid": 2,
	        "play_id": 10200,
	        "play_name": "峥",
	        "last_amount": 100,
	        "recharge_amount": 0,
	        "play_sum_amount": 100,
	        "aid": 18911554496,
	        "recharge_time": "2016-11-09"
	      },
	    ]


//获得支付模块的订单号等信息
request:
	randkey
	c_version
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'get_out_trade_no'
		platform: 'tocar'  //与城市无关,但是需要校验用户是否登录
		aid:
		key:
		amount:   //数量
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 1 登录错误
	sub_desc	//sub_code 描述
	data:
		out_trade_no	//订单号
		zhifubao_notify	//支付宝回调服务器地址
		ailipay_url: "https://mapi.alipay.com/gateway.do?_input_charset=utf-8&body=%E5%8A%9F%E5%A4%AB%E9%BA%BB%E5%B0%86%E6%B8%B8%E6%88%8F%E9%92%BB&notify_url=http%3A%2F%2F120.76.211.70%2Fpay%2Fextend%2Falipay%2Fnotify.php&out_trade_no=70&partner=2088521147710743&payment_type=1&seller_id=2088521147710743&service=create_direct_pay_by_user&subject=%E4%BB%A3%E7%90%86%E8%B4%AD%E9%92%BB%E6%94%AF%E4%BB%98%E5%AE%9D%E8%AE%A2%E5%8D%95&total_fee=300&sign=2e940d20e83bbf4bf552e484a0031f98&sign_type=MD5_input_charset=utf-8"


//支付成功回调 充钻  -----凯哥 你不需要这个协议
request:
	randkey
	c_version
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'remote_call_back'
		platform : 'tocar'
		out_trade_no : 1453171903596  //订单id
		total_fee: 1100               // 订单支付金额
		call_back_param : "{'uid':17701360024}" //回传参数
response:
	code : 0 //是否成功 0成功
	desc	//描述
	sub_code	//出错类型
	sub_desc	//sub_code 描述
	data:


//配置数据  get_conf
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		act: 'get_conf'
		platform: 'tocar'
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 , 2
	sub_desc	//sub_code 描述	   1 登录错误, 2没有记录
	data:


//总代理购钻记录
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'agent_buy_amount_list'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:                    //登录key
        page:      //当前页
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1登录错误  2记录不存在
	sub_desc	//sub_code 描述
	 "data": {
	    "data_count": 1,
	    "page_count": 10,
	    "list": [
	      {
	        "buy_aid": 187,
	        "aid": 18911554496,
	        "money": "0.01",
	        "buy_amount": 1,
	        "buy_status": 1,
	        "activity_info": "",
	        "handler": 18911554496,
	        "buy_time": "2016-12-23 09:50:39",
	        "month": 201612,
	        "name": "唐总bass"
	      }
	    ]
	  },

//下载
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		act: 'get_agent_info_excel'
		platform: 'tocar'
		shop:'sichuan0001'
		aid:  //登录aid
		key:
		p_aid:  //下载 的aid
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 ,1,2
	sub_desc	//sub_code 描述	 1 登录错误, 2无此权限   3.无此记录


// 购钻记录
request:
	randkey
	c_version:0.0.1
	parameter
		mod: 'Business'
		ran:'189115544960.4648064303463134'//随机数校验
		act: 'agent_buy_list'
		platform: 'tocar'
        aid:18911554499              //客服(经理)aid(即:手机号)
        key:
        page:      //当前页
        search_aid: //  被查看购钻记录aid
response:
	code //是否成功 0成功
	desc	//描述
	sub_code	//出错类型 0 成功 , 1 登录错误   3  无此记录
	sub_desc	//sub_code 描述
	"data": {
	    "data_count": 15,
	    "page_count": 10,
	    "list": [
	      {
	        "buy_aid": 243,
	        "aid": 18911554411,       //购钻人aid
	        "money": "0.00",
	        "buy_amount": 11,                  //购钻数量
	        "buy_status": 2,
	        "activity_info": "",
	        "handler": 18911551111,
	        "buy_time": "2017-01-06 17:35:53",  //购钻时间
	        "month": 201701
	      }
	      ]
	    }


//读取kpi信息
request:
	randkey
	c_version
	parameter
		mod: 'Business'
		act: 'kpi_get'
		platform: 'tocar'	//或者 gfplay_ios
		page: 1 // 页数 默认为 1
		shop:'sichuan0001'
response:
	code //是否成功 0成功
	desc	//描述
	data:
		all_count: 1,
		count_per_page: 20
		kpi_list:
		[
		    {
		        "id_time": "2017-01-18",
		        "all_user": 45,	//用户总数量
		        "new_user": 0,	//日新增用户数
		        "active_user": 8,	//每日活跃人数
		        "game_num": 42,	//每日玩牌把数
		        "hour_user": {	//每日各时间段每小时用户在线数据
		            "11": "0", "12": "0", "13": "0"
		        },
		        "currency": -14	//每日消费
		    }
		]
