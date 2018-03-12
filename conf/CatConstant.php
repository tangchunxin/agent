<?php
/**
 * @author xuqiang76@163.com
 * @final 20160929
 */

namespace bigcat\conf;

class CatConstant
{

	const C_VERSION = '0.0.1';
	const CONF_VERSION = '0.0.1';
	const SECRET = 'Keep it simple stupid!';
	const CDKEY  = 'God bless you!';
	const LOG_FILE = './log/business.log';
	const CACHE_TYPE = '\bigcat\inc\CatMemcache';
	const C_VERSION_CHECK = true;
	const CNT_PER_PAGE = 20; //每页数量
	const POWER_MOD = 10;
	const RETURN_BASE = 0.3;  //大客户返利30%
	const STR_OBJ = 1; //缓存字符串rad 验证
	//const UNIT_PRICE = 0.15; //    钻/单价0.15元
	//const BOSS = array('8613671301110','8617600222046','8613671200980');
	const BOSS = array(8618911554496,8613911172259,8618310721990,8613623320600,8613716767992,8615010982862);//唐纯鑫 李雪斌


	const OK = 0;
	const ERROR = 1;
	const ERROR_MC = 2;
	const ERROR_INIT = 3;
	const ERROR_UPDATE = 4;
	const ERROR_VERIFY = 5;
	const ERROR_ARGUMENT = 6;
	const ERROR_VERSION = 7;

	const TYPE_1_ZFB_CZ = 1; //支付宝
	const TYPE_2_GFSD_CZ = 2; //官方手动充值钻
	const TYPE_3_DL_FL = 3; //代理返利
	const TYPE_4_HD_JL = 4; //活动奖励
	const TYPE_5_CZ_JL = 5; //充值奖励
	const TYPE_6_WX_CZ = 6; //微信充值
	const TYPE_9_DEL_ZUAN = 9; //代理扣钻

	const MODELS = array('Business' => '\bigcat\controller\Business');
	const UNCHECK_C_CERSION_ACT = array('Business' => [
													   'get_conf'
													   ,'date_index'
													   ,'kpi_crontab_new'
													   ,'agent_info_game'
													   ,'wx_get_out_trade_no'
													   ,'decrypt_add_agent'
													   ,'extract_income'
													   ,'extract_income_list'
													   ,'add_service'
													   ,'add_rechargeable_card'
													   ,'show_rechargeable_card'
													   ,'show_gift_exchange_log'
													   ,'change_delivery_information'
													   ,'delete_rechargeable_card'
													   ,'wx_get_jsapi'
		                                               ]);
	const UNCHECK_VERIFIED_ACT = array('Business' => [
													 'agent_login_info'
													 ,'agent_info_list'
													 ,'add_agent'
													 ,'play_find_by_id'

													 ,'play_recharge'
													 ,'search_agent'
													 ,'get_conf'

													 ,'delete_agent_info'
													 ,'get_agent_info_excel'
													 ,'kpi_get'
													 ,'insert_model'
													 ,'updata'

													 ,'del_agent_amount'
													 ,'wx_get_out_trade_no'
													 ,'get_order_query'
													 ,'get_agent_buy_excel'

													 ,'get_jsapi_ticket'
													 ,'boss_add_agent'
													 ,'all_agent'
													 ,'kpi_get_all'

													 ,'chmod_agent_id'
													 ,'chmod_p_aid'
													 ,'bind_out_agent_id'
													 ,'play_recharge_list_new'
													 ,'play_list'

                                                     ,'pull_blacklist'
                                                     ,'blacklist'
                                                     ,'kpi_crontab_new'
                                                     ,'income_statistics'
													 ,'change_agent'
													 
                                                     ,'chmod_agent_info'
                                                     ,'find_play_recharge'
                                                     ,'find_play_video'
                                                     ,'decrypt_add_agent'
                                                     ,'extract_income'
                                                     ,'extract_income_list'
                                                     ,'add_service'
                                                     ,'add_rechargeable_card'
													 ,'show_rechargeable_card'
													 ,'show_gift_exchange_log'
													 ,'change_delivery_information'
													 ,'delete_rechargeable_card'
													 ,'kpi_get_new'
													 ,'income_statistics_excel'
													 ,'extract_income_list_excel'
													 ,'set_shared'
													 ,'guild_pay'
													 ,'wx_get_jsapi'
													 ,'change_uid_bind_agent'

                                                     
													 ]);
  //kpi
  const GET_USER = array(

						'hb' => 'http://hbcdn.gfplay.cn/mahjong/game_s_http/index.php'

						,'jiamusi' => 'http://xinji.gfplay.cn:83/mahjong/game_s_http_jiamusi/index.php'

						,'chengde' => 'http://chengdecdn.gfplay.cn/mahjong/game_s_http_chengde/index.php' 

						,'baoding' => 'http://baodingcdn.gfplay.cn/mahjong/game_s_http_new/index.php'

						,'chifeng'=>'http://chifengcdn.gfplay.cn/mahjong/game_s_http_chifeng/index.php'

						,'xinji' => 'http://xinji.gfplay.cn:83/mahjong/game_s_http/index.php'

						,'dezhou' => 'http://dezhou.gfplay.cn:83/mahjong/game_s_http_dezhou/index.php'

						,'shaanxin' => 'http://sx.gfplay.cn:81/mahjong/game_s_http/index.php'




						//,'sichuan' => 'http://sc.gfplay.cn:80/mahjong/game_s_http/index.php'
						//,'beimei' => 'http://na.gfplay.cn:80/mahjong/game_s_http/index.php'
	);

