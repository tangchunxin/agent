<?php
namespace bigcat\conf;
class Config
{
	const DEBUG = false;
	const LANGUAGE = 'cn';
	const PLATFORM = 'gfplay';

	const USER_PATH = 'http://127.0.0.1/user_php70/index.php';
	const USER_WX_PATH  = 'http://127.0.0.1/user_wx/index.php';
    const AGENT_URL = 'http://127.0.0.1/mahjong/game_agent/fair_agent/index.php';
	const POWER_PATH = 'http://127.0.0.1/power_control/index.php';

	const PAY_PATH = 'http://127.0.0.1/pay/index.php';
	const MY_PATH = 'http://127.0.0.1/mahjong/game_agent/fair_agent/index.php';

	const MC_SERVERS = array(array('127.0.0.1',11211));

	//正式版地址数据地址 
	// const DB_HOST = '10.66.187.150';
	// const DB_USERNAME = 'root';
	// const DB_PASSWD = 'gfplay@541013';
	// const DB_DBNAME = 'big_agent_chengde';
 	//const DB_PORT = '3306';

	//测试版数据地址
	const DB_HOST = '';
	const DB_USERNAME = '';
	const DB_PASSWD = '';
	const DB_DBNAME = '';
	const DB_PORT = '';
	
	
	const GET_USER = 'http://dezhou.gfplay.cn:83/mahjong/game_s_http/index.php';   //自己游戏充值
	
	const HOSE = 'work.gfplay.cn';
	const UNIT_PRICE = 15; //钻/单价0.15元
	
	//项目memcached 区分用的key前缀
	const KYE_NAME = 'chengde_';
	const API_KEY = 'NCBDpay';
	const RPC_KEY = '';
	
	const INIT_AMOUNT = 150;   //默认150个钻的选项 UNIT_PRICE_ARR
	const WXPAY = 'wxpay';   //微信支付模块名称
	const WXPAY_LINFIY = array(0=>'wxpay_linfiy');   //h5微信支付模块名称
	const COUNT_DAY = 30; //财务报表天数
	const NAME = '陕西'; //财务报表名字
	const TYPE2_OUT_AGENT = 0; //副会长是否可以把玩家提出工会  0可以踢出工会   1 不可以
	
	const IP_EXCEPTION = array(
		'123.112.224.152'
	);	
	const  UNIT_PRICE_ARR= array(
		'150'=>150,
	    '300'=>375,
	    '600'=>970,
	    '0.01'=>1,
	);
	
	//微信app支付
	const  WX_PAY= array(
		12=>0.01,
		28=>25,
		60=>50,
		140=>108,
	);
	
	//微信app支付货币2
	const  WX_PAY_CURRENCY2= array(
		1=>1,
		5=>5,
		25=>25,
		50=>50,
		100=>100,
		300=>300,
		500=>500,
		1000=>1000,
	);
	
	const TYPE_1 = 10; //会长个数
	const TYPE_2 = 20; //副会长个数
	
	//代理分成百分比  直属用户
	const SHARED = [
		9 => 0.40
		,8 => 0.60
		,1 => 0.45
		,2 => 0.30
	];
	
	//代理分成百分比  下属用户
	const SHARED_FORM_SUBORDINATE = [
		9 => 0.40
		,8 => 0.15
		,1 => 0.15
		,2 => 0
	];
	
	const ONLY_YESTERDAY = 0;  //0可以看今天的收益   1不可以看今天的收益,只能看昨天的收益
	const SELF_INCOME = 0;  //0总收益包括下属的收益   1只显示自己收益
	const SUM_INCOME_TIME = 1;  //每日提现次数
	const TRADE_NO_KEY = 'baoding';  //订单号前缀
	
	const SSLCERT_PATH = './inc/cert/apiclient_cert.pem';
	const SSLKEY_PATH = './inc/cert/apiclient_key.pem';
	const START_INCOME_TIME = 1509811200;
	const TIME_LIMIT = [1=>10,2=>17];  //开始时间10点至17点
	const WEEKARRAY= array(0,1,2,3,4,5,6);  //0星期日  1-6 星期一至星期六
	const FROM_DB = 'test_game_mahjong_cangzhou';  //加密字符串,和http数据库保持一致
	const ADD_AGENT1 = 0; //8是否可以添加1
	const ADD_AGENT1_arr = array(8615075758077,8617331785734,8618003376688); //8可以添加1
	
	const IS_SOCIETY = 0;  //是否开通公会房  给凯哥显示使用 后台没意义

	const GIFTGROUP = [
		'1' => '30元移动充值卡'
		,'2' => '20元联通充值卡'
		,'3' => '20元电信充值卡'
		,'4' => '50元移动充值卡'
		,'5' => '50元联通充值卡'
		,'6' => '50元电信充值卡'
		,'7' => '100元移动充值卡'
		,'8' => '100元联通充值卡'
		,'9' => '100元电信充值卡'

	];
	
	
}