	const SUB_DESC = array(
						'Business_agent_login_info' => array('sub_code_1'=>'登录错误','sub_code_2'=>'您还未开通代理身份','sub_code_8'=>'您的身份正在审核中','sub_code_9'=>'您的账号已被查封,解封时间未到')
						,'Business_agent_info_list' => array('sub_code_1'=>'登录错误','sub_code_6'=>'无此权限','sub_code_2'=>'您还未开通代理身份','sub_code_8'=>'您的身份正在审核中','sub_code_9'=>'您的账号已被查封,解封时间未到')
						,'Business_add_agent' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'该账号已存在','sub_code_4'=>'手机验证码错误','sub_code_5'=>'添加失败或已经超过人员数量限制','sub_code_6'=>'您的账号不存在','sub_code_7'=>'该功能已关闭,无法开通下级')
						,'Business_play_find_by_id' => array('sub_code_1'=>'登录错误','sub_code_2'=>'记录不存在','sub_code_9'=>'您的账号已被查封')
						,'Business_play_recharge' => array('sub_code_1'=>'登录错误','sub_code_2'=>'记录不存在','sub_code_3'=>'余额不足','sub_code_4'=>'扣钻不成功','sub_code_5'=>'非法充值','sub_code_9'=>'您的账号已被查封','sub_code_8'=>'无此权限')
						
						,'Business_search_agent' => array('sub_code_1'=>'登录错误','sub_code_4'=>'记录不存在或非本公会成员','sub_code_3'=>'记录不存在或非本公会成员')
						,'Business_delete_agent_info' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'记录不存在','sub_code_4'=>'该代理还有绑定的玩家,暂时不能删除')
						,'Business_kpi_get' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')		
						,'Business_del_agent_amount' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'充值数量错误','sub_code_4'=>'该用户不存在','sub_code_5'=>'该用户余额不足,无法扣除')
						,'Business_wx_get_out_trade_no' => array('sub_code_1'=>'登录错误','sub_code_2'=>'充值数量错误')
					
						,'Business_get_agent_buy_excel' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_get_jsapi_ticket' => array('sub_code_1'=>'错误','sub_code_2'=>'')
						,'Business_boss_add_agent' => array('sub_code_1'=>'错误','sub_code_2'=>'无此权限','sub_code_3'=>'登录人员身份错误','sub_code_4'=>'添加失败','sub_code_5'=>'分成比例错误','sub_code_6'=>'无法读取登录人员信息')					
						,'Business_kpi_get_all' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_chmod_agent_id' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'暂无数据','sub_code_4'=>'新电话号码已经存在,无法修改')
						
						,'Business_chmod_p_aid' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'暂无代理数据!','sub_code_4'=>'该账号不是副会长或还有未结算的收益','sub_code_5'=>'代理上下级关系错误','sub_code_6'=>'代理身份不一致,无法合并','sub_code_7'=>'此代理暂无下级,无需合并','sub_code_8'=>'更换的新上级代理不存在')				
						,'Business_bind_out_agent_id' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'解绑失败!')					
						,'Business_play_recharge_list_new' => array('sub_code_1'=>'登录错误','sub_code_2'=>'权限错误','sub_code_3'=>'查询失败','sub_code_4'=>'非本公会成员')
						,'Business_play_list' => array('sub_code_1'=>'登录错误','sub_code_2'=>'非本公会成员','sub_code_3'=>'查询失败','sub_code_4'=>'该玩家尚未绑定公会','sub_code_5'=>'非本公会玩家')
                        ,'Business_pull_blacklist' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'查询失败','sub_code_4'=>'操作失败')
	 
                        ,'Business_blacklist' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'查询失败')						
						,'Business_income_statistics' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'记录为空或查询失败','sub_code_4'=>'开始时间大于结束时间','sub_code_5'=>'搜索区间不能超过2个月','sub_code_6'=>'登陆账号与查询账号相同,无需查询','sub_code_7'=>'查询日期不能超过当日日期')
						,'Business_change_agent' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'操作失败','sub_code_4'=>'找不到该用户','sub_code_5'=>'短信验证码错误','sub_code_6'=>'新号码已经存在,无法更换','sub_code_7'=>'还有未提取的收益,暂时无法更换','sub_code_8'=>'修改的不是您的下级')                     
                        ,'Business_chmod_agent_info' => array('sub_code_1'=>'登录错误','sub_code_2'=>'非下级成员','sub_code_3'=>'下级成员不存在','sub_code_4'=>'操作失败')
                        ,'Business_find_play_recharge' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'查询失败')
						
						,'Business_find_play_video' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'查询失败')
						,'Business_decrypt_add_agent' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'该账号已存在','sub_code_4'=>'手机验证码错误','sub_code_5'=>'添加失败或已经超过人员数量限制','sub_code_6'=>'链接错误')
						,'Business_extract_income' => array('sub_code_1'=>'登录错误','sub_code_2'=>'当日收益无法提取','sub_code_3'=>'开始时间不能大于结束时间','sub_code_4'=>'时间不能超过2个月','sub_code_5'=>'收益查询失败','sub_code_6'=>'该收益已经提取,请勿重复提取','sub_code_7'=>'OpenId获取失败','sub_code_8'=>'订单生成失败','sub_code_9'=>'订单生成失败','sub_code_10'=>'提现失败或系统繁忙!请稍后再试!!','sub_code_11'=>'提取成功后,时期列表获取失败','sub_code_12'=>'提取成功后,更新订单失败','sub_code_13'=>'kpi_new预更新失败','sub_code_14'=>'提现金额不足1元','sub_code_15'=>'登录账号和查询账号一致,无需重复查询','sub_code_16'=>'上下级关系错误','sub_code_17'=>'该账号不存在','sub_code_18'=>'公司身份无法提现','sub_code_19'=>'提现时间间隔不得低于15秒','sub_code_20'=>'今天提取次数已超过上限','sub_code_21'=>'本次提取日期未能与上次提取日期连续','sub_code_22'=>'提现失败,更新订单失败','sub_code_23'=>'无法获取conf数据','sub_code_24'=>'提现时间未在指定范围内','sub_code_25'=>'请在10:00至17:00之间提现','sub_code_26'=>'今日不可提现,未在指定星期范围内!','sub_code_27'=>'无法给非实名用户付款!请实名认证后再尝试!')
						,'Business_add_service' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'该账号不存在','sub_code_4'=>'身份错误','sub_code_5'=>'添加失败或该账号已存在')
						,'Business_extract_income_list' => array('sub_code_1'=>'登录错误','sub_code_2'=>'开始时间不能大于结束时间','sub_code_3'=>'登录账号与查询账号相同,无需重复查询','sub_code_4'=>'上下级关系错误','sub_code_5'=>'查询账号不错在')
						
						,'Business_income_statistics_excel' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_income_extract_income_list_excel' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_income_delete_rechargeable_card' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_income_show_gift_exchange_log' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_income_change_delivery_information' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						
						,'Business_income_show_rechargeable_card' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_income_add_rechargeable_card' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限')
						,'Business_set_shared' => array('sub_code_1'=>'登录错误','sub_code_2'=>'无此权限','sub_code_3'=>'分成比例错误','sub_code_4'=>'修改的账号身份错误','sub_code_5'=>'查无此人','sub_code_6'=>'修改失败')
						,'Business_guild_pay' => array('sub_code_1'=>'登录错误','sub_code_2'=>'灵飞码校验错误','sub_code_3'=>'用户不存在','sub_code_4'=>'开通失败','sub_code_5'=>'请勿重复开通','sub_code_6'=>'暂未开通,请勿重复操作','sub_code_7'=>'灵飞码已经被开通,请联系客人人员')
	
					);

	//代理分成百分比
    // const COMPANY = 9;
    // const FIRST_LEVEL_PERCENT = 8;
    // const SECOND_LEVEL_PERCENT = 1;
    // const THIRD_LEVEL_PERCENT =2;

    // public static $shared = [
    //     self::COMPANY => 0.40,
    //     self::FIRST_LEVEL_PERCENT => 0.60,
    //     self::SECOND_LEVEL_PERCENT => 0.45,
    //     self::THIRD_LEVEL_PERCENT => 0.30
    // ];

    // public static $shared_from_subordinate = [
    //     self::COMPANY => 0.40,
    //     self::FIRST_LEVEL_PERCENT => 0.15,
    //     self::SECOND_LEVEL_PERCENT => 0.15,
    //     self::THIRD_LEVEL_PERCENT => 0
    // ];




}
