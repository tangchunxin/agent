<?php
/**
 * @author tangchunxin
 * @final  start:20161104
 */

namespace bigcat\controller;

use bigcat\conf\Config;

use bigcat\inc\BaseFunction;
use bigcat\inc\WxPay;
use bigcat\inc\CatMemcache;
use bigcat\conf\CatConstant;

use bigcat\model\AgentBuy;
use bigcat\model\AgentBuyFactory;
use bigcat\model\AgentBuyListFactory;
use bigcat\model\AgentBuyMultiFactory;

use bigcat\model\AgentInfo;
use bigcat\model\AgentInfoFactory;
use bigcat\model\AgentInfoListFactory;
use bigcat\model\AgentInfoMultiFactory;

use bigcat\model\PlayRecharge;
use bigcat\model\PlayRechargeFactory;
use bigcat\model\PlayRechargeListFactory;
use bigcat\model\PlayRechargeMultiFactory;

use bigcat\model\User;
use bigcat\model\UserFactory;
use bigcat\model\UserListFactory;
use bigcat\model\UserMultiFactory;

use bigcat\model\Income;
use bigcat\model\IncomeFactory;
use bigcat\model\IncomeListFactory;
use bigcat\model\IncomeMultiFactory;

use bigcat\model\PowerControl;
use bigcat\model\PowerControlFacroty;

use bigcat\model\KpiFactory;
use bigcat\model\KpiNewFactory;
use gf\inc\ConstConfig;


class Business
{
	private $log = './log/business.log';
	private $kpi_key = 'kpi_key';
	public static $income_key = 'income_lock_key_';
	public static $aidlock = 'aidlock_key';
	public static $agent_info = 'agent_info_key';
	///////////////////静态方法////////////////////////////

	public static function cmp_agent_init_time($a, $b)
	{
		$return = 0;
		$key_name = 'init_time';
		$order_sort = 'desc';

		if ($a->$key_name == $b->$key_name)
		{
			$return = 0;
		}
		else if ($a->$key_name < $b->$key_name)
		{
			$return = -1;
		}
		else if ($a->$key_name > $b->$key_name)
		{
			$return = 1;
		}

		if($order_sort == 'desc')
		{
			$return = -$return;
		}
		return $return;
	}

	public static function cmp_agent_update_time($a, $b)
	{
		$return = 0;
		$key_name = 'update_time';
		$order_sort = 'desc';

		if ($a->$key_name == $b->$key_name)
		{
			$return = 0;
		}
		else if ($a->$key_name < $b->$key_name)
		{
			$return = -1;
		}
		else if ($a->$key_name > $b->$key_name)
		{
			$return = 1;
		}

		if($order_sort == 'desc')
		{
			$return = -$return;
		}
		return $return;
	}

	public static function cmp_agent_buy_time($a, $b)
	{
		$return = 0;
		$key_name = 'buy_time';
		$order_sort = 'desc';

		if ($a->$key_name == $b->$key_name)
		{
			$return = 0;
		}
		else if ($a->$key_name < $b->$key_name)
		{
			$return = -1;
		}
		else if ($a->$key_name > $b->$key_name)
		{
			$return = 1;
		}

		if($order_sort == 'desc')
		{
			$return = -$return;
		}
		return $return;
	}

	///////////////////////////////////私有方法///////////////
	private function _add_amount($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['recharge_aid'])
			|| 	empty($params['aid'])
			|| 	empty($params['buy_status'])
			|| 	empty($params['last_amount'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

			$obj_agentbuy = new AgentBuy();
			if(isset($obj_agentbuy))
			{
				$obj_agentbuy->aid = $params['recharge_aid'];
				if(!empty($params['money']))
				{
					$obj_agentbuy->money = $params['money'];
				}
				else
				{
					$obj_agentbuy->money = 0;
				}
				$obj_agentbuy->buy_status = $params['buy_status'];
				$obj_agentbuy->buy_amount = $params['last_amount'];
				if(!empty($params['activity_info']))//活动奖励原因
				{
					$obj_agentbuy->activity_info = $params['activity_info'];
				}

				$obj_agentbuy->handler = $params['aid'];
				$obj_agentbuy->buy_time = $itime;
				$obj_agentbuy->month = (int)date("Ym",$itime);

				$rawsqls[] =$obj_agentbuy->getInsertSql();
			}

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['code'] = 1; $response['desc'] = __line__; break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	private function _add_agent_info_last_amount($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (
		 	    empty($params['aid'])
			|| 	empty($params['last_amount'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				if(isset($obj_agentinfo_multi) && is_array($obj_agentinfo_multi))
				{
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
					$obj_agentinfo_multi_item->last_amount = $obj_agentinfo_multi_item->last_amount + $params['last_amount'];
				}
			}
			$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['code'] = 1; $response['desc'] = __line__; break;
			}

			if(isset($obj_agentinfo_multi_factory))
			{
				$obj_agentinfo_multi_factory->writeback();
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}


	private function _login_check($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['aid'])
			|| empty($params['key'])
			) {
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();
			$data['login_check_result'] = '';

			$data_request = array(
			'mod' => 'Business'
			, 'act' => 'login_check'
			, 'platform' => 'gfplay'
			, 'uid' => $params['aid']
			, 'key' => $params['key']
			);
			$randkey = BaseFunction::encryptMD5($data_request);
			if(!empty($params['fromwx']))
			{
				$url = Config::USER_WX_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
			}
			else
			{				
				$url = Config::USER_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
			}
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
			if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;			
			}

			$response['data'] = $data;

		} while (false);

		return $response;
	}

	private function _get_encrypt_uid($uid ,$aid)
	{		
		$data_request = array(
			'uid' => $uid
			,'aid' => $aid
			,'from_db' => Config::FROM_DB  //区分每个游戏
		);

		$randkey = BaseFunction::encryptMD5($data_request);
		return $randkey."$".$uid;
	}

	public static function _get_conf_power_control()
	{
		$mcobj = BaseFunction::getMC();
		$obj_power_control_factory = new PowerControlFacroty($mcobj);
		if($obj_power_control_factory->initialize() && $obj_power_control_factory->get())
		{
			$obj_power_control = $obj_power_control_factory->get();
			if(isset($obj_power_control->power_modular))
			{
				return $obj_power_control->power_modular;
			}
		}
		else
		{
			$obj_power_control_factory->clear();
		}
		return array();
	}

	private function _power_check($params, &$name)
	{
		//权限验证成功 并返回用户的名字
		$return = true;
		do {
			if (empty($params['uid'])
			|| empty($params['modular'])
			|| empty($params['type'])
			|| empty($params['page'])
			|| empty($params['city'])
			|| empty($params['power_fun'])
			) {
				$return = false; break;
			}

			$data_request = array(
			'mod' => 'Business'
			, 'act' => 'get_power_list'
			, 'platform' => 'tocar'
			, 'uid' => $params['uid']
			, 'modular' => $params['modular']
			, 'type' => $params['type']
			, 'page' => $params['page']
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::POWER_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))), true);
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0) || empty($result['data']['power_arr'][0])) {
				BaseFunction::logger($this->log, "【result】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$return = false; break;
			}
			else
			{
				$city_boll = false;
				$fun_boll = false;

				$tmp_power_modular = Business::_get_conf_power_control();
				//BaseFunction::logger('./log/business.log', "【_power_check444】:\n" . var_export($tmp_power_modular, true) . "\n" . __LINE__ . "\n");
				if($tmp_power_modular && is_array($tmp_power_modular))
				{
					if(isset($result['data']['power_arr'][0]['name']))
					{
						$name = $result['data']['power_arr'][0]['name'];
					}

					//power1校验
					if(isset($tmp_power_modular['power_modular'][CatConstant::POWER_MOD]['power']['power1']) && isset($result['data']['power_arr'][0]['power1_str']))
					{
						$power1_arr = explode(',', $result['data']['power_arr'][0]['power1_str']);
						foreach ($power1_arr as $val)
						{
							if(isset($tmp_power_modular['power_modular'][CatConstant::POWER_MOD]['power']['power1'][$val])
							&& $tmp_power_modular['power_modular'][CatConstant::POWER_MOD]['power']['power1'][$val][1] == $params['city'])
							{
								$city_boll = true;
							}
						}
						//BaseFunction::logger('./log/business.log', "【city_boll】:\n" . var_export($city_boll, true) . "\n" . __LINE__ . "\n");
						unset($power1_arr);
						unset($val);
					}
					else
					{
						BaseFunction::logger('./log/business.log', "【_power_check444】:\n" . var_export("bbb", true) . "\n" . __LINE__ . "\n");
					}

					//power2校验
					if(isset($tmp_power_modular['power_modular'][CatConstant::POWER_MOD]['power']['power2']) && isset($result['data']['power_arr'][0]['power2_str']))
					{
						$power2_arr = explode(',', $result['data']['power_arr'][0]['power2_str']);
						foreach ($power2_arr as $val)
						{
							if(isset($tmp_power_modular['power_modular'][CatConstant::POWER_MOD]['power']['power2'][$val])
							&& $tmp_power_modular['power_modular'][CatConstant::POWER_MOD]['power']['power2'][$val][0] == $params['power_fun'])
							{
								$fun_boll = true;
							}
							//BaseFunction::logger('./log/business.log', "【fun_boll】:\n" . var_export($fun_boll, true) . "\n" . __LINE__ . "\n");
						}
						unset($power2_arr);
						unset($val);
					}
					else
					{
						BaseFunction::logger('./log/business.log', "【_power_check444】:\n" . var_export("aaa", true) . "\n" . __LINE__ . "\n");
					}
				}
				$return = $city_boll && $fun_boll;

				//BaseFunction::logger('./log/business.log', "【_power_check5555】:\n" . var_export($return, true) . "\n" . __LINE__ . "\n");
			}
		} while (false);

		return $return;
	}


	////////////公共方法////////////////////////////////////////
	//前端 登录之后获取人员信息  city
	public function agent_login_info($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = '';
		$self_type = 255;

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($login_result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 1;$response['desc'] = __line__;break;
			}

			$mcobj = BaseFunction::getMC();

 			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				if(is_array($obj_agentinfo_multi))
				{
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
					// if($obj_agentinfo_multi_item->status == 1)  //判断代理是否已经审核通过
					// {
					// 	$response['sub_code'] = 8;$response['desc'] = __line__;break;
					// }
					if($obj_agentinfo_multi_item->status == 4)//判断大客户是否已封号
					{
						// if($obj_agentinfo_multi_item->close_down_time<$itime)
						// {
						// 	$obj_agentinfo_multi_item->status = 2;
						// 	$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
						// 	$obj_agentinfo_multi_item->date_init_time = date("Y-m-d",$obj_agentinfo_multi_item->init_time);
						// 	$tmp = $obj_agentinfo_multi_item;
						// }
						// else
						{
							$response['sub_code'] = 9;$response['desc'] = __line__;break;
						}
					}
					$self_type = $obj_agentinfo_multi_item->type;
					// elseif($obj_agentinfo_multi_item->status == 2)
					// {
					// 	$obj_agentinfo_multi_item->date_init_time = date("Y-m-d",$obj_agentinfo_multi_item->init_time);
					// 	$tmp[] = $obj_agentinfo_multi_item;
					// }
					// else
					// {
					// 	$response['sub_code'] = 2; $response['desc'] = __line__;break;
					// }
					$init_time = $obj_agentinfo_multi_item->init_time;
					$obj_agentinfo_multi_item->date_init_time = date("Y-m-d",$obj_agentinfo_multi_item->init_time);
					$tmp = $obj_agentinfo_multi_item;
				}
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 2; $response['desc'] = __line__;break;
			}

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}
			if(isset($obj_agentinfo_multi_factory))
			{
				$obj_agentinfo_multi_factory->writeback();
			}

			//加密处理aid
			if($obj_agentinfo_multi_item->type != 2)
			{
				$encrypt_aid = (string)$params['aid'];
				$encrypt = BaseFunction::encryptRandAuth($itime,$encrypt_aid);
	
				$data['encrypt_aid'] = $encrypt;
				$data['time'] = $itime;
			}
			else
			{
				$data['encrypt_aid'] = '';
				$data['time'] = '';
			}

			if($self_type != 9)
			{
				$extract_date = $this->_is_extract_date($params['aid']);
				if(!empty($extract_date))
				{
					if(defined("bigcat\\conf\\Config::START_INCOME_TIME") && Config::START_INCOME_TIME > $init_time)
					{
						$data['init_time'] = date("Y-m-d",Config::START_INCOME_TIME);
					}
					else
					{
						$data['init_time'] = $obj_agentinfo_multi_item->date_init_time;;//已提现范围
					}

					$data['extract_date'] = date("Y-m-d",$extract_date);//已提现范围				
					if($data['extract_date'] == date('Y-m-d', $itime - 86400) )//如果昨天收益已经提现了
					{
						$data['extract_start_date'] = ''; //可提现范围
						$data['extract_end_date'] =  ''; //可提现范围
					}
					else
					{
						$data['extract_start_date'] = date("Y-m-d",$extract_date+86400); //可提现范围
						$data['extract_end_date'] =  date('Y-m-d', $itime - 86400); //可提现范围
					}
				}
				else
				{
					$data['init_time'] = '';
					$data['extract_date'] = '';
					if(defined("bigcat\\conf\\Config::START_INCOME_TIME") && Config::START_INCOME_TIME > $init_time)
					{
						$extract_start_date = Config::START_INCOME_TIME;
					}
					else
					{
						$extract_start_date = $init_time;
					}

					//if($extract_start_date > ($itime - 86400)) //今天注册的用户,,,可提现收益不能超过昨天					
					if(  date("Y-m-d",$extract_start_date) == date("Y-m-d",time())  )  //今天注册的用户,,,今日不能体现
					{
						$data['extract_start_date'] = '';
						$data['extract_end_date'] =  '';
					}
					else
					{
						$data['extract_start_date'] =  date("Y-m-d",$extract_start_date);
						$data['extract_end_date'] =  date('Y-m-d', $itime - 86400);
					}
					
				}
			}
			else
			{
				$data['init_time'] = '';
				$data['extract_date'] = '';
				$data['extract_start_date'] = '';
				$data['extract_end_date'] =  '';
			}

			$data['agent_info'] = $tmp;
			$response['data'] = $data;
			
		} while (false);

		return $response;
	}

	//微信登录 检查人员是否存在  no
	public function agent_info_test($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = '';

		do {
			if (empty($params['aid'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

 			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				if(!empty($obj_agentinfo_multi))
				{
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

					if ($obj_agentinfo_multi_item->type == 9 && empty($params['is_form_user_wx']))   //游戏客户端  9不能成为工会
                    {
                        $response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
                    }
					$data = $obj_agentinfo_multi_item;
				}
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			// if($obj_agentinfo_multi_factory)
			// {
			// 	$obj_agentinfo_multi_factory->writeback();
			// }

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//添加客服经理  city
	public function add_agent($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = array();
		$p_num = 0;
		$self_type = 0;
		$children_num = 0;

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['children_aid'])
			|| 	empty($params['name'])
			|| 	empty($params['wx_id'])
			|| 	!isset($params['num'])
			|| 	empty($params['provinces'])
			|| 	empty($params['city'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;break;
			}
			$mcobj = BaseFunction::getMC();

			//查询推荐码
			$obj_agentinfo_multi_factory =  new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item  = current($obj_agentinfo_multi);
				$p_num = $obj_agentinfo_multi_item->p_num;
				$type = $obj_agentinfo_multi_item->type;

				//8不能添加1
				if($type == 8 && defined("bigcat\\conf\\Config::ADD_AGENT1") && Config::ADD_AGENT1 == 1)
				{
					if(defined("bigcat\\conf\\Config::ADD_AGENT1_arr") && !in_array($params['aid'],Config::ADD_AGENT1_arr))
					{
						$response['sub_code'] = 7;$response['desc'] = __line__;break;
					}
					 
				}
				$p_aid = $params['aid'];
				$shared_info = $obj_agentinfo_multi_item->shared_info;

				//获取分成比例
				$sub_shared_arr = array();
				$third_shared_arr = array();
				$shared_arr = $this->_get_shared($p_num, $type, $sub_shared_arr ,$shared_info ,$third_shared_arr);

			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 6;$response['desc'] = __line__;break;
			}

			$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',$params['aid']);
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_list = $obj_agentinfo_list_factory->get();
				$children_num = count($obj_agentinfo_list);
			}
			else
			{
				$obj_agentinfo_list_factory->clear();
			}

			if($type != 9 )  //公司身份添加人员  不需要短信验证
			{
                $data_request = array(
                'mod' => 'Business'
                , 'act' => 'login_num_check'
                , 'type' => '2'    //验证类型此type非彼type  密码验证用1   添加人员用2
                , 'platform' => 'tocar'
                , 'aid' => $params['children_aid']
                , 'num' => $params['num']
                , 'key' => Config::API_KEY
                );

                $randkey = BaseFunction::encryptMD5($data_request);
                $url = Config::USER_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
                $result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
                if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
                {
                    BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
                    BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
                    $response['sub_code'] = 4; $response['desc'] = __line__; break;
                }
			}

			//总代不能新增  客服
			if($type == 9)
			{
				$new_type = 8;
			}
			else if($type == 8)//不能超过10个 会长
			{
				$new_type = 1;
			}
			else if($type == 1 && $children_num <= Config::TYPE_2)//不能找过9个副会长
			{
				$new_type = 2;
			}
			else
			{
				$response['sub_code'] = 5;$response['desc'] = __line__;break;
			}
			
			//新增人员
			$obj_agentinfo = new AgentInfo();

            if(isset($obj_agentinfo))
            {
				$obj_agentinfo->aid = $params['children_aid'];
				$obj_agentinfo->name = $params['name'];
				$obj_agentinfo->wx_id = trim($params['wx_id']);
				$obj_agentinfo->p_aid = $p_aid;
				$obj_agentinfo->type = $new_type;
				$obj_agentinfo->status = 2;//通过状态

				$obj_agentinfo->provinces = $params['provinces'];//通过状态
				$obj_agentinfo->city = $params['city'];//通过状态
				$obj_agentinfo->audit_eid = $params['aid'];
				$obj_agentinfo->last_amount = 0;
				if(8 == $new_type)
				{
					if($type == 9 )
					{
						$obj_agentinfo->p_num = $params['children_aid'];
					}
					else
					{
						$response['sub_code'] = 5; $response['desc'] = __line__;return $response;
					}
				}
				else
				{
					$obj_agentinfo->p_num = $p_num;
				}
				$obj_agentinfo->init_time = $itime;
				$obj_agentinfo->update_time = $itime;
				$obj_agentinfo->month = date("Ym",$itime);
				$obj_agentinfo->shared = $shared_arr[$new_type];
				$obj_agentinfo->sub_shared = $sub_shared_arr[$new_type];
				$obj_agentinfo->third_shared = $third_shared_arr[$new_type];
				$obj_agentinfo->shared_info = $shared_info;

            	$rawsqls[] = $obj_agentinfo->getInsertSql();
            }

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}
	//添加客服经理  city
	public function add_service($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['children_aid'])
			|| 	empty($params['name'])
			|| 	empty($params['wx_id'])
			|| 	empty($params['provinces'])
			|| 	empty($params['city'])
			|| 	empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");

			//检查用户登录
			// $login_result = $this->_login_check($params);
			// if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			// {
			// 	$response['sub_code'] = 1;$response['desc'] = __line__;break;
			// }

			//验证权限
			// $u_name = '';
			// $power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			// if (!$power_result)
			// {
			// 	$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			// }

			$mcobj = BaseFunction::getMC();

			//查询推荐码
			$obj_agentinfo_multi_factory =  new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item  = current($obj_agentinfo_multi);
				$type = $obj_agentinfo_multi_item->type;
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 3;$response['desc'] = __line__;break;
			}

			if($type == 9)
			{
				$new_type = 9;
			}
			else
			{
				$response['sub_code'] = 4;$response['desc'] = __line__;break;
			}

			//新增人员
			$obj_agentinfo = new AgentInfo();

            if(isset($obj_agentinfo))
            {
				$obj_agentinfo->aid = $params['children_aid'];
				$obj_agentinfo->name = $params['name'];
				$obj_agentinfo->wx_id = trim($params['wx_id']);
				$obj_agentinfo->p_aid = $params['aid'];
				$obj_agentinfo->type = $new_type;
				$obj_agentinfo->status = 2;//通过状态

				$obj_agentinfo->provinces = $params['provinces'];//通过状态
				$obj_agentinfo->city = $params['city'];//通过状态
				$obj_agentinfo->audit_eid = $params['aid'];
				$obj_agentinfo->last_amount = 0;
				$obj_agentinfo->p_num = 0;

				$obj_agentinfo->init_time = $itime;
				$obj_agentinfo->update_time = $itime;
				$obj_agentinfo->month = date("Ym",$itime);

            	$rawsqls[] = $obj_agentinfo->getInsertSql();
            }

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 5; $response['desc'] = __line__;break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//下级列表  city
	public function agent_info_list($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$data = array();
		$tmp = array();
		$data_count = 0;
		$page_count = CatConstant::CNT_PER_PAGE;
		$boss_arr =array();

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['page'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$page = isset($params['page']) ? intval($params['page']) : 1;

			//检查是否登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;break;
			}

			$mcobj = BaseFunction::getMC();
			$boss_arr = CatConstant::BOSS;

			do
			{
				$tmp_user_game_lock = $mcobj->setKeep(self::$agent_info.$params['aid'], 1, 1);
			}while (!$tmp_user_game_lock);
			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid']);
            if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
            {
                $obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
                $obj_agentinfo_multi_item = current($obj_agentinfo_multi);
            }
            else
            {
                $obj_agentinfo_multi_factory->clear();
                $response['sub_code'] = 2;$response['desc'] = __line__;break;
            }

			if(!empty($params['p_aid']) )
			{
				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',$params['p_aid']);
			}
			else
            {
                if ($obj_agentinfo_multi_item->type == 9)
                {
                    $obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',null,8);
                }
                else
                {
                    $obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',$params['aid']);
                }
                if(in_array($params['aid'],$boss_arr))
                {
                    $obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',null,9);
                }
            }

			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_list = $obj_agentinfo_list_factory->get();

                $data_count = count($obj_agentinfo_list);
				$obj_agentinfo_list_page_arr = array_slice($obj_agentinfo_list, ($page - 1) * $page_count, $page_count);

				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,implode(',',$obj_agentinfo_list_page_arr));
				if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
				{
					$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,$obj_agentinfo_list_factory);
					if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
					{
						$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
						$obj_agentinfo_multi = array_values($obj_agentinfo_multi);
		                usort($obj_agentinfo_multi,array('bigcat\controller\Business','cmp_agent_init_time'));
						if(isset($obj_agentinfo_multi) && is_array($obj_agentinfo_multi))
						{
							foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
							{
								if($obj_agentinfo_multi_item->status == 4)  //被查封的账号 不显示在列表中
								{
									unset($obj_agentinfo_multi_item);
								}
								else
								{
									$obj_agentinfo_multi_item->init_time = date("Y-m-d H:i:s",$obj_agentinfo_multi_item->init_time);
									$tmp[] = $obj_agentinfo_multi_item;
								}

							}
						}
					}
				}

			}
			else
			{
				$obj_agentinfo_list_factory->clear();

			}
			$mcobj->del(self::$agent_info.$params['aid'], self::$agent_info.$params['aid']);

            $data['data_count'] = $data_count;
            $data['page_count'] = $page_count;
			$data['agent_info'] = $tmp;
			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//微信获取订单号 app支付  city
	public function wx_get_out_trade_no($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();

		do {
			if (
				!isset($params['aid']) || !$params['aid']
			|| !isset($params['amount']) || !$params['amount']
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//第二种货币
			if(!empty($params['currency_type']) && $params['currency_type'] == 2)
			{
				$currency_type = $params['currency_type'];
				$total_fee = intval(Config::WX_PAY_CURRENCY2[$params['amount']]*100);
			}
			else
			{
				$currency_type = 0;
				$total_fee = intval(Config::WX_PAY[$params['amount']]*100);
			}

			//购买金额不能为0 或者配置金额不存在
			if(empty($total_fee))
			{
		    	BaseFunction::logger($this->log, "【data_request】:\n" . var_export($total_fee, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 2; $response['desc'] = __line__;  break;
			}
			//廊坊 文安后台 两个微信支付
			if(!empty($params['wxpay_type']) && $params['wxpay_type'] == 2)
			{
				$wxpay_type = Config::WXPAY_2;
			}
			else
			{
				$wxpay_type = Config::WXPAY;
			}


			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'wx_get_out_trade_no'
				, 'platform' => 'tocar'
				//, 'notify_url' => Config::MY_PATH      //本代码的地址,,,会存在  pay的表中,,待支付成功之后,调用改地址 ,给玩家充值
				, 'notify_url' => Config::GET_USER      //本代码的地址,,,会存在  pay的表中,,待支付成功之后,调用改地址 ,给玩家充值
				, 'call_back_param' => json_encode(array('aid'=>$params['aid'],'amount'=>$params['amount'],'currency_type'=>$currency_type))
				, 'total_fee' => $total_fee
				, 'type' => '2'
				, 'wxpay' => $wxpay_type
				, 'openId' => 1
				, 'trade_type_app' => 1
				);
			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::PAY_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);
			//$result = (BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
			//BaseFunction::logger($this->log, "【get_out_trade_no】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0) )
			{
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【get_out_trade_no】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__;  break;
			}

			$data = json_decode($result['data'],true);
			$response['data'] = $data;
		} while (false);

		return $response;
	}
	//微信获取订单号 jsapi支付  city
	public function wx_get_jsapi($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();

		do {
			if (
				empty($params['aid']) 
			|| empty($params['amount']) 
			|| empty($params['openId'])
			|| !isset($params['wx_type'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$currency_type = 0;
			$total_fee = intval(Config::WX_PAY[$params['amount']]*100);
			
			//购买金额不能为0 或者配置金额不存在
			if(empty($total_fee))
			{
		    	BaseFunction::logger($this->log, "【data_request】:\n" . var_export($total_fee, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 2; $response['desc'] = __line__;  break;
			}

			$wxpay = Config::WXPAY_LINFIY[$params['wx_type']];
			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'wx_get_out_trade_no'
				, 'platform' => 'tocar'
				, 'notify_url' => Config::GET_USER      //本代码的地址,,,会存在  pay的表中,,待支付成功之后,调用改地址 ,给玩家充值
				, 'call_back_param' => json_encode(array('aid'=>$params['aid'],'amount'=>$params['amount'],'currency_type'=>$currency_type))
				, 'total_fee' => $total_fee
				, 'type' => '2'
				, 'wxpay' => $wxpay
				, 'openId' => $params['openId']
				, 'trade_type_app' => 0               //jsapi=0   app=1
				);
			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::PAY_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0) )
			{
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【get_out_trade_no】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__;  break;
			}

			$data = json_decode($result['data'],true);
			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//配置参数   city
	public function get_conf()
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();
		$itime = time();

		do {
			$data['unit_price'] = Config::UNIT_PRICE;
			$data['unit_price_arr'] = Config::UNIT_PRICE_ARR;
			$data['init_amount'] = Config::INIT_AMOUNT;
			$data['today'] = date('Y-m-d', $itime);
			$data['yesterday'] = date('Y-m-d', $itime - 86400);
			if(defined("bigcat\\conf\\Config::GIFTGROUP"))
			{
				$data['gif_group'] = Config::GIFTGROUP;
			}
			$data['is_society'] = Config::IS_SOCIETY; //是否开通公会房 给前端显示用
			
			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//查找代理  city
	public function search_agent($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = array();

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['search_aid'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			//判断大客户是否已封号
			// $result = $this->_judge_agent_status($params);
			// if (!$result || $result['code'] != 0 || $result['sub_code'] != 0 )
			// {
			// 	$response['sub_code'] = 9;$response['desc'] = __line__;break;
			// }

			$mcobj = BaseFunction::getMC();

			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['search_aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current( array_values($obj_agentinfo_multi));
				if(isset($params['type']) && $params['type'] == 2 )
				{
					;
				}
				else
				{
					if($obj_agentinfo_multi_item->p_aid != $params['aid'])
					{
						$this->_getFather($params['search_aid'], $idArray, $params['aid']);
	                    if (!in_array($params['aid'], $idArray))
	                    {
	                        $response['sub_code'] = 3; $response['desc'] = __line__; break;
	                    }
					}
				}
				$obj_agentinfo_multi_item->init_time = date("Y-m-d H:i:s",$obj_agentinfo_multi_item->init_time);
				$data['list'] =$obj_agentinfo_multi_item;
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 4;$response['desc'] = __line__;	break;//记录不存在
			}

			//$data['list'] = $tmp;
			$response['data'] = $data;
		} while (false);

		return $response;
	}


	//自己用的删除代理信息  city  shop#
	public function delete_agent_info($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$data = array();
		$set_user_status = true;

		do {
			if(empty($params['api_key']) || $params['api_key'] != Config::API_KEY) //来自内部调用
			{
				if (empty($params['aid'])
				|| 	empty($params['key'])
				|| 	empty($params['del_aid'])
				|| 	empty($params['shop'])
				|| 	empty($params['type'])
				)
				{
					$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
				}

				//检查用户登录
				$login_result = $this->_login_check($params);
				if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
				{
					$response['sub_code'] = 1;$response['desc'] = __line__;	break;
				}
				//验证权限
				$u_name = '';
				$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
				if (!$power_result)
				{
					$response['sub_code'] = 2;$response['desc'] = __line__;	break;
				}
			}

			$mcobj = BaseFunction::getMC();
			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['del_aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

				if($params['type'] == 1)
				{
					//判断代理下面有没有玩家,,
					$result = $this->_get_agent_uid_num($params['del_aid']);
					if(empty($result['amount']))
					{
						$rawsqls[] = $obj_agentinfo_multi_item->getDelSql();
					}
					else
					{
						$response['sub_code'] = 4;$response['desc'] = __line__;	break;
					}
				}
				elseif($params['type'] == 2 || $params['type'] == 4 )
				{
					$obj_agentinfo_multi_item->status = 4;   //4是查封
					$obj_agentinfo_multi_item->close_down_info = "于".date("Y-m-d H:i:s",time())."被查封";   //4是查封
					$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
				}
				elseif($params['type'] == 3)
				{
					$obj_agentinfo_multi_item->status = 2;   //2是解封
					$obj_agentinfo_multi_item->close_down_info = "于".date("Y-m-d H:i:s",time())."被解封";   //4是查封
					$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
				}

			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 3; $response['desc'] = __line__; break;
			}


			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				$set_user_status = false;
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = CatConstant::ERROR_UPDATE; $response['desc'] = __line__; break;
			}

			if(isset($obj_agentinfo_multi_factory))
			{
				$obj_agentinfo_multi_factory->writeback();
			}

			//user_wx模块改status
			if($set_user_status)
			{
				$data_request = array(
				'mod' => 'Business'
				, 'act' => 'set_user_status'
				, 'type' => $params['type']
				, 'platform' => 'tocar'
				, 'aid' => $params['del_aid']
				//, 'key' => Config::API_KEY
				);

				$randkey = BaseFunction::encryptMD5($data_request);
				$url = Config::USER_WX_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
				$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
				{
					BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
					BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
					//$response['sub_code'] = 5; $response['desc'] = __line__; break;
				}
			}

			//user_php70模块改status
			if($set_user_status)
			{
				$data_request = array(
				'mod' => 'Business'
				, 'act' => 'set_user_status'
				, 'type' => $params['type']
				, 'platform' => 'tocar'
				, 'aid' => $params['del_aid']
				//, 'key' => Config::API_KEY
				);

				$randkey = BaseFunction::encryptMD5($data_request);
				$url = Config::USER_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
				$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
				{
					BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
					BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				}
			}

			if(isset($obj_agentinfo_multi_factory)  )
			{
				$obj_agentinfo_multi_factory->clear();
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}


	//下级代理 打印（管理员权限） no
	public function get_agent_info_excel($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();
		$tmp = array();
		$name = array();
		$title_arr = array();
		$tmp_aid_list = array();

		do {
			if (empty($params['aid'])
			||empty($params['key'])
			) {
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}
			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();
			$boss_arr = CatConstant::BOSS;

			if(!empty($params['p_aid']) )
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['p_aid']);
			}
			else
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			}
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
				$first_sheet_name = $obj_agentinfo_multi_item->name;
				$first_aid = $obj_agentinfo_multi_item->aid;
			}

			/////////////////////////////////////////一级表数据/////////////////
			if(in_array($params['aid'],$boss_arr))
			{
				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',null,8);
			}
			else
			{
				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',$params['aid']);
			}

			if(!empty($params['p_aid']) )
			{
				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',$params['p_aid']);
			}

			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_list = $obj_agentinfo_list_factory->get();

				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,$obj_agentinfo_list_factory);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					$obj_agentinfo_multi = array_values($obj_agentinfo_multi);

	                usort($obj_agentinfo_multi,array('bigcat\controller\Business','cmp_agent_init_time'));
					if(isset($obj_agentinfo_multi) && is_array($obj_agentinfo_multi))
					{
						foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
						{
							$obj_agentinfo_multi_item->init_time = date("Y-m-d H:i:s",$obj_agentinfo_multi_item->init_time);
							$tmp[$obj_agentinfo_multi_item->aid]['name'] = $obj_agentinfo_multi_item->name;
							$tmp[$obj_agentinfo_multi_item->aid]['aid'] = (string)$obj_agentinfo_multi_item->aid;
							$tmp[$obj_agentinfo_multi_item->aid]['area'] = $obj_agentinfo_multi_item->provinces.$obj_agentinfo_multi_item->city;
							if($obj_agentinfo_multi_item->type == 1)
							{
								$tmp[$obj_agentinfo_multi_item->aid]['type'] = '副会长';
							}
							elseif($obj_agentinfo_multi_item->type == 2)
							{
								$tmp[$obj_agentinfo_multi_item->aid]['type'] = '会长';
							}
							elseif($obj_agentinfo_multi_item->type == 8)
							{
								$tmp[$obj_agentinfo_multi_item->aid]['type'] = '合伙人';
							}
							elseif($obj_agentinfo_multi_item->type == 9)
							{
								$tmp[$obj_agentinfo_multi_item->aid]['type'] = '总公司';
							}
							$tmp[$obj_agentinfo_multi_item->aid]['last_amount'] = $obj_agentinfo_multi_item->last_amount;
							$tmp[$obj_agentinfo_multi_item->aid]['init_time'] = $obj_agentinfo_multi_item->init_time;
							$tmp[$obj_agentinfo_multi_item->aid]['info'] = $obj_agentinfo_multi_item->info;
							if($obj_agentinfo_multi_item->type != 2)
							{
								$tmp_aid_list[] = $obj_agentinfo_multi_item->aid;
							}
						}
					}
				}
			}
			else
			{
				$obj_agentinfo_list_factory->clear();
			}
			$data[$first_sheet_name."_".$first_aid] = $tmp;
			/////////////////////////////////下级循环数据////////////////////////////////////////
			foreach ($tmp_aid_list as  $value)
			{
				$tmps = array();

				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',$value);
				if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
				{
					$obj_agentinfo_list = $obj_agentinfo_list_factory->get();

					$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,$obj_agentinfo_list_factory);
					if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
					{
						$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
						$obj_agentinfo_multi = array_values($obj_agentinfo_multi);
		                usort($obj_agentinfo_multi,array('bigcat\controller\Business','cmp_agent_init_time'));
						if(isset($obj_agentinfo_multi) && is_array($obj_agentinfo_multi))
						{
							foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
							{
								$obj_agentinfo_multi_item->init_time = date("Y-m-d H:i:s",$obj_agentinfo_multi_item->init_time);
								$tmps[$obj_agentinfo_multi_item->aid]['name'] = $obj_agentinfo_multi_item->name;
								$tmps[$obj_agentinfo_multi_item->aid]['aid'] = (string)$obj_agentinfo_multi_item->aid;
								$tmps[$obj_agentinfo_multi_item->aid]['area'] = $obj_agentinfo_multi_item->provinces.$obj_agentinfo_multi_item->city;
								if($obj_agentinfo_multi_item->type == 1)
								{
									$tmps[$obj_agentinfo_multi_item->aid]['type'] = '副会长';
								}
								elseif($obj_agentinfo_multi_item->type == 2)
								{
									$tmps[$obj_agentinfo_multi_item->aid]['type'] = '会长';
								}
								elseif($obj_agentinfo_multi_item->type == 8)
								{
									$tmps[$obj_agentinfo_multi_item->aid]['type'] = '合伙人';
								}
								elseif($obj_agentinfo_multi_item->type == 9)
								{
									$tmps[$obj_agentinfo_multi_item->aid]['type'] = '总公司';
								}
								$tmps[$obj_agentinfo_multi_item->aid]['last_amount'] = $obj_agentinfo_multi_item->last_amount;
								$tmps[$obj_agentinfo_multi_item->aid]['init_time'] = $obj_agentinfo_multi_item->init_time;
								$tmps[$obj_agentinfo_multi_item->aid]['info'] = $obj_agentinfo_multi_item->info;
							}
						}
					}
				}
				else
				{
					$obj_agentinfo_list_factory->clear();
				}
				$name = $tmp[$value]['name'];
				$data[$name."_".$value] = $tmps;
			}

			$title_arr = array('姓名','手机号',	'区域','职位','剩余钻数','加入时间','备注'	);

			$filename = $first_sheet_name."下级人员明细".date("Ymd",time());//文件名称
			BaseFunction::write_xls($data, $title_arr, $filename);

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//自己游戏 充值////////////////////////////
	//查找玩家 city
	public function play_find_by_id($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['p_aid'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			$data_request = array(
			'mod' => 'Business'
			, 'act' => 'get_user'
			, 'platform' => 'gfplay'
			, 'uid' => $params['p_aid']
			);
			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
			if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

	    	$tmp['userId'] = $result->data->obj_user->uid;
	    	$tmp['nickName'] =  $result->data->obj_user->name;
			$tmp['specialGold'] =  $result->data->obj_user_game->currency;

			if(isset($result->data->obj_user_game->currency2))
			{
				$tmp['currency2'] = $result->data->obj_user_game->currency2;
			}
			
			if(isset($result->data->obj_user_game->score))
			{
				$tmp['score'] = $result->data->obj_user_game->score;
			}

			if(isset($result->data->obj_user_game->currency2))
			{
				$tmp['cup'] = $result->data->obj_user_game->cup;
			}
			
			$data['play_user'] = $tmp;
			$response['data'] = $data;
		} while (false);

		return $response;
	}


	//玩家充值  city  shop#
	public function play_recharge($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$data_tmp = array();
		$tmp = array();

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['p_aid'])
			|| 	empty($params['recharge_amount'])
			|| 	empty($params['shop'])
			|| 	(!empty($params['type']) && !in_array($params['type'],array(2,23,35,43)) )  //如果存在 ,,
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}
			if($params['recharge_amount'] <= 0 || floor($params['recharge_amount']) != $params['recharge_amount'])
			{
				$response['sub_code'] = 5;$response['desc'] = __line__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 8;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();

			//判断大客户剩余钻数够不够
			// $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			// if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			// {
			// 	$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
			// 	if(is_array($obj_agentinfo_multi))
			// 	{
			// 		$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
			// 		$last_amount = $obj_agentinfo_multi_item->last_amount;
			// 		if($last_amount == 0 || $last_amount < $params['recharge_amount'])
			// 		{
			// 			$obj_agentinfo_multi_factory->clear();
			// 			$response['sub_code'] = 3; $response['desc'] = __line__; break;
			// 		}
			// 		$data_tmp[$obj_agentinfo_multi_item->aid] = $obj_agentinfo_multi_item;

			// 	}
			// }
			// else
			// {
			// 	$obj_agentinfo_multi_factory->clear();
			// 	$response['sub_code'] = 2; $response['desc'] = __line__;break;
			// }


			//获取玩家昵称
			$data_request = array(
			'mod' => 'Business'
			, 'act' => 'get_user'
			, 'platform' => 'gfplay'
			, 'uid' => $params['p_aid']
			);
			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
			if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$tmp['nickName'] = $result->data->obj_user->name;
			$tmp['last_amount'] = $result->data->obj_user_game->currency;
			if(isset($result->data->obj_user_game->currency2))
			{
				$tmp['currency2'] = $result->data->obj_user_game->currency2;
			}
			
			if(isset($result->data->obj_user_game->score))
			{
				$tmp['score'] = $result->data->obj_user_game->score;
			}

			if(isset($result->data->obj_user_game->currency2))
			{
				$tmp['cup'] = $result->data->obj_user_game->cup;
			}

			$money= isset($params['money'])?$params['money'] : 0;
			$type = isset($params['type'])? $params['type'] : 2;
			//给玩家充值  远程协议
			$data_request = array(
			'mod' => 'Business'
			, 'act' => 'checkout_open_room'
			, 'platform' => 'gfplay'
			, 'uid' => $params['p_aid']
			, 'type' => $type
			, 'currency' => $params['recharge_amount']
			, 'money' => $money
			);
			$randkey = BaseFunction::encryptMD5($data_request);
			BaseFunction::logger($this->log, "【randkey】:\n" . var_export($randkey, true) . "\n" . __LINE__ . "\n");
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
			BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
			if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}


			//玩家充值记录
            $obj_playrecharge = new PlayRecharge();
            if(isset($obj_playrecharge))
            {
            	$obj_playrecharge->play_id = $params['p_aid'];
				$obj_playrecharge->play_name = $tmp['nickName'];
				if($type == 23) //红
				{
					$obj_playrecharge->last_amount = $tmp['currency2'];
					$obj_playrecharge->recharge_amount = $params['recharge_amount'];
					$obj_playrecharge->play_sum_amount = $tmp['currency2'] +$params['recharge_amount'];
				}
				elseif($type == 35)  //积分
				{
					$obj_playrecharge->last_amount = $tmp['score'];
					$obj_playrecharge->recharge_amount = $params['recharge_amount'];
					$obj_playrecharge->play_sum_amount = $tmp['score'] +$params['recharge_amount'];
				}
				elseif($type == 43)  //奖杯
				{
					$obj_playrecharge->last_amount = $tmp['cup'];
					$obj_playrecharge->recharge_amount = $params['recharge_amount'];
					$obj_playrecharge->play_sum_amount = $tmp['cup'] +$params['recharge_amount'];
				}
				else
				{
					$obj_playrecharge->last_amount = $tmp['last_amount'];
					$obj_playrecharge->recharge_amount = $params['recharge_amount'];
					$obj_playrecharge->play_sum_amount = $tmp['last_amount'] +$params['recharge_amount'];
				}

            	$obj_playrecharge->aid = $params['aid'];
            	$obj_playrecharge->recharge_time = $itime;
            	$obj_playrecharge->type = $params['type'];

				$rawsqls[] = $obj_playrecharge->getInsertSql();
            }

			//大客户扣钻
			// if(isset($data_tmp[$params['aid']]))
			// {
			// 	$data_tmp[$params['aid']]->last_amount = $data_tmp[$params['aid']]->last_amount - $params['recharge_amount'];
			// 	$rawsqls[] = $data_tmp[$params['aid']] ->getUpdateSql();
			// }
			// else
			// {
			// 	$response['sub_code'] = 4; $response['desc'] = __line__; break;
			// }

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR_UPDATE; $response['desc'] = __line__; break;
			}

			// if($obj_agentinfo_multi_factory)
			// {
			// 	$obj_agentinfo_multi_factory->writeback();
			// }

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//苹果支付接口 city
	public function pay_apple($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['aid'])
			|| 	empty($params['p_aid'])
			|| 	empty($params['recharge_amount'])
			|| 	empty($params['nickName'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();
			$last_amount = isset($params['last_amount']) ? intval($params['last_amount']) : 0;

			//验证苹果订单号是否重复
			$obj_palyrecharge_list_factory = new PlayRechargeListFactory($mcobj,$params['aid']);
			if($obj_palyrecharge_list_factory->initialize() && $obj_palyrecharge_list_factory->get())
			{
				BaseFunction::logger($this->log, "【get_aid】:\n" . var_export($params['aid'].'_isset', true) . "\n" . __LINE__ . "\n");
				$obj_palyrecharge_list_factory->clear();
				$response['sub_code'] = 2; $response['desc'] = __line__; break;
			}
			elseif(Config::DEBUG)
			{
				BaseFunction::logger($this->log, "【get_aid】:\n" . var_export($params['aid'].'_ok', true) . "\n" . __LINE__ . "\n");
			}

		    //玩家充值记录
            $obj_playrecharge = new PlayRecharge();
            if(isset($obj_playrecharge))
            {
            	$obj_playrecharge->play_id = $params['p_aid'];
            	$obj_playrecharge->play_name = $params['nickName'];
            	$obj_playrecharge->last_amount = $last_amount;
            	$obj_playrecharge->recharge_amount = $params['recharge_amount'];
            	$obj_playrecharge->play_sum_amount = $last_amount +$params['recharge_amount'];

            	$obj_playrecharge->aid = $params['aid'];
            	$obj_playrecharge->recharge_time = $itime;

				$rawsqls[] = $obj_playrecharge->getInsertSql();
            }

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR_UPDATE; $response['desc'] = __line__; break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//给玩家或代理扣钻  no
	public function del_agent_amount($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$data_tmp = array();
		$buy_amount = false;

		do{
			if(empty($params['aid']) //客服登录
			||empty($params['key'])
			||empty($params['shop'])
			||empty($params['recharge_aid'])   //被充值代理
			||empty($params['last_amount'])
			||empty($params['type']) 		//1玩家   2代理
			||!isset($params['activity_info']) 		//备注
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			//判断充值数量>=0
			if($params['last_amount'] <= 0 || floor($params['last_amount']) != $params['last_amount'])
			{
				$response['sub_code'] = 3;$response['desc'] = __line__;	break;
			}

			 $mcobj = BaseFunction::getMC();
			//查看aid  剩余钻够不够

			//给id充钻
			if($params['type'] == 2)
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['recharge_aid']);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

					if($params['last_amount'] <= $obj_agentinfo_multi_item->last_amount  )
					{
						$obj_agentinfo_multi_item->last_amount -= $params['last_amount'];						
						$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
					}
					else
					{
						$response['sub_code'] = 5; $response['desc'] = __line__; break;
					}
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 4; $response['desc'] = __line__; break;
				}

				if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
				{
					BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
					$response['code'] = CatConstant::ERROR_UPDATE; $response['desc'] = __line__; break;
				}
				else
				{
					$buy_amount = true;
					if(isset($obj_agentinfo_multi_factory))
					{
						$obj_agentinfo_multi_factory->writeback();
					}
				}

				// //充值记录
				if($buy_amount)
				{
					$params['buy_status'] = CatConstant::TYPE_9_DEL_ZUAN;
					$params['last_amount'] = (int)('-'.$params['last_amount']);

					$result = $this->_add_amount($params);
					if (!$result || !isset($result['code']) || $result['code'] != 0 )
					{
						BaseFunction::logger($this->log, "【result】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
						$return = false; break;
					}
				}
			}
			elseif($params['type'] == 1)
			{
				//获取玩家昵称
				$data_request = array(
				'mod' => 'Business'
				, 'act' => 'get_user'
				, 'platform' => 'gfplay'
				, 'uid' => $params['recharge_aid']
				);
				$randkey = BaseFunction::encryptMD5($data_request);
				$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
				$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
					BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
					BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
					$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
				}

				$tmp['nickName'] = $result->data->obj_user->name;
				$tmp['last_amount'] = $result->data->obj_user_game->currency;

				if($params['last_amount'] > $tmp['last_amount'])
				{
					$response['sub_code'] = 5; $response['desc'] = __line__; break;
				}

				//扣钻
				$params['last_amount'] = (int)('-'.$params['last_amount']);
				//给玩家充值  远程协议
				$data_request = array(
				'mod' => 'Business'
				, 'act' => 'checkout_open_room'
				, 'platform' => 'gfplay'
				, 'uid' => $params['recharge_aid']
				, 'type' => '2'
				, 'currency' => $params['last_amount']
				);
				$randkey = BaseFunction::encryptMD5($data_request);
				$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
				$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
					BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
					BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
					$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
				}

				//玩家充值记录
	            $obj_playrecharge = new PlayRecharge();
	            if(isset($obj_playrecharge))
	            {
	            	$obj_playrecharge->play_id = $params['recharge_aid'];
	            	$obj_playrecharge->play_name = $tmp['nickName'];
	            	$obj_playrecharge->last_amount = $tmp['last_amount'];
	            	$obj_playrecharge->recharge_amount = $params['last_amount'];
	            	$obj_playrecharge->play_sum_amount = $tmp['last_amount'] + $params['last_amount'];

					if(!empty($params['activity_info']))
					{
						$obj_playrecharge->activity_info = $params['activity_info'];
					}
	            	$obj_playrecharge->aid = $params['aid'];
	            	$obj_playrecharge->recharge_time = $itime;

					$rawsqls[] = $obj_playrecharge->getInsertSql();
	            }

	            if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
				{
					BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
					$response['code'] = CatConstant::ERROR_UPDATE; $response['desc'] = __line__; break;
				}
			}
			//aid扣钻

			$response['data'] = $data;
		}while(false);

		return $response;
	}

	//财务报表（管理员权限）no
	public function get_agent_buy_excel($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();
		$tmp = array();
		$tmp_name = array();
		$title_arr = array();
		$emd_time = strtotime( date('Ymd',time()))+86400;  //当天的时间
		$start_time = $emd_time - 86400*Config::COUNT_DAY;  //往前推了几天

		do {
			if (empty($params['aid'])
			||empty($params['key'])
			||empty($params['shop'])
			) {
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}
			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();

			$obj_agentinfo_list_factory =  new AgentInfoListFactory($mcobj,null,'',null,9);//公司身份
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,$obj_agentinfo_list_factory);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					if(isset($obj_agentinfo_multi) && is_array($obj_agentinfo_multi))
					{
						foreach ($obj_agentinfo_multi as $key => $obj_agentinfo_multi_item)
						{
							$tmp_name[$obj_agentinfo_multi_item->aid] = $obj_agentinfo_multi_item->name;
						}
					}
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 3;$response['desc'] = __line__;	break;
				}
			}
			else
			{
				$obj_agentinfo_list_factory->clear();
				$response['sub_code'] = 3;$response['desc'] = __line__;	break;
			}

			//总表微信
			$obj_agentbuy_list_factory = new AgentBuyListFactory($mcobj,null,'',null,6,null,$start_time,$emd_time);
			if($obj_agentbuy_list_factory->initialize() && $obj_agentbuy_list_factory->get())
			{
				$obj_agentbuy_multi_factory = new AgentBuyMultiFactory($mcobj,$obj_agentbuy_list_factory);
				if($obj_agentbuy_multi_factory->initialize() && $obj_agentbuy_multi_factory->get())
				{
					$obj_agentbuy_multi = $obj_agentbuy_multi_factory->get();
					if(isset($obj_agentbuy_multi) && is_array($obj_agentbuy_multi))
					{

						$obj_agentbuy_multi = array_values($obj_agentbuy_multi);
	                	usort($obj_agentbuy_multi,array('bigcat\controller\Business','cmp_agent_buy_time'));
						foreach ($obj_agentbuy_multi as  $obj_agentbuy_multi_item)
						{
								$obj_agentbuy_multi_item->buy_time = date("Y-m-d H:i:s",$obj_agentbuy_multi_item->buy_time);
								unset($obj_agentbuy_multi_item->month);
								if($obj_agentbuy_multi_item->buy_status == 1)
								{
									$obj_agentbuy_multi_item->buy_status ='支付宝';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 2)
								{
									$obj_agentbuy_multi_item->buy_status ='官方手动充值';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 3)
								{
									$obj_agentbuy_multi_item->buy_status ='代理返利';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 4)
								{
									$obj_agentbuy_multi_item->buy_status ='赠送';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 5)
								{
									$obj_agentbuy_multi_item->buy_status ='充值奖励';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 6)
								{
									$obj_agentbuy_multi_item->buy_status ='微信支付';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 9)
								{
									$obj_agentbuy_multi_item->buy_status ='扣钻';
								}

								$tmp[] =  $obj_agentbuy_multi_item;
						}
					}
				}
				$data['微信支付'] = $tmp;
			}
			else
			{
				$data['微信支付'] = array();
			}

			//总表支付宝
			$obj_agentbuy_list_factory = new AgentBuyListFactory($mcobj,null,'',null,1,null,$start_time,$emd_time);
			if($obj_agentbuy_list_factory->initialize() && $obj_agentbuy_list_factory->get())
			{
				$tmp = array();
				$obj_agentbuy_multi_factory = new AgentBuyMultiFactory($mcobj,$obj_agentbuy_list_factory);
				if($obj_agentbuy_multi_factory->initialize() && $obj_agentbuy_multi_factory->get())
				{
					$obj_agentbuy_multi = $obj_agentbuy_multi_factory->get();
					if(isset($obj_agentbuy_multi) && is_array($obj_agentbuy_multi))
					{

						$obj_agentbuy_multi = array_values($obj_agentbuy_multi);
	                	usort($obj_agentbuy_multi,array('bigcat\controller\Business','cmp_agent_buy_time'));
						foreach ($obj_agentbuy_multi as  $obj_agentbuy_multi_item)
						{
								$obj_agentbuy_multi_item->buy_time = date("Y-m-d H:i:s",$obj_agentbuy_multi_item->buy_time);
								unset($obj_agentbuy_multi_item->month);
								if($obj_agentbuy_multi_item->buy_status == 1)
								{
									$obj_agentbuy_multi_item->buy_status ='支付宝';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 2)
								{
									$obj_agentbuy_multi_item->buy_status ='官方手动充值';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 3)
								{
									$obj_agentbuy_multi_item->buy_status ='代理返利';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 4)
								{
									$obj_agentbuy_multi_item->buy_status ='赠送';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 5)
								{
									$obj_agentbuy_multi_item->buy_status ='充值奖励';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 6)
								{
									$obj_agentbuy_multi_item->buy_status ='微信支付';
								}
								elseif($obj_agentbuy_multi_item->buy_status == 9)
								{
									$obj_agentbuy_multi_item->buy_status ='扣钻';
								}

								$tmp[] =  $obj_agentbuy_multi_item;
						}
					}
				}
				$data['支付宝'] = $tmp;
			}
			else
			{
				$data['支付宝'] = array();
			}

			foreach($tmp_name as $key => $value)
			{
				$tmp = array();
				$obj_agentbuy_list_factory = new AgentBuyListFactory($mcobj,null,'',null,null,$key,$start_time,$emd_time);
				if($obj_agentbuy_list_factory->initialize() && $obj_agentbuy_list_factory->get())
				{
					$obj_agentbuy_multi_factory = new AgentBuyMultiFactory($mcobj,$obj_agentbuy_list_factory);
					if($obj_agentbuy_multi_factory->initialize() && $obj_agentbuy_multi_factory->get())
					{
						$obj_agentbuy_multi = $obj_agentbuy_multi_factory->get();
						if(isset($obj_agentbuy_multi) && is_array($obj_agentbuy_multi))
						{

							$obj_agentbuy_multi = array_values($obj_agentbuy_multi);
		                	usort($obj_agentbuy_multi,array('bigcat\controller\Business','cmp_agent_buy_time'));
							foreach ($obj_agentbuy_multi as  $obj_agentbuy_multi_item)
							{
									$obj_agentbuy_multi_item->buy_time = date("Y-m-d H:i:s",$obj_agentbuy_multi_item->buy_time);
									unset($obj_agentbuy_multi_item->month);
									if($obj_agentbuy_multi_item->buy_status == 1)
									{
										$obj_agentbuy_multi_item->buy_status ='支付宝';
									}
									elseif($obj_agentbuy_multi_item->buy_status == 2)
									{
										$obj_agentbuy_multi_item->buy_status ='官方手动充值';
									}
									elseif($obj_agentbuy_multi_item->buy_status == 3)
									{
										$obj_agentbuy_multi_item->buy_status ='代理返利';
									}
									elseif($obj_agentbuy_multi_item->buy_status == 4)
									{
										$obj_agentbuy_multi_item->buy_status ='赠送';
									}
									elseif($obj_agentbuy_multi_item->buy_status == 5)
									{
										$obj_agentbuy_multi_item->buy_status ='充值奖励';
									}
									elseif($obj_agentbuy_multi_item->buy_status == 6)
									{
										$obj_agentbuy_multi_item->buy_status ='微信支付';
									}
									elseif($obj_agentbuy_multi_item->buy_status == 9)
									{
										$obj_agentbuy_multi_item->buy_status ='扣钻';
									}

									$tmp[] =  $obj_agentbuy_multi_item;
							}
						}
					}
				}

				$data[$tmp_name[$key]] = $tmp;
			}

			$title_arr = array('充值序号','购钻ID','购钻金额','购钻数量','购钻来源','详情','操作人ID','充值时间');
			$filename = Config::NAME."充值明细".date("Ymd",time());//文件名称\

			//$data 数据格式
			// $data = array(
			// 	'唐'=>array(array('13','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('12','8618911554496','100','8618911551111','2016.01.20','')
			// 		       )
			// 	,'里'=>array(array('15','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('14','8618911554496','100','8618911551111','2016.01.20','')
			// 				)

			// 	);
			BaseFunction::write_agent_buy_xls($data, $title_arr, $filename);

			$response['data'] = $data;
		} while (false);

		return $response;
	}


	//微信分享  no
	public function get_jsapi_ticket($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$buy_amount = false;
		$strkey = "get_jsapi_ticket";
		$strkey_ticket = "get_jsapi_ticket".$params['aid'];
		$strkey_token = "get_token".$params['aid'];

		do {
			if (empty($params['aid'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			$sign_obj = null;
			$strkey = "get_jsapi_ticket".$params['aid'];

			$mcobj = BaseFunction::getMC();


			$access_token = $mcobj->get( $strkey, $strkey_token );
			//BaseFunction::logger($this->log, "【token】:\n" . var_export($access_token, true) . "\n" . __LINE__ . "\n");
			if(!$access_token)
			{
				$strkey_token_tmp = BaseFunction::get_access_token();
				if(!$strkey_token_tmp['access_token'])
				{
					BaseFunction::logger($this->log, "【token】:\n" . var_export($strkey_token_tmp, true) . "\n" . __LINE__ . "\n");
					$response['sub_code'] = 1; $response['desc'] = __line__; break;
				}

				$access_token = $strkey_token_tmp['access_token'];

				$mcobj->set($strkey, $strkey_token, $access_token,7200 );
			}


			$jsapi_ticket = $mcobj->get( $strkey, $strkey_ticket );
			//BaseFunction::logger($this->log, "【token】:\n" . var_export($jsapi_ticket, true) . "\n" . __LINE__ . "\n");

			if(!$jsapi_ticket && !empty($access_token))
			{
				$jsapi_ticket_tmp = BaseFunction::get_jsapi_ticket($access_token);
				if(!$jsapi_ticket_tmp['ticket'])
				{
					BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($jsapi_ticket_tmp, true) . "\n" . __LINE__ . "\n");
					$response['sub_code'] = 1; $response['desc'] = __line__; break;
				}

				$jsapi_ticket = $jsapi_ticket_tmp['ticket'];

				$mcobj->set($strkey, $strkey_ticket, $jsapi_ticket, 7200 );
			}

			$sign_obj = BaseFunction::get_sign($jsapi_ticket);
			$data['sign_obj'] = $sign_obj;
			BaseFunction::logger($this->log, "【sign_obj】:\n" . var_export($sign_obj, true) . "\n" . __LINE__ . "\n");

			$response['data'] = $data;
		} while (false);

		return $response;
	}


	//微信绑定  no
	public function add_or_update($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['aid'])
			||empty($params['form'])
			||empty($params['name'])
			||empty($params['provinces'])
			||empty($params['city'])
			)
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($params, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

			if( $params['form'] == 'wx_user')
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid']);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();

					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
					$obj_agentinfo_multi_item->name = $params['name'];
					$obj_agentinfo_multi_item->provinces = $params['provinces'];//通过状态
					$obj_agentinfo_multi_item->city = $params['city'];//通过状态
					if(!empty($params['wx_id']))
					{
						$obj_agentinfo_multi_item->wx_id = trim($params['wx_id']);
					}
					$obj_agentinfo_multi_item->update_time = $itime;

					$obj_agentinfo_multi_item->month = date("Ym",$itime);
					$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();

				}
				else
				{
					$obj_agentinfo_multi_factory->clear();

					if(empty($params['p_aid']))
					{
						$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
					}
					$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['p_aid']);
					if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
					{
						$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
						if(!empty($obj_agentinfo_multi))
						{
							$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
							$p_num = $obj_agentinfo_multi_item->p_num;
							$type_tmp = $obj_agentinfo_multi_item->type;
							if(9 == $type_tmp)
							{
								$type = 8;
							}
							elseif(8 == $type_tmp)
							{
								//8不能添加1
								if( defined("bigcat\\conf\\Config::ADD_AGENT1") && Config::ADD_AGENT1 == 1)
								{									
									if(defined("bigcat\\conf\\Config::ADD_AGENT1_arr") && !in_array($params['p_aid'],Config::ADD_AGENT1_arr))
									{
										$response['sub_code'] = 7;$response['desc'] = __line__;break;
									}
															
								}
								$type = 1;
							}
							elseif(1 == $type_tmp)
							{
								$type = 2;
							}
							elseif(2 == $type_tmp)
							{
								$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
							}


							//获取分成比例
							$sub_shared_arr = array();
							$third_shared_arr = array();
							$shared_info = $obj_agentinfo_multi_item->shared_info;
							$shared_arr = $this->_get_shared($p_num, $type_tmp, $sub_shared_arr ,$shared_info ,$third_shared_arr);
						}
					}
					else
					{
						$obj_agentinfo_multi_factory->clear();
						$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
					}

					$obj_agentinfo = new AgentInfo();
		            if(isset($obj_agentinfo))
		            {
						$obj_agentinfo->aid = $params['aid'];
						$obj_agentinfo->name = $params['name'];
						$obj_agentinfo->p_aid = $params['p_aid'];
						if(!empty($params['wx_id']))
						{
							$obj_agentinfo->wx_id = trim($params['wx_id']);
						}
						$obj_agentinfo->type = $type;
						$obj_agentinfo->status = 2;//通过状态

						$obj_agentinfo->provinces = $params['provinces'];//
						$obj_agentinfo->city = $params['city'];//通过状态
						$obj_agentinfo->audit_eid = $params['aid'];
						$obj_agentinfo->last_amount = 0;

						if(8 == $type)
						{
							if($type_tmp == 9 )
							{
								$obj_agentinfo->p_num = $params['aid'];
							}
							else
							{
								$response['sub_code'] = 5; $response['desc'] = __line__;return $response;
							}
						}
						else
						{
							$obj_agentinfo->p_num = $p_num;
						}
						$obj_agentinfo->init_time = $itime;
						$obj_agentinfo->update_time = $itime;
						$obj_agentinfo->month = date("Ym",$itime);					
						$obj_agentinfo->shared = $shared_arr[$type];
						$obj_agentinfo->sub_shared = $sub_shared_arr[$type];
						$obj_agentinfo->third_shared = $third_shared_arr[$type];
						$obj_agentinfo->shared_info = $shared_info;
						

		            	$rawsqls[] = $obj_agentinfo->getInsertSql();
		            }
				}

				if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
				{
					BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
					$response['sub_code'] = 3; $response['desc'] = __line__;break;
				}

				if(isset($obj_agentinfo_multi_factory))
				{
					$obj_agentinfo_multi_factory->writeback();
				}
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//添加客服经理  no
	public function boss_add_agent($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = array();
		$p_num = 0;
		$p_aid = 0;

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['children_aid'])
			|| 	empty($params['name'])
			|| 	!isset($params['wx_id'])
			|| 	empty($params['type']) //1 客服经理  2客服  8总代理  9总公司
			|| 	!in_array($params['type'],array(8,9)) //1 客服经理  2客服  8总代理  9总公司
			|| 	empty($params['shop'])
			|| 	empty($params['provinces'])
			|| 	empty($params['city'])
			|| 	empty($params['shared'])
			|| 	empty($params['sub_shared'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();
			
			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null ,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
				$type = $obj_agentinfo_multi_item->type;
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 6;$response['desc'] = __line__;	break;
			}

			if(9 != $type)
			{
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}

			if( $params['type'] == 8)
			{
				$p_num = $params['children_aid'];
				$p_aid = $params['aid'];
			}
			//处理分成比例		
			{
				$shared_arr = $params['shared'];
				if(  $shared_arr[9]*100 != 100 
				|| $shared_arr[8]*100 > 100 || $shared_arr[8]*100 < 0
				|| $shared_arr[1]*100 > 100 || $shared_arr[1]*100 < 0
				|| $shared_arr[2]*100 > 100 || $shared_arr[2]*100 < 0
				)
				{
					$response['sub_code'] = 3; $response['desc'] = __line__;break;
				}
	
				$sub_shared_arr = $params['sub_shared'];
				if(  $sub_shared_arr[9]*100 != 100 
				|| $sub_shared_arr[8]*100 > 100 || $sub_shared_arr[8]*100 < 0
				|| $sub_shared_arr[1]*100 > 100 || $sub_shared_arr[1]*100 < 0
				|| $sub_shared_arr[2]*100 > 100 || $sub_shared_arr[2]*100 != $shared_arr[2]*100
				)
				{
					$response['sub_code'] = 3; $response['desc'] = __line__;break;
				}

				$third_shared_arr = $params['third_shared'];
				if( $third_shared_arr[8]*100 > 100 || $third_shared_arr[8]*100 < 0	)
				{
					$response['sub_code'] = 3; $response['desc'] = __line__;break;
				}
				
				$marge_shared[] = $shared_arr;
				$marge_shared[] = $sub_shared_arr;
				$marge_shared[] = $third_shared_arr;
				
				$shared_info = json_encode($marge_shared);
				//临时补位
				// $third_shared_arr[9] = 1;
				// $third_shared_arr[1] = 0;
				// $third_shared_arr[2] = 0;

			}

			//新增人员
			$obj_agentinfo = new AgentInfo();

            if(isset($obj_agentinfo))
            {
				$obj_agentinfo->aid = $params['children_aid'];
				$obj_agentinfo->name = $params['name'];
				$obj_agentinfo->wx_id = trim($params['wx_id']);
				$obj_agentinfo->provinces = $params['provinces'];//通过状态
				$obj_agentinfo->city = $params['city'];//通过状态
				$obj_agentinfo->p_aid = $p_aid;
				$obj_agentinfo->type = $params['type'];

				$obj_agentinfo->status = 2;//通过状态

				$obj_agentinfo->audit_eid = $params['aid'];
				$obj_agentinfo->last_amount = 0;
				$obj_agentinfo->p_num = $p_num;

				$obj_agentinfo->init_time = $itime;
				$obj_agentinfo->update_time = $itime;
				$obj_agentinfo->month = date("Ym",$itime);
				$obj_agentinfo->shared = $shared_arr[$params['type']];
				$obj_agentinfo->sub_shared = $sub_shared_arr[$params['type']];
				$obj_agentinfo->third_shared = $third_shared_arr[$params['type']];
				$obj_agentinfo->shared_info = $shared_info;

            	$rawsqls[] = $obj_agentinfo->getInsertSql();
            }

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 4; $response['desc'] = __line__;break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//全部代理报表（管理员权限）city  shop#
	public function all_agent($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();
		$tmp = array();
		$title_arr = array();

		do {
			if (empty($params['aid'])
			||empty($params['key'])
			||empty($params['shop'])
			) {
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}
			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();

			$obj_agentinfo_list_factory =  new AgentInfoListFactory($mcobj);//公司身份
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,$obj_agentinfo_list_factory);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					$sum = count($obj_agentinfo_multi);
					if(isset($obj_agentinfo_multi) && is_array($obj_agentinfo_multi))
					{
						foreach ($obj_agentinfo_multi as $key => $obj_agentinfo_multi_item)
						{
							unset($obj_agentinfo_multi_item->update_time);
							unset($obj_agentinfo_multi_item->wx_id);
							unset($obj_agentinfo_multi_item->opend_status);
							unset($obj_agentinfo_multi_item->status);
							unset($obj_agentinfo_multi_item->audit_eid);
							unset($obj_agentinfo_multi_item->info);
							unset($obj_agentinfo_multi_item->close_down_info);
							unset($obj_agentinfo_multi_item->p_num);
							unset($obj_agentinfo_multi_item->close_down_time);
							unset($obj_agentinfo_multi_item->p_num);
							if($obj_agentinfo_multi_item->type == 9)
							{
								$obj_agentinfo_multi_item->type ='公司身份';
							}
							elseif($obj_agentinfo_multi_item->type == 8)
							{
								$obj_agentinfo_multi_item->type ='城市合伙人';
							}
							elseif($obj_agentinfo_multi_item->type == 1)
							{
								$obj_agentinfo_multi_item->type ='会长';
							}
							elseif($obj_agentinfo_multi_item->type == 2)
							{
								$obj_agentinfo_multi_item->type ='副会长';
							}

							$obj_agentinfo_multi_item->init_time = date("Y-m-d",$obj_agentinfo_multi_item->init_time);
							unset($obj_agentinfo_multi_item->update_time);
							unset($obj_agentinfo_multi_item->month);
							unset($obj_agentinfo_multi_item->date_init_time);
							$tmp[] =  $obj_agentinfo_multi_item;

						}
					}
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 3;$response['desc'] = __line__;	break;
				}
			}
			else
			{
				$obj_agentinfo_list_factory->clear();
				$response['sub_code'] = 3;$response['desc'] = __line__;	break;
			}

			$data['全部代理'] = $tmp;

			$title_arr = array('代理ID','代理姓名','所在省份','所在城市','推荐人ID','身份','剩余钻数','加入时间','直属用户分成占比','下属用户分成占比','json');
			$filename = Config::NAME.$sum."名全部代理明细".date("Ymd",time());//文件名称\

			//$data 数据格式
			// $data = array(
			// 	'唐'=>array(array('13','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('12','8618911554496','100','8618911551111','2016.01.20','')
			// 		       )
			// 	,'里'=>array(array('15','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('14','8618911554496','100','8618911551111','2016.01.20','')
			// 				)

			// 	);
			BaseFunction::write_agent_buy_xls($data, $title_arr, $filename,$sum);

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//全部kpi数据  no
	public function kpi_get_all($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();

		do {
			if (empty($params['aid'])
			||empty($params['key'])
			||empty($params['shop'])
			) {
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			foreach (CatConstant::GET_USER as $key => $get_url)
			{
				$obj_kpi_factory = new KpiFactory($mcobj, $this->kpi_key,$get_url);
				if($obj_kpi_factory->initialize() && $obj_kpi_factory->get())
				{
					$obj_kpi = $obj_kpi_factory->get();
					foreach($obj_kpi as  $obj_kpi_item)
					{
						if(isset($obj_kpi_item->data->kpi_list[0]))
			            {
			                $data[$key][] = $obj_kpi_item->data->kpi_list[0];
			            }

			            if(isset($obj_kpi_item->data->kpi_list[1]))
			            {
			                $data[$key][] = $obj_kpi_item->data->kpi_list[1];
			            }
					}
				}

				if(isset($obj_kpi_factory ))
				{
					$obj_kpi_factory->writeback();
					$obj_kpi_factory->clear();
				}
			}

			$response['data'] = $data;

		} while (false);

		return $response;
	}

    
    //KPI数据  city
    public function kpi_get($params)
    {
        $response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
        $rawsqls = array();
        $itime = time();
        $data = array();
        $aidArray = array();

        do {
            if (empty($params['aid'])
				|| 	empty($params['key'])
            )
            {
                $response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
            }

            //检查用户登录
            $login_result = $this->_login_check($params);
            if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
            {
                $response['sub_code'] = 1;$response['desc'] = __line__;	break;
            }

            $mcobj = BaseFunction::getMC();
            $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
            if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
            {
                $obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
                $obj_agentinfo_multi_item = current($obj_agentinfo_multi);
                $type = $obj_agentinfo_multi_item->type;
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
			}
			
			if(9 == $type)//pc版需要加shop  暂时
			{
				if(!empty($params['shop']))
				{
					//验证权限
					$u_name = '';
					$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
					if (!$power_result)
					{
						$response['sub_code'] = 2;$response['desc'] = __line__;	break;
					}
				}
				else
				{
					$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
				}			
			}

			//获取下属用户
            $aidArray = $this->_getSon($params['aid']);

            if (!is_array($aidArray))
            {
                $response['sub_code'] = 3; $response['desc'] = __line__; break;
            }
			$obj_kpinew_factory = new KpiNewFactory($mcobj,$params['aid'] , $type, implode(',', $aidArray));
			if($obj_kpinew_factory->initialize() && $obj_kpinew_factory->get())
			{
				$obj_kpinew = $obj_kpinew_factory->get();
				$data = $obj_kpinew->data->list;
			}
			else
			{
				$obj_kpinew_factory->clear();
			}

            if (!empty($data))
            {
                foreach ($data as $key=>$value)
                {
                    $value->id_time = date('Y-m-d', $value->id_time);
                }
            }

			$response['data'] = $obj_kpinew->data;
			
        } while (false);

        return $response;
    }

	//user模块调用 首次登陆判断是否 已经封号  no
	public function judge_is_chafeng($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();

		do {
			if (empty($params['aid'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

 			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				if(is_array($obj_agentinfo_multi))
				{
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

					if($obj_agentinfo_multi_item->status == 4)//判断大客户是否已封号
					{
						$response['sub_code'] = 1; $response['desc'] = __line__;break;
					}
				}
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//强制更换代理手机号  city
	public function change_agent($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do{
			if(empty($params['aid_of_operator'])
			|| empty($params['aid_before_change'])
			|| empty($params['aid_after_change'])
			|| empty($params['key'])
			|| empty($params['name'])
			|| !isset($params['wx_id'])
			|| !isset($params['num'])
			|| empty($params['provinces'])
			|| empty($params['city'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$params['aid'] = $params['aid_of_operator'];
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			 //验证短信验证码
			 if (true)
			 {
				 $data_request = array(
					 'mod' => 'Business'
				 , 'act' => 'login_num_check'
				 , 'type' => '2'    //验证类型此type非彼type  密码验证用1   添加人员用2
				 , 'platform' => 'tocar'
				 , 'aid' => $params['aid_after_change']
				 , 'num' => $params['num']
				 , 'key' => Config::API_KEY
				 );
	 
				 $randkey = BaseFunction::encryptMD5($data_request);
				 $url = Config::USER_PATH."?randkey=".$randkey."&c_version=0.0.1";
				 $result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				 if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
					 BaseFunction::logger($this->log, "【data_request】:\n".var_export($data_request, true)."\n".__LINE__."\n");
					 BaseFunction::logger($this->log, "【login_check】:\n".var_export($result, true)."\n".__LINE__."\n");
					 $response['sub_code'] = 5; $response['desc'] = __line__; break;
				 }
			 }

			$mcobj = BaseFunction::getMC();

			$params_tmp['aid'] = $params['aid_before_change'];
			$params_tmp['end_time'] = strtotime(date("Ymd",time()));
			$params_tmp['start_time'] = Config::START_INCOME_TIME;
			if($this->_have_income($params_tmp))
			{ 
				$response['sub_code'] = 7; $response['desc'] = __line__; break;
			}


			////////////////验证要更换的新的手机号码 是否已经存在////////////////////
			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid_after_change']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$response['sub_code'] = 6; $response['desc'] = __line__; break;
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid_before_change']);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
					$type = $obj_agentinfo_multi_item->type;
					$old_p_num = $obj_agentinfo_multi_item->p_num;
					//判断修改的是不自己的下级					
					if($obj_agentinfo_multi_item->p_aid != $params['aid_of_operator'])
					{
						$response['sub_code'] = 8; $response['desc'] = __line__; break;
					}
					//新增新号码
					$obj_agentinfo = new AgentInfo();
		            if(isset($obj_agentinfo))
		            {
						$obj_agentinfo->aid = $params['aid_after_change'];
						$obj_agentinfo->wx_id = trim($params['wx_id']);
						$obj_agentinfo->name = $params['name'];
						$obj_agentinfo->provinces = $params['provinces'];
						$obj_agentinfo->city =$params['city'];

						$obj_agentinfo->p_aid = $obj_agentinfo_multi_item->p_aid;
						$obj_agentinfo->type =$obj_agentinfo_multi_item->type;
						$obj_agentinfo->opend_status = $obj_agentinfo_multi_item->opend_status;
						$obj_agentinfo->status = $obj_agentinfo_multi_item->status;
						$obj_agentinfo->audit_eid = $obj_agentinfo_multi_item->audit_eid;

						$obj_agentinfo->info = $obj_agentinfo_multi_item->info;
						$obj_agentinfo->last_amount = $obj_agentinfo_multi_item->last_amount;
						$obj_agentinfo->close_down_info = $obj_agentinfo_multi_item->close_down_info;
						$obj_agentinfo->close_down_time = $obj_agentinfo_multi_item->close_down_time;
						if($obj_agentinfo_multi_item->type == 8)
						{
							$obj_agentinfo->p_num = $params['aid_after_change'];
							$p_num = $params['aid_after_change'];
						}
						else
						{
							$obj_agentinfo->p_num = $obj_agentinfo_multi_item->p_num;
							$p_num = $obj_agentinfo_multi_item->p_num;
						}

						$obj_agentinfo->init_time = $obj_agentinfo_multi_item->init_time;
						$obj_agentinfo->update_time = $itime;
						$obj_agentinfo->month = $obj_agentinfo_multi_item->month;						
						$obj_agentinfo->shared = $obj_agentinfo_multi_item->shared;
						$obj_agentinfo->sub_shared = $obj_agentinfo_multi_item->sub_shared;
						$obj_agentinfo->shared_info = $obj_agentinfo_multi_item->shared_info;
						$obj_agentinfo->third_shared = $obj_agentinfo_multi_item->third_shared;
						
		            	$rawsqls[] = $obj_agentinfo->getInsertSql();
					}

		            //旧手机号删除
					//$rawsqls[] = $obj_agentinfo_multi_item->getDelSql();
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 4; $response['desc'] = __line__; break;
				}
			}

			////////////////////////修改p_aid//////////////////////////////
			//修改下级
			//$rawsqls = array();
			$tmps_obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null, '', $params['aid_before_change']);
			if($tmps_obj_agentinfo_list_factory->initialize() && $tmps_obj_agentinfo_list_factory->get())
			{
				$tmps_obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, $tmps_obj_agentinfo_list_factory);
				if($tmps_obj_agentinfo_multi_factory->initialize() && $tmps_obj_agentinfo_multi_factory->get())
				{
					$tmps_obj_agentinfo_multi = $tmps_obj_agentinfo_multi_factory->get();
					if(isset($tmps_obj_agentinfo_multi) && is_array($tmps_obj_agentinfo_multi))
					{
						foreach($tmps_obj_agentinfo_multi as $tmps_obj_agentinfo_multi_item)
						{
							$tmps_obj_agentinfo_multi_item->p_aid = $params['aid_after_change'];
							$tmps_obj_agentinfo_multi_item->info = $params['info'];
							$tmps_obj_agentinfo_multi_item->update_time = $itime;
							$tmps_obj_agentinfo_multi_item->p_num = $p_num;
							$rawsqls[] = $tmps_obj_agentinfo_multi_item->getUpdateSql();
						}
					}
				}
				else
				{
					$tmps_obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 4; $response['desc'] = __line__; break;
				}
			}

			//如果更换的是8身份  修改身份为2的人
			if($type == 8)
			{
				$sql = "select `aid` from `agent_info` where `p_num` = ".$old_p_num." and `type` = 2";
				$obj_agentinfo_list_factory_2 = new AgentInfoListFactory($mcobj, null, null, null, null, null, null, $sql);
				if($obj_agentinfo_list_factory_2->initialize() && $obj_agentinfo_list_factory_2->get())
				{
					$obj_agentinfo_multi_factory_2 = new AgentInfoMultiFactory($mcobj, $obj_agentinfo_list_factory_2);
					if($obj_agentinfo_multi_factory_2->initialize() && $obj_agentinfo_multi_factory_2->get())
					{
						$obj_agentinfo_multi_2 = $obj_agentinfo_multi_factory_2->get();
						if(is_array($obj_agentinfo_multi_2))
						{
							foreach($obj_agentinfo_multi_2 as $obj_agentinfo_multi_item_2)
							{
								$obj_agentinfo_multi_item_2->p_num = $p_num;										
								$rawsqls[] = $obj_agentinfo_multi_item_2->getUpdateSql();
							}
						}
					}
				}

			}

			//原子操作$rawsqls
			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR_UPDATE; $response['desc'] = __line__; break;
			}
			else
			{
				if(isset($obj_agentinfo_multi_factory))
				{
					$obj_agentinfo_multi_factory->clear();
				}
				if(isset($tmps_obj_agentinfo_multi_factory))
				{
					$tmps_obj_agentinfo_multi_factory->writeback();
				}
				if(isset($obj_agentinfo_multi_factory_2))
				{
					$obj_agentinfo_multi_factory_2->writeback();
				}
			}

			//内部 将旧账号 查封处理
			$params['del_aid'] = $params['aid_before_change'];
			$params['type'] = 4;//被替换账号  是同一个微信 两个号码,解决 无法绑定
			$params['api_key'] = Config::API_KEY;
			$result_delete = $this->delete_agent_info($params);
			if (!$result_delete || !isset($result_delete['code']) || $result_delete['code'] != 0 || (isset($result_delete['sub_code']) && $result_delete['sub_code'] != 0))
			{
				BaseFunction::logger($this->log, "【result_delete】:\n" . var_export($result_delete, true) . "\n" . __LINE__ . "\n");
			}
								
			///////////////////////////更改用户agent_id////////////////////////////
            $data_request = array(
                'mod' => 'Business',
                'act' => 'change_user_agent',
                'platform' => 'gfplay',
                'aid_before_change' => $params['aid_before_change'],
                'aid_after_change' => $params['aid_after_change']
            );

            $randkey = BaseFunction::encryptMD5($data_request);
            $url = Config::GET_USER."?randkey=".$randkey."&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
            if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
            {				
				//user端不成功标记下
                $tmp_obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid_after_change']);
                if ($tmp_obj_agentinfo_multi_factory->initialize() && $tmp_obj_agentinfo_multi_factory->get())
                {
                    $tmp_obj_agentinfo_multi = $tmp_obj_agentinfo_multi_factory->get();
                    $tmp_obj_agentinfo_multi_item = current($tmp_obj_agentinfo_multi);
                    $tmp_obj_agentinfo_multi_item->info = $tmp_obj_agentinfo_multi_item->info. '更改用户agent_id失败';
                    $rawsqls[] = $tmp_obj_agentinfo_multi_item->getUpdateSql();
                }

                if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
                {
					BaseFunction::logger($this->log, "【result】:\n".var_export($rawsqls, true)."\n".__LINE__."\n");
					$response['sub_code'] = 3; $response['desc'] = __line__; break;
				}
				else
				{
					if(isset($tmp_obj_agentinfo_multi_factory))
					{
						$tmp_obj_agentinfo_multi_factory->writeback();
					}
				}
				
				BaseFunction::logger($this->log, "【更新玩家agentid】:\n".var_export($result, true)."\n".__LINE__."\n");
                $response['sub_code'] = 3;$response['desc'] = __line__;break;
            }

			$response['data'] = $data;
		}while(false);

		return $response;
	}

	//强制更换上级代理 city shop#
	public function chmod_p_aid($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$is_chomd_yes = false;
		$search_aid_type = 255;
		$search_aid_p_num = 255;

		do{
			if(empty($params['aid'])
			||empty($params['key'])
			||empty($params['search_aid'])
			||empty($params['shop'])
			||!isset($params['chmod_aid'])
			||empty($params['type'])
			||!in_array($params['type'],[1,2,3])
			||!isset($params['info'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();
			do
			{
				$tmp_user_game_lock = $mcobj->setKeep(self::$agent_info.$params['aid'], 1, 1);
			}while (!$tmp_user_game_lock);

			if($params['type'] != 3)
			{
				//验证要更换的上级的手机号码 是否已经存在
				$obj_agentinfo_multi_factory_chomd = new AgentInfoMultiFactory($mcobj, null, $params['chmod_aid']);
				if($obj_agentinfo_multi_factory_chomd->initialize() && $obj_agentinfo_multi_factory_chomd->get())
				{
					$obj_agentinfo_multi_chomd = $obj_agentinfo_multi_factory_chomd->get();
					$obj_agentinfo_multi_item_chomd = current($obj_agentinfo_multi_chomd);

					//获取分成比例
					$sub_shared_arr = array();
					$third_shared_arr = array();
					$shared_info = $obj_agentinfo_multi_item_chomd->shared_info;
					$shared_arr = $this->_get_shared($obj_agentinfo_multi_item_chomd->p_num, $obj_agentinfo_multi_item_chomd->type, $sub_shared_arr ,$shared_info ,$third_shared_arr);
				}
				else
				{
					$obj_agentinfo_multi_factory_chomd->clear();
					$response['sub_code'] = 8; $response['desc'] = __line__; break;
				}
			}


			//更换上级代理
			if($params['type'] == 1)
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['search_aid']);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

					if($obj_agentinfo_multi_item->type == 2 && $obj_agentinfo_multi_item_chomd->type == 1)
					{
						$is_chomd_yes = true;
					}
					else if($obj_agentinfo_multi_item->type == 1 && $obj_agentinfo_multi_item_chomd->type == 8)
					{
						$is_chomd_yes = true;
					}
					else if($obj_agentinfo_multi_item->type == 8 && $obj_agentinfo_multi_item_chomd->type == 9)
					{
						$is_chomd_yes = true;
					}
					else
					{
						$response['sub_code'] = 5; $response['desc'] = __line__; break;
					}

					if(!$is_chomd_yes)
					{
						$response['sub_code'] = 5; $response['desc'] = __line__; break;
					}
					

					$obj_agentinfo_multi_item->info = $params['info'].",原上级代理为:". $obj_agentinfo_multi_item->p_aid;
					$obj_agentinfo_multi_item->p_aid = $params['chmod_aid'];
					if($obj_agentinfo_multi_item_chomd->type != 9)
					{
						$obj_agentinfo_multi_item->p_num = $obj_agentinfo_multi_item_chomd->p_num;						
					}
					if($obj_agentinfo_multi_item->type == 2 || $obj_agentinfo_multi_item->type == 1) //要是type=8   分成比例就不跟随变动了
					{
						$obj_agentinfo_multi_item->shared = $shared_arr[$obj_agentinfo_multi_item->type];
						$obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$obj_agentinfo_multi_item->type];
						$obj_agentinfo_multi_item->third_shared = $third_shared_arr[$obj_agentinfo_multi_item->type];
						$obj_agentinfo_multi_item->shared_info = $shared_info;
					}				
					
					$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();

					if($obj_agentinfo_multi_item->type == 1)
					{
						$sql = "select `aid` from `agent_info` where `p_aid` = ".intval($params['search_aid'])." and `type` = 2";
						$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null, null, null, null, null, null, $sql);
						if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
						{
							$obj_agentinfo_multi_factorys = new AgentInfoMultiFactory($mcobj, $obj_agentinfo_list_factory);
							if($obj_agentinfo_multi_factorys->initialize() && $obj_agentinfo_multi_factorys->get())
							{
								$obj_agentinfo_multi = $obj_agentinfo_multi_factorys->get();
								if(is_array($obj_agentinfo_multi))
								{
									foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
									{
										$obj_agentinfo_multi_item->p_num = $obj_agentinfo_multi_item_chomd->p_num;
																				
										$obj_agentinfo_multi_item->shared = $shared_arr[$obj_agentinfo_multi_item->type];
										$obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$obj_agentinfo_multi_item->type];
										$obj_agentinfo_multi_item->third_shared = $third_shared_arr[$obj_agentinfo_multi_item->type];
										$obj_agentinfo_multi_item->shared_info = $shared_info;
										
										$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();

									}
								}
							}
						}
					}
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 3; $response['desc'] = __line__; break;
				}
			}

			//A下级代理 全部转给B
			if($params['type'] == 2)
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['search_aid']);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
					//A和B的身份是同级   A的下级才能 合并给 B
					if($obj_agentinfo_multi_item->type != $obj_agentinfo_multi_item_chomd->type)
					{
						$response['sub_code'] = 6; $response['desc'] = __line__; break;
					}
					$search_aid_type = $obj_agentinfo_multi_item->type;
					$search_aid_p_num = $obj_agentinfo_multi_item->p_num;
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 3; $response['desc'] = __line__; break;
				}

				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null , '', $params['search_aid']);
				if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
				{
					$obj_agentinfo_multi_factorys = new AgentInfoMultiFactory($mcobj, $obj_agentinfo_list_factory);
					if($obj_agentinfo_multi_factorys->initialize() && $obj_agentinfo_multi_factorys->get())
					{
						$obj_agentinfo_multi = $obj_agentinfo_multi_factorys->get();
						if(isset($obj_agentinfo_multi) && is_array($obj_agentinfo_multi))
						{
							foreach ($obj_agentinfo_multi as $obj_agentinfo_multi_item)
							{
								$obj_agentinfo_multi_item->info = $params['info'].",原上级代理为:". $params['search_aid'];
								$obj_agentinfo_multi_item->p_aid = $params['chmod_aid'];
								if($obj_agentinfo_multi_item_chomd->type != 9)
								{
									$obj_agentinfo_multi_item->p_num = $obj_agentinfo_multi_item_chomd->p_num;						
								}
															
								if($search_aid_type != 9)//两个公司身份合并下级  比例应该保持不变
								{
									$obj_agentinfo_multi_item->shared = $shared_arr[$obj_agentinfo_multi_item->type];
									$obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$obj_agentinfo_multi_item->type];
									$obj_agentinfo_multi_item->third_shared = $third_shared_arr[$obj_agentinfo_multi_item->type];
			
									$obj_agentinfo_multi_item->shared_info = $shared_info;
								}								
								$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
							}
						}
					}
					else
					{
						$obj_agentinfo_multi_factorys->clear();
						$response['sub_code'] = 7; $response['desc'] = __line__; break;
					}
				}
				else
				{
					$obj_agentinfo_list_factory->clear();
					$response['sub_code'] = 7; $response['desc'] = __line__; break;
				}

				if($search_aid_type == 8)
				{
					$sql = "select `aid` from `agent_info` where `p_num` = ".$search_aid_p_num." and `type` = 2";

					$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null, null, null, null, null, null, $sql);
					if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
					{
						$obj_agentinfo_multi_factoryss = new AgentInfoMultiFactory($mcobj, $obj_agentinfo_list_factory);
						if($obj_agentinfo_multi_factoryss->initialize() && $obj_agentinfo_multi_factoryss->get())
						{
							$obj_agentinfo_multi = $obj_agentinfo_multi_factoryss->get();
							if(is_array($obj_agentinfo_multi))
							{
								foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
								{
									$obj_agentinfo_multi_item->p_num = $obj_agentinfo_multi_item_chomd->p_num;									

									$obj_agentinfo_multi_item->shared = $shared_arr[$obj_agentinfo_multi_item->type];
									$obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$obj_agentinfo_multi_item->type];
									$obj_agentinfo_multi_item->third_shared = $third_shared_arr[$obj_agentinfo_multi_item->type];
	
									$obj_agentinfo_multi_item->shared_info = $shared_info;
									
									$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();

								}
							}
						}
					}
				}
			}

			if($params['type'] == 3)
			{
				$paid_obj_agentinfo_multi_factory= new AgentInfoMultiFactory($mcobj, null, $params['search_aid']);
				if($paid_obj_agentinfo_multi_factory->initialize() && $paid_obj_agentinfo_multi_factory->get())
				{
					$paid_obj_agentinfo_multi = $paid_obj_agentinfo_multi_factory->get();
					$paid_obj_agentinfo_multi_item = current($paid_obj_agentinfo_multi);
					
					//找到p_num的分成比例
					$sub_shared_arr = array();
					$third_shared_arr = array();
					$shared_info = $paid_obj_agentinfo_multi_item->shared_info;
					$shared_arr = $this->_get_shared($paid_obj_agentinfo_multi_item->p_num, $paid_obj_agentinfo_multi_item->type, $sub_shared_arr ,$shared_info ,$third_shared_arr);


					//验证收益是否已经结清
					$params_tmp['aid'] = $params['search_aid'];
					$params_tmp['end_time'] = strtotime(date("Ymd",time()));
					$params_tmp['start_time'] = Config::START_INCOME_TIME;
					if($paid_obj_agentinfo_multi_item->type != 2 || $this->_have_income($params_tmp))
					{
						$response['sub_code'] = 4; $response['desc'] = __line__; break;
					}
					else
					{
						$paid_obj_agentinfo_multi_item->info = $params['info'].",原上级代理为:". $paid_obj_agentinfo_multi_item->p_aid;
						$paid_obj_agentinfo_multi_item->type = 1;
						$paid_obj_agentinfo_multi_item->p_aid = $paid_obj_agentinfo_multi_item->p_num;

						$paid_obj_agentinfo_multi_item->shared = $shared_arr[$paid_obj_agentinfo_multi_item->type];
						$paid_obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$paid_obj_agentinfo_multi_item->type];
						$paid_obj_agentinfo_multi_item->third_shared = $third_shared_arr[$paid_obj_agentinfo_multi_item->type];
						$paid_obj_agentinfo_multi_item->shared_info = $shared_info;

						$rawsqls[] = $paid_obj_agentinfo_multi_item->getUpdateSql();
					}
				}
				else
				{
					$paid_obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 8; $response['desc'] = __line__; break;
				}
			}

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['code'] = CatConstant::ERROR_UPDATE; $response['desc'] = __line__; break;
			}
			else
			{
				if(isset($obj_agentinfo_multi_factory))
				{
					$obj_agentinfo_multi_factory->writeback();
				}
				if(isset($obj_agentinfo_multi_factorys))
				{
					$obj_agentinfo_multi_factorys->writeback();
				}
				if(isset($obj_agentinfo_multi_factoryss))
				{
					$obj_agentinfo_multi_factoryss->writeback();
				}
				if(isset($paid_obj_agentinfo_multi_factory))
				{
					$paid_obj_agentinfo_multi_factory->writeback();					
				}
	
			}

			$mcobj->del(self::$agent_info.$params['aid'], self::$agent_info.$params['aid']);

			$response['data'] = $data;
		}while(false);

		return $response;
	}

	//给玩家解除绑定 推广员号码  city
	public function bind_out_agent_id($params)
	{
		$response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if( empty($params['aid'])
			|| empty($params['key'])
			|| empty($params['play_uid'])
			|| empty($params['agent_id'])
			|| empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
			}

			$mcobj = BaseFunction::getMC();
			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}
			
			//以后废除
			// if(!empty(Config::TYPE2_OUT_AGENT) )
			// {
			// 	$self_type = $this->_is_self_type($params['aid']);
			// 	if($self_typ == 2)
			// 	{
			// 		$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			// 	}
			// }
			$self_type = $this->_is_self_type($params['aid']);
			if($self_type == 9 || $self_type ==8)  //8 9 验证权限
			{
				//验证权限
				$u_name = '';
				$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
				if (!$power_result)
				{
					$response['sub_code'] = 2;$response['desc'] = __line__;	break;
				}
			}
			else
			{
				if($self_type == 1 || $self_type == 2)  //8 9 验证权限
				{
					$response['sub_code'] = 2;$response['desc'] = __line__;	break;
				}
				
			}

			//判断agent_id是否为一个代理
			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'bind_out_agent_id'
				, 'platform' => 'gfplay'
				, 'uid' => $params['play_uid']
				, 'agent_id' => $params['agent_id']
				);
	
				$randkey = BaseFunction::encryptMD5($data_request);
				$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
				$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
					BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
					BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
					$response['sub_code'] = 3; $response['desc'] = __line__; break;
				}

			

			$response['data'] = $data;
		}while(false);

		return $response;
	}

	//玩家充值记录  city
	public function play_recharge_list_new($params)
	{
		$response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp_id = array();
		$all = false;//全部记录
        $idArray = array();
        $self_type = 0;
        $agentId = '';
		$playerId = '';
		$is_judge_son = true;	

		do {
			if( empty($params['aid'])  //登录者
			|| empty($params['key'])
			|| empty($params['page'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();
			$page = isset($params['page']) ? intval($params['page']) : 1;

            //处理时间
            $params['start_time'] = empty($params['start_time']) ? 0 : strtotime($params['start_time']);
            $params['end_time'] = empty($params['end_time']) ? 0 : strtotime($params['end_time']) + 86400;
			$searchByKeyword = empty($params['keywords']) ? false : $params['keywords'];
			if(!empty($params['keywords']))
			{
				$params['keywords'] = str_replace(' ','',$params['keywords']);//去除 86  18911554496
			}

			$self_type = $this->_is_self_type($params['aid']);
			if(9 == $self_type )//pc版需要加shop  暂时
			{
				if(!empty($params['shop']))
				{
					//验证权限
					$u_name = '';
					$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
					if (!$power_result)
					{
						$response['sub_code'] = 2;$response['desc'] = __line__;	break;
					}
				}
				else
				{
					$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
				}
				$is_judge_son = false;			
			}
            if ($searchByKeyword)
            {
                if (!is_numeric($params['keywords']))
                {
                    $data['list'] = [];
                    $response['data'] = $data;
                    break;
                }

                if (strlen($params['keywords']) == 13)
                {
                    //确认登录者身份和  被查者的关系  必须是一个公会的
                    $this->_getFather($params['keywords'], $idArray, $params['aid']);
                    if (!in_array($params['aid'], $idArray) && $self_type != 9)
                    {
                        $response['sub_code'] = 2; $response['desc'] = __line__; break;
                    }

                    //获取搜索人信息
                    $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null ,$params['keywords']);
                    if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
                    {
                        $obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
                        $obj_agentinfo_multi_item = current($obj_agentinfo_multi);
                        $type = $obj_agentinfo_multi_item->type;
                        $agentId = $params['keywords'];
                        $playerId = '';
                    }
                    else
                    {
                        $obj_agentinfo_multi_factory->clear();
                        $data = ['data_count'=>0,'page_count'=>0,'list'=>[]];$response['data'] = $data;break;
					}
					$judge_agent = false;
                }
                else
                {
                    $agentId = '';
					$playerId = $params['keywords'];
					$judge_agent = true;
                }
            }
            else
            {
                //$self_type = $this->_is_self_type($params['aid']);
                $agentId = $self_type == 9 ? '' : $params['aid'];
            }

            //远程调用play_recharge_list
            $data_request = array(
                'mod' => 'Business'
                , 'act' => 'player_recharge_list_new'
                , 'platform' => 'gfplay'
                , 'page' => $page    //页号
                , 'agent_id' => $agentId    //按照推广员身份查充值记录
                , 'play_id'=>$playerId
                , 'start_time' =>$params['start_time']
                , 'end_time' =>$params['end_time']
            );

			$randkey = BaseFunction::encryptMD5($data_request);
            $url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__; break;
			}

			if($is_judge_son && $judge_agent)
			{
				$sub_code_desc = false;
				foreach($result['data']['list'] as $key => $value)
				{
					if($value['aid'] != $params['aid'])
					{
						if(!$sub_code_desc)
						{
							$sub_code_desc = true;
						}
						unset($result['data']['list'][$key]);
					}

				}
				if(empty($result['data']['list']) && $sub_code_desc )
				{
					$response['sub_code'] = 4; $response['desc'] = __line__; break;
				}
			}

			$response['data'] = $result['data'];
		}while(false);

		return $response;
	}

    //拉入黑名单  city
    public function pull_blacklist($params)
    {
        $response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
        $rawsqls = array();
        $itime = time();
        $data = array();
        $response['data'] = $data;
        $aidArray = array();

        do {
            if(
				empty($params['aid'])
				|| !isset($params['status'])
				|| !isset($params['key'])
				|| !isset($params['agent_id'])
				|| empty($params['uid']))  //玩家
            {
                $response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
            }

            //检查用户登录
            $login_result = $this->_login_check($params);
            if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
            {
                $response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}
			
			$self_type = $this->_is_self_type($params['aid']);
			//副会长不能拉入黑名单
			if($self_type == 2)
			{
				$response['sub_code'] = 2; $response['desc'] = __line__; break;
			}

			$aidArray = $this->_getSon($params['aid']);

            if (!is_array($aidArray))
            {
                $response['sub_code'] = 3; $response['desc'] = __line__; break;
            }
            else
            {
                if (!empty($aidArray) && !in_array($params['agent_id'],$aidArray))
                {
                    $response['sub_code'] = 2; $response['desc'] = __line__; break;
                }
            }

            //调用http拉入黑名单
            $data_request = array(
                'act' => 'pull_blacklist',
                'uid' => $params['uid'],
                'status' => $params['status']
            );

            $result = $this->_sendHttpRequest($data_request, __LINE__);
            if (is_array($result) && !empty($result['sub_code'])) {
            	
            	return $result;break;
            }

        }while(false);

        return $response;
    }

    //黑名单列表  city
    public function blacklist($params)
    {
        $response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
        $rawsqls = array();
        $itime = time();
        $data = array();
        $response['data'] = $data;
        $idArray = array();

        do {
            if(
                empty($params['aid'])
                || empty($params['key'])
                || empty($params['page'])
            )
            {
                $response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
            }

            //检查用户登录
            $login_result = $this->_login_check($params);
            if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
            {
                $response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
            }

            //搜索
            $aidArray = $this->_getSon($params['aid']);

            if (!is_array($aidArray))
            {
                $response['sub_code'] = 2; $response['desc'] = __line__; break;
            }

			$params['keywords'] = empty($params['keywords']) ? 0 : $params['keywords'];
			$params['keywords'] = str_replace(' ','',$params['keywords']);//去除 86  18911554496
            //调用http拉入黑名单
            $data_request = array(
                'act' => 'blacklist',
                'page'     => $params['page'],
				'aidArray' => implode(',',$aidArray),
				'keywords' => $params['keywords'],
			);

            $result = $this->_sendHttpRequest($data_request, __LINE__);
            if (is_array($result) && !empty($result['sub_code'])) {
            	
            	return $result;break;
            }

            $response['data'] = $result->data;
        }while(false);

        return $response;
    }

    //玩家列表  city
    public function play_list($params)
    {
        $response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
        $rawsqls = array();
        $itime = time();
        $data = array();
        $tmp_id = array();
        $all = false;//全部记录
        $idArray = array();
        $self_type = 0;
        $start_time = '';
        $end_time = '';

        do {
            if( empty($params['aid'])  //登录者
                || empty($params['key'])
                || !isset($params['agent_id'])    //被查询者
                || empty($params['page'])
                || !isset($params['play_id'])
                || !isset($params['start_time'])
				|| !isset($params['end_time'])
            )
            {
                $response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
            }

            //检查用户登录
            $login_result = $this->_login_check($params);
            if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
            {
            	$response['sub_code'] = 1;$response['desc'] = __line__;	break;
            }

            $mcobj = BaseFunction::getMC();
            $page = isset($params['page']) ? intval($params['page']) : 1;

            if(!empty($params['play_id']))
            {
                $params['agent_id'] = '';
                $params['start_time'] = '';
                $params['end_time'] = '';
            }

			$self_type = $this->_is_self_type($params['aid']);

			if(9 == $self_type )//pc版需要加shop  暂时
			{
				if(!empty($params['shop']))
				{
					//验证权限
					$u_name = '';
					$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
					if (!$power_result)
					{
						$response['sub_code'] = 2;$response['desc'] = __line__;	break;
					}
				}
				else
				{
					$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
				}			
			}
			
            $agent_id = $params['agent_id'];
            if(empty($params['agent_id']) && empty($params['play_id']))
            {
                if(9 == $self_type)
                {
                    $all = true;//全部记录
                }
                $agent_id = $params['aid'];
            }

            //确认登录者身份和  被查者的关系  必须是一个公会上下级
            if(!empty($params['agent_id']) && $self_type != 9)
            {
                if($params['agent_id'] != $params['aid'])
                {
	                $this->_getFather($params['agent_id'], $idArray, $params['aid']);
	                if (!in_array($params['aid'], $idArray))
	                {
	                    $response['sub_code'] = 2; $response['desc'] = __line__; break;
	                }
                }

            }

            //处理时间
            if(!empty($params['start_time']))
            {
                $start_time = strtotime($params['start_time']);
                if(!empty($params['end_time']))
                {
                    $end_time = strtotime($params['end_time']) + 86400;
                }
                else
                {
                    $end_time = strtotime("now");
                }
            }

            if(!empty($params['end_time']) && empty($params['start_time']))
            {
                //$start_time = strtotime(date("Ymd",strtotime($params['end_time'])));
                $end_time = strtotime($params['end_time']) + 86400;
            }


            //远程调用play_recharge_list
            $data_request = array(
                'mod' => 'Business'
            , 'act' => 'play_list'
            , 'platform' => 'gfplay'
            , 'page' => $page    //页号
            , 'agent_id' => $agent_id    //按照推广员身份查充值记录
            , 'all' =>$all
            , 'play_id' =>$params['play_id']
            , 'start_time' =>$start_time
            , 'end_time' =>$end_time
            );

            $randkey = BaseFunction::encryptMD5($data_request);
            $url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
            if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
                BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
                BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
                $response['sub_code'] = 3; $response['desc'] = __line__; break;
            }

            //不是公司身份  就判断关系   按照玩家id 查找
            if($self_type != 9 && !empty($params['play_id']))
            {
                $agent_id = $result->data->list[0]->agent_id;
                if(empty($agent_id))
                {
                    $response['sub_code'] = 4; $response['desc'] = __line__; break;
                }
                if($agent_id != $params['aid'])  //玩家的绑订的不是我  再接着查找
                {
                    $this->_getFather($agent_id, $idArray, $params['aid']);
                    if (!in_array($params['aid'], $idArray))
                    {
                        $response['sub_code'] = 5; $response['desc'] = __line__; break;
                    }
                }
            }

            $response['data'] = $result->data;
        }while(false);

        return $response;
    }

    //游戏模块调用获取代理信息  city
    public function agent_info_game($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$data = array();
		$tmp = '';

		do {
			if (empty($params['aid'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

 			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				if(is_array($obj_agentinfo_multi))
				{
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
					$obj_agentinfo_multi_item->aid = (string)$obj_agentinfo_multi_item->aid;
					$obj_agentinfo_multi_item->date_init_time = date("Y-m-d",$obj_agentinfo_multi_item->init_time);

					unset($obj_agentinfo_multi_item->p_aid);
					unset($obj_agentinfo_multi_item->status);
					unset($obj_agentinfo_multi_item->audit_eid);
					unset($obj_agentinfo_multi_item->info);
					unset($obj_agentinfo_multi_item->last_amount);
					unset($obj_agentinfo_multi_item->close_down_info);
					unset($obj_agentinfo_multi_item->close_down_time);
					unset($obj_agentinfo_multi_item->init_time);
					unset($obj_agentinfo_multi_item->month);
					unset($obj_agentinfo_multi_item->p_num);
					unset($obj_agentinfo_multi_item->update_time);

					$tmp = $obj_agentinfo_multi_item;
				}
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 2; $response['desc'] = __line__;break;
			}

			$data['agent_info'] = $tmp;
			$response['data'] = $data;
		} while (false);

		return $response;
	}

    //收益统计  city
    public function income_statistics($params)
    {
		$response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
        $rawsqls = array();
        $itime = time();
        $data = array('data_count'=>0,'page_count'=>0,'list'=>[]);
        $response['data'] = $data;
        $idArray = array();
		$sql = '';
		
        do {
			if(empty($params['aid']) || empty($params['key']) || empty($params['page']))
            {
				$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
			}

			if(!empty(Config::ONLY_YESTERDAY))
			{
				if( (!empty($params['start_time']) && strtotime($params['start_time']) >= strtotime(date("Y-m-d"))) 
				||  (!empty($params['end_time']) && strtotime($params['end_time']) >= strtotime(date("Y-m-d")))
				)
				{
					$response['sub_code'] = 7; $response['desc'] = __line__; break;
				}
			}
			
            //检查用户登录
            $login_result = $this->_login_check($params);
            if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
            {
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
            }
			
            $mcobj = BaseFunction::getMC();
			
            $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid']);
            if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
            {
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
                $obj_agentinfo_multi_item = current($obj_agentinfo_multi);
				$type = $obj_agentinfo_multi_item->type;
				$my_percent = $obj_agentinfo_multi_item->shared;
				// $shared = $obj_agentinfo_multi_item->shared;
			    // $sub_shared = $obj_agentinfo_multi_item->sub_shared;
			    // $shared_info = $obj_agentinfo_multi_item->shared_info;
            }
            else
            {
				$obj_agentinfo_multi_factory->clear();
                $response['sub_code'] = 3; $response['desc'] = __line__; break;
			}
			
			if(9 == $type)//pc版需要加shop  暂时
			{
				if(!empty($params['shop']))
				{
					//验证权限
					$u_name = '';
					$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
					if (!$power_result)
					{
						$response['sub_code'] = 2;$response['desc'] = __line__;	break;
					}
				}
				else
				{
					$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
				}
			}
			
            //搜索时间检验
            if (!empty($params['start_time']) && !empty($params['end_time']) && strtotime($params['start_time']) > strtotime($params['end_time']))
            {
				$response['sub_code'] = 4; $response['desc'] = __LINE__; break;
            }
			
            if (!empty($params['start_time']) && !empty($params['end_time']) && ((strtotime($params['end_time']) - strtotime($params['start_time'])) > 60 * 86400) && $type == 8 )
            {
                $response['sub_code'] = 5; $response['desc'] = __LINE__; break;
            }

            //如果有搜索,验证是否有搜索权限
			$params['keywords'] = empty($params['keywords']) ? 0 : $params['keywords'];
			$params['keywords'] = str_replace(' ','',$params['keywords']);//去除 86  18911554496

            if (!empty($params['keywords']))
            {
                $aidArray = $this->_getSon($params['aid']);

                if (!is_array($aidArray))
                {
                    $response['sub_code'] = 2; $response['desc'] = __line__; break;
                }
                if ($type != 9 && !in_array($params['keywords'], $aidArray))
                {
                    $response['sub_code'] = 2; $response['desc'] = __line__; break;
                }
                if ($params['aid'] == $params['keywords'])
                {
                    $response['sub_code'] = 6; $response['desc'] = __line__; break;
                }

                $obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, $params['keywords']);
            }
            else
            {
            	if ($type == 9 && empty($params['p_aid'])) {
               		$sql = "select `aid` from `agent_info` where type = 8";
                	$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, $params['aid'], '', null,null,null,null,$sql);
               	}
               	else
               	{
               		if (empty($params['p_aid'])) {
            			$params['p_aid'] = $params['aid']; 
            		}
                	$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null, '', $params['p_aid']);
               	}
            }

            if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
            {
                $obj_agentinfo_list = $obj_agentinfo_list_factory->get();

                $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, $obj_agentinfo_list_factory);
                if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
                {
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					
                }
                else
                {
                    $obj_agentinfo_multi_factory->clear();
                    $response['sub_code'] = 7; $response['desc'] = __line__; break;
                }
            }
            else
            {
                $obj_agentinfo_list_factory->clear();
            }

			$obj_agentinfo_list = empty($obj_agentinfo_list) ? array() : $obj_agentinfo_list;

			$aidArray = implode(',',$obj_agentinfo_list);
				
			$params['start_time'] = empty($params['start_time']) ? 0 : strtotime($params['start_time']);
			$params['end_time'] = empty($params['end_time']) ? 0 : strtotime($params['end_time']) + 86400;
			///////////
			$result = $this->_income_statistics($params, $type, $aidArray, $obj_agentinfo_multi, $itime ,$my_percent);
			if(isset($result['sub_code']) && $result['sub_code'] != 0)
			{
				return $result;
			}
		
            $response['data'] = $result['data'];

        }while(false);

        return $response;
	}
	
	private function _income_statistics($params, $type=0, $aidArray=array(), $obj_agentinfo_multi=null, $itime, $my_percent=0 )
	{

		$data_list = array();
		$page_count = CatConstant::CNT_PER_PAGE;
		$aid = $type == 9 ? '8600000000000' : $params['aid'];
		//查询收益
		$data_request = array(
			'act' => 'income_statistics',
			'aid'   => $aid,
			'type'  => $type,
			'download_type'  => 255,
			'page'  => $params['page'],
			'keywords' => $params['keywords'],
			'aidArray' => $aidArray,
			'start_time' => $params['start_time'],
			'end_time' => $params['end_time'],
		);

		$result = $this->_sendHttpRequest($data_request, __LINE__, true);
		if (isset($result['sub_code']) && $result['sub_code'] != 0) {
			return $result;
		}

		$data_list = $result['data']['list'];
		$new_data = [];
		$arr = [];

		$operator = $result['data']['operator'];

	
		//循环operator
		$total_recharge = 0;
		$my_total_income = 0;
		$subordinate_income = 0;
		$my_percent = array(0=>$my_percent);
		//$my_percent = array();
		$pay_status = 0;
		$agent_sum_income = 0;
		$total_recharge_subordinate = 0;
		foreach($operator as $operator_item)
		{

			$total_recharge += $operator_item['recharge_direct'] + $operator_item['recharge_subordinate'] + $operator_item['recharge_under_subordinate'];				
			$total_recharge_subordinate +=  $operator_item['recharge_subordinate'] + $operator_item['recharge_under_subordinate'];				
			$my_total_income += ($operator_item['recharge_direct'] * $operator_item['recharge_direct_shared']) + ($operator_item['recharge_subordinate'] * $operator_item['recharge_subordinate_shared']) + ($operator_item['recharge_under_subordinate'] * $operator_item['recharge_under_subordinate_shared']);
			$subordinate_income += ($operator_item['recharge_subordinate'] * $operator_item['recharge_subordinate_shared'])  + ($operator_item['recharge_under_subordinate'] * $operator_item['recharge_under_subordinate_shared']);;	
									
			if(!in_array($operator_item['recharge_direct_shared'],$my_percent))
			{
				$my_percent[] = $operator_item['recharge_direct_shared'];
			}				
			$pay_status += $operator_item['pay_status'];
		}
		$result['data']['pay_status'] = $pay_status;
		
		//循环下属
		foreach($data_list as $data_list_item)
		{
			
			if(isset($data_agent[$data_list_item['agent_id']]))
			{
				$data_agent[$data_list_item['agent_id']]['total_recharge'] += $data_list_item['recharge_direct'] + $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];
				$agent_id_total_income = $data_list_item['recharge_direct'] * $data_list_item['recharge_direct_shared'] + $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];								
				$data_agent[$data_list_item['agent_id']]['total_income'] += $agent_id_total_income;
				$data_agent[$data_list_item['agent_id']]['total_recharge_subordinate'] +=  $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];
				$data_agent[$data_list_item['agent_id']]['subordinate_income'] +=  $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];								
				if(!in_array($data_list_item['recharge_direct_shared'],  $data_agent[$data_list_item['agent_id']]['income_percent']))
				{
					$data_agent[$data_list_item['agent_id']]['income_percent'][] =  $data_list_item['recharge_direct_shared'];
				}
			}
			else
			{
				$data_agent[$data_list_item['agent_id']]['total_recharge'] = $data_list_item['recharge_direct'] + $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];									
				$agent_id_total_income = $data_list_item['recharge_direct'] * $data_list_item['recharge_direct_shared'] + $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];					
				$data_agent[$data_list_item['agent_id']]['total_income'] = $agent_id_total_income;
				$data_agent[$data_list_item['agent_id']]['total_recharge_subordinate'] =  $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];					
				$data_agent[$data_list_item['agent_id']]['subordinate_income'] =  $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];	
				$data_agent[$data_list_item['agent_id']]['income_percent'][] =  $data_list_item['recharge_direct_shared'];
			}

			//计算公司的钱
			if(9 == $type && $data_list_item['agent_id'] != 8600000000000)
			{			
				$agent_sum_income += $agent_id_total_income;			
			}
		}

		if (isset($obj_agentinfo_multi))
		{
			foreach ($obj_agentinfo_multi as $obj_agentinfo_multi_item)
			{
				if (isset($data_agent[$obj_agentinfo_multi_item->aid]) && $obj_agentinfo_multi_item->aid != $params['aid'])
				{
					//总充值
					$obj_agentinfo_multi_item->total_recharge = $data_agent[$obj_agentinfo_multi_item->aid]['total_recharge'];
					
					//分成占比;
					if($obj_agentinfo_multi_item->total_recharge != 0)
					{
						$obj_agentinfo_multi_item->income_percent = implode(',',$data_agent[$obj_agentinfo_multi_item->aid]['income_percent']);
					}
					else
					{
						$obj_agentinfo_multi_item->income_percent = $obj_agentinfo_multi_item->shared;
					}

					//应得收益
					$obj_agentinfo_multi_item->total_income =  $data_agent[$obj_agentinfo_multi_item->aid]['total_income'];

					//下属充值
					$obj_agentinfo_multi_item->total_recharge_subordinate =  $data_agent[$obj_agentinfo_multi_item->aid]['total_recharge_subordinate'];

					//下属贡献
					$obj_agentinfo_multi_item->subordinate_income = $data_agent[$obj_agentinfo_multi_item->aid]['subordinate_income'];
					
					$new_data[] = $obj_agentinfo_multi_item;
				}
			}
		}
		
		if($type == 9 && !empty($operator))
		{
			$my_total_income = $total_recharge - $agent_sum_income;
			$page = isset($params['page']) ? intval($params['page']) : 1;
			$result['data']['data_count'] = count($new_data);
			$new_data = array_slice($new_data, ($page - 1) * $page_count, $page_count);		
			$subordinate_income = $my_total_income;
		}
		$result['data']['total_recharge']= number_format($total_recharge,2);   //总充值
		$result['data']['total_recharge_subordinate']= number_format($total_recharge_subordinate,2);  //下属充值
		$result['data']['subordinate_income'] = number_format($subordinate_income,2);  //下属贡献
		$result['data']['my_total_income'] = number_format($my_total_income,2);  //我的收益
		$result['data']['my_percent'] = implode('或',$my_percent); //我的分成占比
		
		//设置每周几提现
		$week_index = (int)date("w",$itime);
		if(!in_array($week_index,Config::WEEKARRAY))
		{
			$result['data']['pay_status'] = 255;
		}
		else
		{
			if(!empty($result['data']['pay_status']))
			{				
				$result['data']['pay_status'] = (int)$result['data']['pay_status'];
			}
			else
			{
				//设置提现时间
				$time_limit = (int)date("H",$itime);
				if($time_limit < Config::TIME_LIMIT[1] || $time_limit >= Config::TIME_LIMIT[2])
				{
					$result['data']['pay_status'] = 255;						
				}
			}
		}
		$result['data']['list'] = $new_data;
		return $result;

	}
    

    //获取所有代理,用户http端kpi统计  city
    public function get_all_agents($params)
    {
        $response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
        $data = array();

        do {
			$mcobj = BaseFunction::getMC();
			
            $obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj);

            if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get()) {
                $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, $obj_agentinfo_list_factory);
                if ($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get()) {
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
                    $data['list'] = $obj_agentinfo_multi;
                } else {
                    $obj_agentinfo_multi_factory->clear();
                    $response['sub_code'] = 2;
                    $response['desc'] = __line__;
                    break;
                }
            }
			
            $response['data'] = $data;

        } while (false);

        return $response;
    }

	//修改下级信息 姓名 微信号 city
	public function chmod_agent_info($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['children_aid'])
			|| 	!isset($params['name'])
			|| 	!isset($params['wx_id'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;break;
			}
			$mcobj = BaseFunction::getMC();

			$obj_agentinfo_multi_factory =  new AgentInfoMultiFactory($mcobj,null,$params['children_aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item  = current($obj_agentinfo_multi);
				if($obj_agentinfo_multi_item->p_aid == $params['aid'])
				{
					if(!empty($params['name']))
					{
						$obj_agentinfo_multi_item->name = $params['name'];
					}
					if(!empty($params['wx_id']))
					{
						$obj_agentinfo_multi_item->wx_id = trim($params['wx_id']);
					}
					$obj_agentinfo_multi_item->update_time = $itime;
					$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
				}
				else
				{
					$response['sub_code'] = 2; $response['desc'] = __line__;break;
				}
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 3;$response['desc'] = __line__;break;
			}

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 4; $response['desc'] = __line__;break;
			}
			if(isset($obj_agentinfo_multi_factory))
			{
				$obj_agentinfo_multi_factory->writeback();
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//指定玩家消耗情况  city  shop#
	public function find_play_recharge($params)
    {
        $response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
        $rawsqls = array();
        $itime = time();
        $data = array();
        $response['data'] = $data;
        $idArray = array();

        do {
            if(
                empty($params['aid'])
                || empty($params['key'])
                || empty($params['page'])
                || empty($params['play_uid'])
                || empty($params['shop'])
            )
            {
                $response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
			}
			$page = isset($params['page']) ? intval($params['page']) : 1;

            //检查用户登录
            $login_result = $this->_login_check($params);
            if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
            {
                $response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}
			
			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

            $data_request = array(
                'mod'=> 'Business',
                'act' => 'find_play_recharge',
                'platform' => 'gfplay',
                'page'     => $params['page'],
				'uid' => $params['play_uid'],
			);

            $randkey = BaseFunction::encryptMD5($data_request);
            $url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
            if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
                BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
                BaseFunction::logger($this->log, "【data_request】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
                $response['sub_code'] = 3; $response['desc'] = __line__; break;
            }

            $response['data'] = $result->data;
        }while(false);

        return $response;
	}
	
	//指定玩家播放码 city  shop#
	public function find_play_video($params)
	{
		$response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$response['data'] = $data;
		$idArray = array();

		do {
			if(
				empty($params['aid'])
				|| empty($params['key'])
				|| empty($params['page'])
				|| empty($params['play_uid'])
				|| empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
			}
			$page = isset($params['page']) ? intval($params['page']) : 1;

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}
			
			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			$data_request = array(
				'mod'=> 'Business',
				'act' => 'find_play_video',
				'platform' => 'gfplay',
				'page'     => $params['page'],
				'uid' => $params['play_uid'],
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
			if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__; break;
			}

			$response['data'] = $result->data;
		}while(false);

		return $response;
	}
	
    public function change_user_agent($params)
    {
    	$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
        $data = array();
        $response['data'] = $data;

        do {
            if (
            	empty($params['key'])
                || empty($params['aid_before_change'])
                || empty($params['aid_after_change'])
            ) {
                $response['code'] = CatConstant::ERROR;$response['desc'] = __LINE__;break;
            }
	        $data_request = array(
	            'mod' => 'Business',
	            'act' => 'change_user_agent',
	            'platform' => 'gfplay',
	            'aid_before_change' => $params['aid_before_change'],
	            'aid_after_change' => $params['aid_after_change']
	        );

	        $randkey = BaseFunction::encryptMD5($data_request);
	        $url = Config::GET_USER."?randkey=".$randkey."&c_version=0.0.1";
	        $result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
	        if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
	            //user端不成功标记下
	            $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid_after_change']);
	            if ($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get()) {
	                $obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
	                $obj_agentinfo_multi_item = current($obj_agentinfo_multi);
	                $obj_agentinfo_multi_item->info = '更改用户agent_id失败';
	                $rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
	            }

	            if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
	            {
	                $response['sub_code'] = 3; $response['desc'] = __line__; break;
	            }

	            BaseFunction::logger($this->log, "【data_request】:\n".var_export($result, true)."\n".__LINE__."\n");
	            $response['sub_code'] = 3;$response['desc'] = __line__;break;
	        }

	        $response['data'] = $data;

	    }while(false);

	    return $response;
	}
	
	//添加客服经理  city
	public function decrypt_add_agent($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = array();
		$p_num = 0;
		$self_type = 0;
		$children_num = 0;

		do {
			if (empty($params['encrypt_aid'])
			|| 	empty($params['aid'])
			|| 	empty($params['time'])
			|| 	empty($params['children_aid'])
			|| 	empty($params['name'])
			|| 	empty($params['wx_id'])
			|| 	!isset($params['num'])
			|| 	empty($params['provinces'])
			|| 	empty($params['city'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}


			$mcobj = BaseFunction::getMC();

			//验证是否来自链接的请求
			$aid = BaseFunction::decryptRandAuth($params['time'],$params['encrypt_aid']);
			if($aid != $params['aid'])
			{
				$response['sub_code'] = 6;$response['desc'] = __line__;break;
			}

			//查询推荐码
			$obj_agentinfo_multi_factory =  new AgentInfoMultiFactory($mcobj,null,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item  = current($obj_agentinfo_multi);
				$p_num = $obj_agentinfo_multi_item->p_num;
				$type = $obj_agentinfo_multi_item->type;
				$p_aid = $params['aid'];

				//获取分成比例
				$sub_shared_arr = array();
				$third_shared_arr = array();
				$shared_info = $obj_agentinfo_multi_item->shared_info;
				$shared_arr = $this->_get_shared($p_num, $type, $sub_shared_arr ,$shared_info ,$third_shared_arr );

			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 6;$response['desc'] = __line__;break;
			}

			//查询下级数量
			$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,'',$params['aid']);
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_list = $obj_agentinfo_list_factory->get();
				$children_num = count($obj_agentinfo_list);
			}
			else
			{
				$obj_agentinfo_list_factory->clear();
			}

			if($type != 9 )  //公司身份添加人员  不需要短信验证
			{
				$data_request = array(
				'mod' => 'Business'
				, 'act' => 'login_num_check'
				, 'type' => '2'    //验证类型此type非彼type  密码验证用1   添加人员用2
				, 'platform' => 'tocar'
				, 'aid' => $params['children_aid']
				, 'num' => $params['num']
				, 'key' => Config::API_KEY
				);

				$randkey = BaseFunction::encryptMD5($data_request);
				$url = Config::USER_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
				$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
				{
					BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
					BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
					$response['sub_code'] = 4; $response['desc'] = __line__; break;
				}
			}

			//总代不能新增  客服
			if($type == 9)
			{
				$new_type = 8;
			}
			else if($type == 8)//不能超过10个 会长
			{
				$new_type = 1;
			}
			else if($type == 1 && $children_num < Config::TYPE_2)//不能找过9个副会长
			{
				$new_type = 2;
			}
			else
			{
				$response['sub_code'] = 5;$response['desc'] = __line__;break;
			}

			//新增人员
			$obj_agentinfo = new AgentInfo();

			if(isset($obj_agentinfo))
			{
				$obj_agentinfo->aid = $params['children_aid'];
				$obj_agentinfo->name = $params['name'];
				$obj_agentinfo->wx_id = trim($params['wx_id']);
				$obj_agentinfo->p_aid = $p_aid;
				$obj_agentinfo->type = $new_type;
				$obj_agentinfo->status = 2;//通过状态

				$obj_agentinfo->provinces = $params['provinces'];//通过状态
				$obj_agentinfo->city = $params['city'];//通过状态
				$obj_agentinfo->audit_eid = $params['aid'];
				$obj_agentinfo->last_amount = 0;
				if(8 == $new_type)
				{
					if($type == 9 )
					{
						$obj_agentinfo->p_num = $params['children_aid'];
					}
					else
					{
						$response['sub_code'] = 5; $response['desc'] = __line__;return $response;
					}
				}
				else
				{
					$obj_agentinfo->p_num = $p_num;
				}
				$obj_agentinfo->init_time = $itime;
				$obj_agentinfo->update_time = $itime;
				$obj_agentinfo->month = date("Ym",$itime);

				$obj_agentinfo->shared = $shared_arr[$new_type];
				$obj_agentinfo->sub_shared = $sub_shared_arr[$new_type];
				$obj_agentinfo->third_shared =  $third_shared_arr[$new_type];
				$obj_agentinfo->shared_info = $shared_info;

				$rawsqls[] = $obj_agentinfo->getInsertSql();
			}

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//收益提取  city
	public function extract_income($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = array();
		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['start_time'])
			|| 	empty($params['end_time'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			$mcobj = BaseFunction::getMC();
			//防止连续多次点击
			if(!$mcobj->setKeep(self::$aidlock.$params['aid'], 1, $timeout=15))
			{
				$response['sub_code'] = 19; $response['desc'] = __LINE__; break;
			}

			//设置提现时间
			$time_limit = (int)date("H",$itime);
			if($time_limit < Config::TIME_LIMIT[1] || $time_limit >= Config::TIME_LIMIT[2])
			{
				$response['sub_code'] = 25; $response['desc'] = __LINE__; break;
			}

			//设置每周几提现
			$week_index = (int)date("w",$itime);
			if(!in_array($week_index,Config::WEEKARRAY))
			{
				$response['sub_code'] = 26; $response['desc'] = __LINE__; break;
			}

			//设置每天提现次数
			$sum_income_time = $this->_sum_income_time($params['aid']);
			if(empty(Config::SUM_INCOME_TIME) || $sum_income_time >= Config::SUM_INCOME_TIME)//Config::SUM_INCOME_TIME==0  表示不允许提现
			{
				$response['sub_code'] = 20; $response['desc'] = __LINE__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//////////////////////////
			//计算时间
			if(true)
			{				
				//检验时间不能是当天
				if( (!empty($params['start_time']) && strtotime($params['start_time']) >= strtotime(date("Y-m-d"))) 
				||  (!empty($params['end_time']) && strtotime($params['end_time']) >= strtotime(date("Y-m-d")))
				)
				{
					$response['sub_code'] = 2; $response['desc'] = __line__; break;
				}

				if(!empty(Config::START_INCOME_TIME) && (strtotime($params['start_time']) < Config::START_INCOME_TIME) )
				{
					$response['sub_code'] = 24; $response['desc'] = __line__; break;					
				}
				
				
				//搜索时间检验是否在可查询范围内
				if (!empty($params['start_time']) && !empty($params['end_time']) && strtotime($params['start_time']) > strtotime($params['end_time']))
				{
					$response['sub_code'] = 3; $response['desc'] = __LINE__; break;
				}
				
				// if (!empty($params['start_time']) && !empty($params['end_time']) && ((strtotime($params['end_time']) - strtotime($params['start_time'])) > 90 * 86400))
				// {
				// 	$response['sub_code'] = 4; $response['desc'] = __LINE__; break;
				// }			
				
				$params['start_time'] = strtotime($params['start_time']);
				$params['end_time'] = strtotime($params['end_time']) + 86400;
		
				//日期是否连续 提取
				if(!$this-> _is_continued($params))
				{
					$response['sub_code'] = 21; $response['desc'] = __LINE__; break;
				}
			}
			
			//1.获取玩家openid
			$openid_result = $this->_get_aid_openid($params);
			if($openid_result['code'] !=0 ||  $openid_result['sub_code'] !=0 )
			{
				$response['sub_code'] = 7;$response['desc'] = __LINE__;	break;
			}
			$params['openid'] = $openid_result['data'];
			
			//////////////////////
			//2.通过时间参数  计算是否在可提取的时间范围内  计算  可提取的金额
			$params['income'] = 0;
			$income_result = $this->_self_income_statistics($params);
			if ($income_result['code'] != 0 || $income_result['sub_code'] != 0)
			{
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($income_result, true) . "\n" . __LINE__ . "\n");
				if($income_result['sub_code'] == 4)
				{
					$response['sub_code'] = 18;$response['desc'] = __line__;break;				
				}
				else
				{
					$response['sub_code'] = 5;$response['desc'] = __line__;break;
				}
			}

			if($income_result['pay_status'] > 0)//该日期是否已经提现
			{
				$response['sub_code'] = 6;$response['desc'] = __line__;break;
			}
			else
			{
				$params['income'] = intval($income_result['data']*100);
				if($params['income'] < 100)
				{
					$response['sub_code'] = 14;$response['desc'] = __line__;break;
				}
			}

			///////////////////////////////////
			//获取appid等数据
			$user_conf = $this->_get_user_conf();
			if(!empty($user_conf))
			{
				$params['wx_appid'] = $user_conf['data']['wx_appid'];
				$params['wx_mchid'] = $user_conf['data']['wx_mchid'];
				$params['wx_key'] = $user_conf['data']['wx_key'];
			}
			else
			{
				$response['code'] = 23; $response['desc'] = __line__; break;
			}
			$params['itime'] = $itime;
			$params['desc'] = Config::NAME.$params['aid']."提现".$income_result['data']."元";
			//////////////////////////////////
			//3.发起提现请求  构造请求数据
			$extract_reslut = $this->_extract_income($params);
			if ($extract_reslut['code'] != 0 || $extract_reslut['sub_code'] != 0)
			{
				$response['sub_code'] = $extract_reslut['sub_code']; $response['desc'] = __LINE__;	break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	private function _extract_income($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = $params['itime'];
		$data = array();
		$tmp = array();
		do {
			//获取订单号
			$mcobj = BaseFunction::getMC();
			$obj_income = new Income();
			$obj_income->uid = $params['aid'];
			$obj_income->total_fee = number_format(($params['income']/100),2,".","");
			$obj_income->start_time = $params['start_time'];
			$obj_income->end_time = $params['end_time'];
			$obj_income->status = 0;
			$obj_income->notify_status = 0;
			$obj_income->third_party_type = 4;
			$obj_income->init_time = $itime;
			$obj_income->update_time = $itime;
			
			$rawsqls[] = $obj_income->getInsertSql();

			$error = '';
			if ($rawsqls && !($result = BaseFunction::execute_sql_backend($rawsqls,$error)))
			{
				$response['sub_code'] = 8; $response['desc'] = __line__; break;
			}
			elseif($rawsqls && $result[0]['insert_id'])
			{
				$params['partner_trade_no'] = Config::TRADE_NO_KEY.$result[0]['insert_id'];
				$rawsqls = array();
				$delete_id = $result[0]['insert_id'];
			}
			else
			{
				$response['code'] = 9; $response['desc'] = __line__; break;
			}
			////////////////////////////////////

			$params['pay_status'] = 1;
			if($this->_update_income_statistics($params))
			{				
				//4.提现
				$obj_wxpay = new WxPay($params);
				//构造xml
				$xml = $obj_wxpay->ToXml();
				//发送数据
				$result_xml = $obj_wxpay->postXmlCurl($xml, true, $second = 30);
				$result_arr = $obj_wxpay->FromXml($result_xml);
				if(isset($result_arr) && $result_arr['return_code'] == 'SUCCESS' && $result_arr['result_code'] == 'SUCCESS')
				{
					//5.写提现记录
					//修改Income
					$result_arr['partner_trade_no'] = intval(substr($result_arr['partner_trade_no'],strlen(Config::TRADE_NO_KEY)));
					$obj_income_multi_factory =  new IncomeMultiFactory($mcobj, null, $result_arr['partner_trade_no']);
					if($obj_income_multi_factory->initialize() && $obj_income_multi_factory->get())
					{
						$obj_income_multi = $obj_income_multi_factory->get();
						$obj_income_multi_item = current($obj_income_multi);
						if(isset($obj_income_multi_item))
						{
							$obj_income_multi_item->status = 1;
							$obj_income_multi_item->notify_status = 1;
							$obj_income_multi_item->third_trade_no = $result_arr['payment_no'];
							$obj_income_multi_item->update_time = strtotime($result_arr['payment_time']);

							$rawsqls[] = $obj_income_multi_item->getUpdateSql();
						}
					}
					else
					{
						$obj_income_multi_factory->clear();
					}
					
					if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
					{
						BaseFunction::logger($this->log, "【严重警告rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
						$response['sub_code'] = 12; $response['desc'] = __line__;break;
					}

				}
				else
				{
					//提现失败  修改pay_status
					if(isset($result_arr['err_code']) && isset($result_arr['err_code_des']))
					{
						if($result_arr['err_code'] == 'SYSTEMERROR')
						{
							$notify_status = 3;//SYSTEMERROR
						}
						else
						{
							$params['pay_status'] = 0;
							$notify_status = 2;//提现失败
							$this->_update_income_statistics($params);
						}
						$params['partner_trade_no'] = substr($params['partner_trade_no'],strlen(Config::TRADE_NO_KEY));
						$obj_income_multi_factory =  new IncomeMultiFactory($mcobj, null, $params['partner_trade_no']);
						if($obj_income_multi_factory->initialize() && $obj_income_multi_factory->get())
						{
							$obj_income_multi = $obj_income_multi_factory->get();
							$obj_income_multi_item = current($obj_income_multi);
							if(isset($obj_income_multi_item))
							{
								$obj_income_multi_item->status = 2;
								$obj_income_multi_item->notify_status = $notify_status;
								
								$obj_income_multi_item->third_trade_no = $result_arr['err_code'];
								$obj_income_multi_item->third_notify_info = $result_arr['err_code_des'];
								$obj_income_multi_item->update_time = $itime;

								$rawsqls[] = $obj_income_multi_item->getUpdateSql();
							}
						}
						if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
						{
							BaseFunction::logger($this->log, "【严重警告rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
							$response['sub_code'] = 22; $response['desc'] = __line__;break;
						}

					}
					
					BaseFunction::logger($this->log, "【严重警告result_arr】:\n" . var_export($result_arr, true) . "\n" . __LINE__ . "\n");
					$response['sub_code'] = 10; $response['desc'] = __line__;
					if($result_arr['err_code'] == 'V2_ACCOUNT_SIMPLE_BAN')
					{
						$response['sub_code'] = 27; $response['desc'] = __line__;
					}

				}

				if(isset($obj_income_multi_factory))
				{
					$obj_income_multi_factory->writeback();
				}
			}
			else
			{
				BaseFunction::logger($this->log, "【_update_income_statistics】:\n" . var_export("更新失败", true) . "\n" . __LINE__ . "\n");
				$this->_delete_income($delete_id);
				$response['sub_code'] = 13; $response['desc'] = __line__;break;
			}

		}while (false);
		
		return $response;
	}

	//收益提取列表  city
	public function extract_income_list($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = array();
		$aid = 255;
		$data_count = 0;
		$page_count = CatConstant::CNT_PER_PAGE;
		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	!isset($params['search_aid'])
			|| 	!isset($params['start_time'])
			|| 	!isset($params['end_time'])
			|| 	!isset($params['page'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			$mcobj = BaseFunction::getMC();
			$page = isset($params['page']) ? intval($params['page']) : 1;


			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//搜索时间检验是否在可查询范围内
			if (!empty($params['start_time']) && !empty($params['end_time']) && strtotime($params['start_time']) > strtotime($params['end_time']))
			{
				$response['sub_code'] = 2; $response['desc'] = __LINE__; break;
			}
			
			if(empty($params['search_aid']) )
			{
				if(9 == $this->_is_self_type($params['aid']))
				{
					$sql =  "select `out_trade_no` from `income` where 1";
					
				}
				else
				{
					$sql =  "select `out_trade_no` from `income` where uid = " .$params['aid'];
				}
			}
			else
			{
				if($params['search_aid'] == $params['aid'])
				{
					$response['sub_code'] = 3; $response['desc'] = __LINE__; break;
				}
				else
				{
					if(9 != $this->_is_self_type($params['aid']))
					{
						//验证是否为上下级关系
						$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['search_aid']);
						if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
						{
							$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
							$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
							
							if($obj_agentinfo_multi_item->p_aid != $params['aid'])
							{
								$this->_getFather($params['search_aid'], $idArray, $params['aid']);
								if (!in_array($params['aid'], $idArray))
								{
									$response['sub_code'] = 4; $response['desc'] = __line__; break;
								}
							}
						}
						else
						{
							$obj_agentinfo_multi_factory->clear();
							$response['sub_code'] = 5; $response['desc'] = __line__; break;
						}
					}
					
					$sql =  "select `out_trade_no` from `income` where uid = " .$params['search_aid'];

				}
			}

			if(!empty($params['start_time']))
			{
				$sql .= " and init_time >= " . strtotime($params['start_time']);
			}
			if(!empty($params['end_time']))
			{
				$sql .= " and init_time < " . (strtotime($params['end_time']) + 86400);
			}		
			$sql .=  " order by init_time desc";

			$obj_income_list_factory =  new IncomeListFactory($mcobj, null, null, $sql);
			if($obj_income_list_factory->initialize() && $obj_income_list_factory->get())
			{
				$obj_income_list = $obj_income_list_factory->get();
				
				$data_count = count($obj_income_list);
				$obj_income_list_page_arr = array_slice($obj_income_list, ($page - 1) * $page_count, $page_count);
				$obj_income_list_factory = new IncomeListFactory($mcobj,null,implode(',',$obj_income_list_page_arr));
				if($obj_income_list_factory->initialize() && $obj_income_list_factory->get())
				{
					$obj_income_multi_factory = new IncomeMultiFactory($mcobj, $obj_income_list_factory);
					if($obj_income_multi_factory->initialize() && $obj_income_multi_factory->get())
					{
						$obj_income_multi = $obj_income_multi_factory->get();
						$obj_income_multi = array_values($obj_income_multi);
						usort($obj_income_multi,array('bigcat\controller\Business','cmp_agent_init_time'));
						foreach($obj_income_multi as $obj_income_multi_item)
						{
							unset($obj_income_multi_item->expire_time);
							unset($obj_income_multi_item->third_notify_info);
							$obj_income_multi_item->start_time = date("Y-m-d",$obj_income_multi_item->start_time);
							$obj_income_multi_item->end_time = date("Y-m-d",$obj_income_multi_item->end_time-86400);
							$obj_income_multi_item->init_time = date("Y-m-d H:i:s",$obj_income_multi_item->init_time);
							$obj_income_multi_item->update_time = date("Y-m-d H:i:s",$obj_income_multi_item->update_time);
							$data['list'][] = $obj_income_multi_item;
						}

					}
					else
					{
						$obj_income_multi_factory->clear();
					}
				}
			}
			else
			{
				$obj_income_list_factory->clear();
			}
			$data['data_count'] = $data_count;
            $data['page_count'] = $page_count;
			$response['data'] = $data;
		} while (false);

		return $response;
	}



	//添加充值卡密码
	public function add_rechargeable_card($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['gid'])
			|| 	$params['gid'] > 9
			|| 	$params['gid'] < 1
			|| 	empty($params['password'])
			|| 	empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			//新增记录	
			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'add_rechargeable_card'
				, 'platform' => 'gfplay'
				,'gid' => $params['gid']
				, 'password' => $params['password']
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __LINE__; break;
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//查询充值卡密码
	public function show_rechargeable_card($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (!isset($params['gid'])
			|| 	!isset($params['state'])
			|| 	empty($params['page'])
			|| 	empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			//新增记录	
			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'show_rechargeable_card'
				, 'platform' => 'gfplay'
				,'gid' => $params['gid']
				, 'state' => $params['state']
				, 'page' => $params['page']
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __LINE__; break;
			}

			$response['data'] = $result['data'];
		} while (false);

		return $response;
	}

	//更改发货信息
	public function change_delivery_information($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['gl_id'])
			|| 	empty($params['remark'])
			|| 	empty($params['state'])
			|| 	empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			//新增记录	
			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'change_delivery_information'
				, 'platform' => 'gfplay'
				,'gl_id' => $params['gl_id']
				, 'state' => $params['state']
				, 'remark' => $params['remark']
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __LINE__; break;
			}

			$response['data'] = $result['data'];
		} while (false);

		return $response;
	}

	//查看礼物兑换记录
	public function show_gift_exchange_log($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['page'])
			|| 	!isset($params['state'])
			|| 	!isset($params['uid'])
			|| 	(!empty($params['state']) && !in_array($params['state'],array(1,2,3)) )
			|| 	empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			//新增记录	
			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'show_gift_exchange_log'
				, 'platform' => 'gfplay'
				, 'state' => $params['state']
				, 'page' => $params['page']
				, 'uid' => $params['uid']
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __LINE__; break;
			}

			$response['data'] = $result['data'];
		} while (false);

		return $response;
	}


	//查看礼物兑换记录
	public function delete_rechargeable_card($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['rc_id'])
			|| 	empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['shop'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			//新增记录	
			$data_request = array(
				'mod' => 'Business'
				, 'act' => 'delete_rechargeable_card'
				, 'platform' => 'gfplay'
				, 'rc_id' => $params['rc_id']
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __LINE__; break;
			}

			$response['data'] = $result['data'];
		} while (false);

		return $response;
	}
	
	//收益下载
	public function income_statistics_excel($params)
	{
		$response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array('data_count'=>0,'page_count'=>0,'list'=>[]);
		$response['data'] = $data;
		$idArray = array();
		$sql = '';
		
		do {
			if(empty($params['aid']) || empty($params['key']) )
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __LINE__; break;
			}

			if(!empty(Config::ONLY_YESTERDAY))
			{
				if( (!empty($params['start_time']) && strtotime($params['start_time']) >= strtotime(date("Y-m-d"))) 
				||  (!empty($params['end_time']) && strtotime($params['end_time']) >= strtotime(date("Y-m-d")))
				)
				{
					$response['sub_code'] = 7; $response['desc'] = __line__; break;
				}
			}
			
			$start_time = $params['start_time'];
			$end_time = $params['end_time'];
			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}
			
			$mcobj = BaseFunction::getMC();
			
			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

				$shared = $obj_agentinfo_multi_item->shared;
				$sub_shared = $obj_agentinfo_multi_item->sub_shared;
				$type = $obj_agentinfo_multi_item->type;
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 3; $response['desc'] = __line__; break;
			}
			//$obj_agentinfo_multi_factory->clear();
	
			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			
			//搜索时间检验
			if (!empty($params['start_time']) && !empty($params['end_time']) && strtotime($params['start_time']) > strtotime($params['end_time']))
			{
				$response['sub_code'] = 4; $response['desc'] = __LINE__; break;
			}
			
			if (!empty($params['start_time']) && !empty($params['end_time']) && ((strtotime($params['end_time']) - strtotime($params['start_time'])) > 60 * 86400))
			{
				$response['sub_code'] = 5; $response['desc'] = __LINE__; break;
			}

		
			$sql = "select `aid` from `agent_info` where type != 9";
			$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null,null, null,null,null,null,$sql);
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_list = $obj_agentinfo_list_factory->get();

				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, $obj_agentinfo_list_factory);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 7; $response['desc'] = __line__; break;
				}
			}
			else
			{
				$obj_agentinfo_list_factory->clear();
				$response['sub_code'] = 7; $response['desc'] = __line__; break;
			}
			
			$aidArray = implode(',',$obj_agentinfo_list);
			
			$params['start_time'] = empty($params['start_time']) ? 0 : strtotime($params['start_time']);
			$params['end_time'] = empty($params['end_time']) ? 0 : strtotime($params['end_time']) + 86400;
			$aid = $type == 9 ? '8600000000000' : $params['aid'];
			//查询收益
			$data_request = array(
				'mod' => 'Business',
				 'platform' => 'gfplay',
				'act' => 'income_statistics',
				'aid'  => $aid,
				//'type' => 255,
				'type' => $type,
				'download_type' => 255,
				'page' => 1,
				'aidArray' => $aidArray,
				'start_time' => $params['start_time'],
				'end_time' => $params['end_time'],
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				break;
			}

			$data_list = $result['data']['list'];
            $new_data = [];
            $arr = [];

			$operator = $result['data']['operator'];
	
			//循环operator
			$total_recharge = 0;
			$my_total_income = 0;
			$subordinate_income = 0;
			$my_percent = array(0=>$my_percent);
			//$my_percent = array();
			$pay_status = 0;
			$agent_sum_income = 0;
			$total_recharge_subordinate = 0;
			foreach($operator as $operator_item)
			{

				$total_recharge += $operator_item['recharge_direct'] + $operator_item['recharge_subordinate'] + $operator_item['recharge_under_subordinate'];				
				$total_recharge_subordinate +=  $operator_item['recharge_subordinate'] + $operator_item['recharge_under_subordinate'];				
				$my_total_income += ($operator_item['recharge_direct'] * $operator_item['recharge_direct_shared']) + ($operator_item['recharge_subordinate'] * $operator_item['recharge_subordinate_shared']) + ($operator_item['recharge_under_subordinate'] * $operator_item['recharge_under_subordinate_shared']);
				$subordinate_income += ($operator_item['recharge_subordinate'] * $operator_item['recharge_subordinate_shared'])  + ($operator_item['recharge_under_subordinate'] * $operator_item['recharge_under_subordinate_shared']);;	
										
				if(!in_array($operator_item['recharge_direct_shared'],$my_percent))
				{
					$my_percent[] = $operator_item['recharge_direct_shared'];
				}				
				$pay_status += $operator_item['pay_status'];
			}
			$result['data']['pay_status'] = $pay_status;
			
		//循环下属
			foreach($data_list as $data_list_item)
			{
				
				if(isset($data_agent[$data_list_item['agent_id']]))
				{
					$data_agent[$data_list_item['agent_id']]['total_recharge'] += $data_list_item['recharge_direct'] + $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];
					$agent_id_total_income = $data_list_item['recharge_direct'] * $data_list_item['recharge_direct_shared'] + $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];								
					$data_agent[$data_list_item['agent_id']]['total_income'] += $agent_id_total_income;
					$data_agent[$data_list_item['agent_id']]['total_recharge_subordinate'] +=  $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];
					$data_agent[$data_list_item['agent_id']]['subordinate_income'] +=  $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];								
					if(!in_array($data_list_item['recharge_direct_shared'],  $data_agent[$data_list_item['agent_id']]['income_percent']))
					{
						$data_agent[$data_list_item['agent_id']]['income_percent'][] =  $data_list_item['recharge_direct_shared'];
					}
				}
				else
				{
					$data_agent[$data_list_item['agent_id']]['total_recharge'] = $data_list_item['recharge_direct'] + $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];									
					$agent_id_total_income = $data_list_item['recharge_direct'] * $data_list_item['recharge_direct_shared'] + $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];					
					$data_agent[$data_list_item['agent_id']]['total_income'] = $agent_id_total_income;
					$data_agent[$data_list_item['agent_id']]['total_recharge_subordinate'] =  $data_list_item['recharge_subordinate'] + $data_list_item['recharge_under_subordinate'];					
					$data_agent[$data_list_item['agent_id']]['subordinate_income'] =  $data_list_item['recharge_subordinate'] * $data_list_item['recharge_subordinate_shared'] + $data_list_item['recharge_under_subordinate'] * $data_list_item['recharge_under_subordinate_shared'];	
					$data_agent[$data_list_item['agent_id']]['income_percent'][] =  $data_list_item['recharge_direct_shared'];
				}

				//计算公司的钱
				if(9 == $type && $data_list_item['agent_id'] != 8600000000000)
				{			
					$agent_sum_income += $agent_id_total_income;			
				}
			}

			if (isset($obj_agentinfo_multi))
			{
				foreach ($obj_agentinfo_multi as $obj_agentinfo_multi_item)
				{
					if (isset($data_agent[$obj_agentinfo_multi_item->aid]) && $obj_agentinfo_multi_item->aid != $params['aid'])
					{
						//总充值
						$obj_agentinfo_multi_item->total_recharge = $data_agent[$obj_agentinfo_multi_item->aid]['total_recharge'];
						
						//分成占比;
						if($obj_agentinfo_multi_item->total_recharge != 0)
						{
							$obj_agentinfo_multi_item->income_percent = implode(',',$data_agent[$obj_agentinfo_multi_item->aid]['income_percent']);
						}
						else
						{
							$obj_agentinfo_multi_item->income_percent = $obj_agentinfo_multi_item->shared;
						}

						//应得收益
						$obj_agentinfo_multi_item->total_income =  $data_agent[$obj_agentinfo_multi_item->aid]['total_income'];

						//下属充值
						//$obj_agentinfo_multi_item->total_recharge_subordinate =  $data_agent[$obj_agentinfo_multi_item->aid]['total_recharge_subordinate'];

						//下属贡献
						$obj_agentinfo_multi_item->subordinate_income = $data_agent[$obj_agentinfo_multi_item->aid]['subordinate_income'];

						if($obj_agentinfo_multi_item->type == 8)
						{
							$obj_agentinfo_multi_item->type = '推广员';
						}
						elseif($obj_agentinfo_multi_item->type == 1)
						{
							$obj_agentinfo_multi_item->type = '会长';
						}
						elseif($obj_agentinfo_multi_item->type == 2)
						{
							$obj_agentinfo_multi_item->type = '副会长';
						}
						
						unset($obj_agentinfo_multi_item->wx_id);
						unset($obj_agentinfo_multi_item->provinces);
						unset($obj_agentinfo_multi_item->city);
						unset($obj_agentinfo_multi_item->p_aid);
						unset($obj_agentinfo_multi_item->opend_status);
						unset($obj_agentinfo_multi_item->status);
						unset($obj_agentinfo_multi_item->audit_eid);
						unset($obj_agentinfo_multi_item->info);
						unset($obj_agentinfo_multi_item->last_amount);
						unset($obj_agentinfo_multi_item->close_down_info);
						unset($obj_agentinfo_multi_item->close_down_time);
						unset($obj_agentinfo_multi_item->init_time);
						unset($obj_agentinfo_multi_item->update_time);
						unset($obj_agentinfo_multi_item->month);
						unset($obj_agentinfo_multi_item->p_num);
						unset($obj_agentinfo_multi_item->total_recharge_subordinate);
						unset($obj_agentinfo_multi_item->date_init_time);
						unset($obj_agentinfo_multi_item->shared);
						unset($obj_agentinfo_multi_item->sub_shared);
						unset($obj_agentinfo_multi_item->shared_info);
						unset($obj_agentinfo_multi_item->third_shared);
						
						$new_data[] = $obj_agentinfo_multi_item;
					}
				}
			}
					
			if($type == 9)
			{
							
				$my_total_income = $total_recharge - $agent_sum_income;
				$subordinate_income = $my_total_income;
				//总公司身份
				array_unshift($new_data,array('8600000000000','总公司boss','公司',number_format($total_recharge,2),implode(',',$my_percent), number_format($my_total_income,2), number_format($subordinate_income,2)));
				
			}

			$result['income']['收益明细'] = $new_data;

			$title_arr = array('代理手机号','姓名','身份','总充值','分成占比','总收入','下属贡献');
			$filename = Config::NAME."收益明细".date("Ymd",time());//文件名称\
			$first_name = Config::NAME."地区".$start_time."至".$end_time."收益明细";//第一行文字

			//$data 数据格式
			// $data = array(
			// 	'唐'=>array(array('13','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('12','8618911554496','100','8618911551111','2016.01.20','')
			// 		       )
			// 	,'里'=>array(array('15','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('14','8618911554496','100','8618911551111','2016.01.20','')
			// 				)

			// 	);

			BaseFunction::write_income_xls($result['income'], $title_arr, $filename, $first_name );

		}while(false);

		return $response;
	}


	//收益提取列表下载  city
	public function extract_income_list_excel($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();
		$tmp = array();
		$aid = 255;

		do {
			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	!isset($params['search_aid'])
			|| 	!isset($params['start_time'])
			|| 	!isset($params['end_time'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			$mcobj = BaseFunction::getMC();

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}
			
			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			//搜索时间检验是否在可查询范围内
			if (!empty($params['start_time']) && !empty($params['end_time']) && strtotime($params['start_time']) > strtotime($params['end_time']))
			{
				$response['sub_code'] = 2; $response['desc'] = __LINE__; break;
			}
			
			if(empty($params['search_aid']) )
			{						
				$sql =  "select `out_trade_no` from `income` where 1";			
			}
			else
			{
				$sql =  "select `out_trade_no` from `income` where uid = " .$params['search_aid'];			
			}

			if(!empty($params['start_time']))
			{
				$sql .= " and init_time >= " . strtotime($params['start_time']);
			}
			if(!empty($params['end_time']))
			{
				$sql .= " and init_time < " . (strtotime($params['end_time']) + 86400);
			}		
			$sql .=  " order by init_time desc";

			$obj_income_list_factory =  new IncomeListFactory($mcobj, null, null, $sql);
			if($obj_income_list_factory->initialize() && $obj_income_list_factory->get())
			{
				$obj_income_multi_factory = new IncomeMultiFactory($mcobj, $obj_income_list_factory);
				if($obj_income_multi_factory->initialize() && $obj_income_multi_factory->get())
				{
					$obj_income_multi = $obj_income_multi_factory->get();
					foreach($obj_income_multi as $obj_income_multi_item)
					{
						$obj_income_multi_item->out_trade_no = Config::TRADE_NO_KEY.$obj_income_multi_item->out_trade_no;
						
						unset($obj_income_multi_item->expire_time);
						unset($obj_income_multi_item->third_party_type);
						$obj_income_multi_item->third_notify_info;

						if($obj_income_multi_item->status == 0)
						{
							$obj_income_multi_item->status = '提取中';
						}
						elseif($obj_income_multi_item->status == 1)
						{
							$obj_income_multi_item->status = '提取成功';
						}
						elseif($obj_income_multi_item->status == 2)
						{
							$obj_income_multi_item->status = '提取失败';
						}

						if($obj_income_multi_item->notify_status == 0)
						{
							$obj_income_multi_item->notify_status = '未通知';
						}
						elseif($obj_income_multi_item->notify_status == 1)
						{
							$obj_income_multi_item->notify_status = '通知成功';
						}
						elseif($obj_income_multi_item->notify_status == 2)
						{
							$obj_income_multi_item->notify_status = '通知失败';
						}
						elseif($obj_income_multi_item->notify_status == 3)
						{
							$obj_income_multi_item->notify_status = 'SYSTEMERROR';
						}


						$obj_income_multi_item->start_time = date("Y-m-d",$obj_income_multi_item->start_time);
						$obj_income_multi_item->end_time = date("Y-m-d",$obj_income_multi_item->end_time-86400);
						$obj_income_multi_item->init_time = date("Y-m-d H:i:s",$obj_income_multi_item->init_time);
						$obj_income_multi_item->update_time = date("Y-m-d H:i:s",$obj_income_multi_item->update_time);
						$data['list'][] = $obj_income_multi_item;
					}

				}
				else
				{
					$obj_income_multi_factory->clear();
				}
				
			}
			else
			{
				$obj_income_list_factory->clear();
			}

			$result['income_list']['提现明细'] = $data['list'];
	
			$title_arr = array('灵飞订单号','推广员ID','提现金额','收益开始时间','收益结束时间','灵飞订单状态','微信订单状态','微信订单号','微信订单描述','订单生成时间','订单提现时间(完成时间)');
			$filename = Config::NAME."提现明细".date("Ymd",time());//文件名称

			if(!empty($params['start_time']) && !empty($params['end_time']) )
			{
				$first_name = Config::NAME."地区".$params['start_time']."至".$params['end_time']."今提现明细";//第一行文字
			}
			elseif(!empty($params['start_time']) )
			{
				$first_name = Config::NAME."地区".$params['start_time']."至今提现明细";//第一行文字
			}
			elseif(!empty($params['end_time']) )
			{
				$first_name = Config::NAME."地区截止至".$params['end_time']."提现明细";//第一行文字
			}
			else
			{
				$first_name = Config::NAME."地区全部提现明细";//第一行文字
			}

			//$data 数据格式
			// $data = array(
			// 	'唐'=>array(array('13','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('12','8618911554496','100','8618911551111','2016.01.20','')
			// 		       )
			// 	,'里'=>array(array('15','8618911554496','100','8618911551111','2016.01.20','')
			// 		       ,array('14','8618911554496','100','8618911551111','2016.01.20','')
			// 				)

			// 	);

			BaseFunction::write_income_list_xls($result['income_list'], $title_arr, $filename, $first_name );
			

			$response['data'] = $data;
		} while (false);

		return $response;
	}


	//设置提现比例
	public function set_shared($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0, 'data'=>array());
		$rawsqls = array();
		$itime = time();
		$data = array();

		do {
			if (empty($params['shared'])
			|| 	empty($params['sub_shared'])
			|| 	empty($params['aid'])
			|| 	empty($params['key'])
			|| 	empty($params['shop'])
			|| 	empty($params['type'])
			|| 	!in_array($params['type'],array(1,2))
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
			
			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			//验证权限
			$u_name = '';
			$power_result = $this->_power_check(array('uid'=>$params['aid'], 'modular'=>CatConstant::POWER_MOD, 'type'=>1, 'page'=>1, 'city'=>$params['shop'], 'power_fun'=>__FUNCTION__), $u_name);
			if (!$power_result)
			{
				$response['sub_code'] = 2;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();

			$shared_arr = $params['shared'];
			if(  $shared_arr[9]*100 != 100 
			|| $shared_arr[8]*100 > 100 || $shared_arr[8]*100 < 0
			|| $shared_arr[1]*100 > 100 || $shared_arr[1]*100 < 0
			|| $shared_arr[2]*100 > 100 || $shared_arr[2]*100 < 0
			)
			{
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}

			$sub_shared_arr = $params['sub_shared'];
			if(  $sub_shared_arr[9]*100 != 100 
			|| $sub_shared_arr[8]*100 > 100 || $sub_shared_arr[8]*100 < 0
			|| $sub_shared_arr[1]*100 > 100 || $sub_shared_arr[1]*100 < 0
			|| $sub_shared_arr[2]*100 > 100 || $sub_shared_arr[2]*100 != $shared_arr[2]*100
			)
			{
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}
			
			$third_shared_arr = $params['third_shared'];
			if( $third_shared_arr[8]*100 > 100 || $third_shared_arr[8]*100 < 0	)
			{
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}
			
			$marge_shared[] = $shared_arr;
			$marge_shared[] = $sub_shared_arr;
			$marge_shared[] = $third_shared_arr;
			
			$shared_info = json_encode($marge_shared);
			//临时补位
			$third_shared_arr[9] = 1;
			$third_shared_arr[1] = 0;
			$third_shared_arr[2] = 0;

			if($params['type'] == 2)
			{
				$result = $this->_boss_set_shared($shared_arr,$sub_shared_arr,$shared_info, $third_shared_arr);
				if(isset($result) && ($result['code'] != 0 ||  $result['sub_code'] != 0) )
				{
					$response['sub_code'] = $result['code'];$response['sub_code'] = $result['sub_code']; $response['desc'] = __line__;break;
				}

				break;
			}

			if (empty($params['change_aid']))	
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//只能修改8的  ,自动修改下级
			$sql = "select `aid` from `agent_info` where p_num = ".intval($params['change_aid']);
			$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,null,null,null,null,null,$sql);
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{		
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,$obj_agentinfo_list_factory);	
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();

					foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
					{
						if($obj_agentinfo_multi_item->aid == $params['change_aid'] && ($obj_agentinfo_multi_item->type != 8 && $obj_agentinfo_multi_item->type != 9 )) //change_aid必须是8
						{
							$response['sub_code'] = 4;$response['desc'] = __line__;	break 2;
						}
											
						$obj_agentinfo_multi_item->shared = $shared_arr[$obj_agentinfo_multi_item->type];
						$obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$obj_agentinfo_multi_item->type];
						$obj_agentinfo_multi_item->third_shared = $third_shared_arr[$obj_agentinfo_multi_item->type];

						if($obj_agentinfo_multi_item->type == 9 || $obj_agentinfo_multi_item->type == 8)
						{
							$obj_agentinfo_multi_item->shared_info = $shared_info;
						}
						else
						{
							$obj_agentinfo_multi_item->shared_info = '';
						}
						$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();		
										
					}									
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 5; $response['desc'] = __line__;break;
				}
			}
			else
			{
				//可能是公司9   //但是无法更改 8以下人员  只改9
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['change_aid']);	
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
					{
						if($obj_agentinfo_multi_item->type !=9)
						{
							$response['sub_code'] = 4;$response['desc'] = __line__;	break 2;
						}
						else
						{
							$obj_agentinfo_multi_item->shared = $shared_arr[$obj_agentinfo_multi_item->type];
							$obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$obj_agentinfo_multi_item->type];
							$obj_agentinfo_multi_item->third_shared = $third_shared_arr[$obj_agentinfo_multi_item->type];

							if($obj_agentinfo_multi_item->type == 9 || $obj_agentinfo_multi_item->type == 8)
							{
								$obj_agentinfo_multi_item->shared_info = $shared_info;
							}
							else
							{
								$obj_agentinfo_multi_item->shared_info = '';
							}
							$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();	
						
						}
					}									
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 5; $response['desc'] = __line__;break;
				}
			}
		
			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 6; $response['desc'] = __line__;break;
			}

			if(isset($obj_agentinfo_multi_factory))
			{
				$obj_agentinfo_multi_factory->writeback();
			}

			$response['data'] = $data;
		} while (false);

		return $response;
	}

	//开通公会付费功能
	public function guild_pay($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0, 'data'=>array());
		$rawsqls = array();
		$itime = time();
		$data = array();
		do {

			if (empty($params['aid'])
			|| 	empty($params['key'])
			|| 	!isset($params['encrypt_uid'])
			|| 	empty($params['type'])
			|| 	!in_array($params['type'],array(1,2))
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			$mcobj = BaseFunction::getMC();

			//检查用户登录
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __LINE__;	break;
			}

			if($params['type'] == 1)
			{
				$uid =(int)substr(strrchr($params['encrypt_uid'], "$"), 1);
				$aid = $params['aid'];
				$encrypt = $this->_get_encrypt_uid($uid , $aid);

				if($params['encrypt_uid'] != $encrypt)
				{
					BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($params['encrypt_uid'], true) . "\n" . __LINE__ . "\n");
					BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($encrypt, true) . "\n" . __LINE__ . "\n");
					$response['sub_code'] = 2; $response['desc'] = __line__; break;
				}
				
				$sql = "select `opend_status` from `agent_info` where opend_status = ".intval($uid);
				$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,null,null,null,null,null,$sql);
				if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
				{
					$response['sub_code'] = 7; $response['desc'] = __line__;break;
				}
			}
			else
			{
				$uid = 0;
			}


			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);	
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

				if($params['type'] == 1 && $obj_agentinfo_multi_item->opend_status != 0)
				{
					$response['sub_code'] = 5; $response['desc'] = __line__;break;
				}
				elseif($params['type'] == 2 && $obj_agentinfo_multi_item->opend_status == 0)
				{
					$response['sub_code'] = 6; $response['desc'] = __line__;break;
				}
				
				$obj_agentinfo_multi_item->opend_status = $uid;
				$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['sub_code'] = 3; $response['desc'] = __line__; break;
			}


			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 4; $response['desc'] = __line__;break;
			}

			if(isset($obj_agentinfo_multi_factory))
			{
				$obj_agentinfo_multi_factory->writeback();
			}

		} while (false);
		
		$response['data'] = $data;
		return $response;
	}
	
	//设置全部提现比例  
	private function _boss_set_shared($shared_arr=null,$sub_shared_arr=null,$shared_info='',$third_shared_arr=null)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$data = array();

		do {		
			$mcobj = BaseFunction::getMC();
			$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj);
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,$obj_agentinfo_list_factory);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
	
					if(is_array($obj_agentinfo_multi))
					{
						foreach($obj_agentinfo_multi as $obj_agentinfo_multi_item)
						{
							$obj_agentinfo_multi_item->shared = $shared_arr[$obj_agentinfo_multi_item->type];
							$obj_agentinfo_multi_item->sub_shared = $sub_shared_arr[$obj_agentinfo_multi_item->type];
							$obj_agentinfo_multi_item->third_shared = $third_shared_arr[$obj_agentinfo_multi_item->type];

							if($obj_agentinfo_multi_item->type == 9 || $obj_agentinfo_multi_item->type == 8)
							{
								$obj_agentinfo_multi_item->shared_info = $shared_info;
							}
							else
							{
								$obj_agentinfo_multi_item->shared_info = '';
							}
							$rawsqls[] = $obj_agentinfo_multi_item->getUpdateSql();
						}
					}				
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					$response['sub_code'] = 2; $response['desc'] = __line__;break;
				}				
			}

			if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
			{
				BaseFunction::logger($this->log, "【rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__;break;
			}

			if(isset($obj_agentinfo_multi_factory))
			{
				//$obj_agentinfo_multi_factory->writeback();
				$obj_agentinfo_multi_factory->clear();
			}

			$response['data'] = $result['data'];
		} while (false);

		return $response;
	}
		
	//获取已提现的日期
	private function _is_extract_date($aid)
	{
		if(empty($aid))
		{
			return false;
		}
		$mcobj = BaseFunction::getMC();
		$extract_date = '';
		$sql = "select `end_time` from `income` where uid = ".intval($aid)." and status = 1 order by init_time desc limit 1 ";
		$obj_income_list_factory = new IncomeListFactory($mcobj,null, null, $sql );
		if($obj_income_list_factory->initialize() && $obj_income_list_factory->get())
		{
			$obj_income_list = $obj_income_list_factory->get();
			$extract_date = $obj_income_list[0] - 86400;
		}
		else
		{
			$obj_income_list_factory->clear();
		}		
		return $extract_date;
	}

	//self收益统计  city  最后改成 private
	private function _self_income_statistics($params)
	{
		$response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0 ,'data' => 0);
		$idArray = array();
	
		do {		
			if (empty($params['aid'])
			|| 	empty($params['start_time'])
			|| 	empty($params['end_time'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}
					
			$mcobj = BaseFunction::getMC();
			
			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null ,$params['aid']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
				$type = $obj_agentinfo_multi_item->type;	
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				$response['data'] = 0; break;
			}

			if($type == 9)
			{
				$response['sub_code'] = 4; $response['desc'] = __line__; break;
			}

			 //查询收益	
			$data_request = array(
                'mod' => 'Business'
                , 'act' => 'income_statistics'
                , 'platform' => 'gfplay'
				,'aid'   => $params['aid']
               , 'type'  => $type
               , 'page'  => 1
               , 'download_type'  => 0
               , 'aidArray' => $params['aid']
               , 'start_time' => $params['start_time']
               , 'end_time' => $params['end_time']
            );

			$randkey = BaseFunction::encryptMD5($data_request);
            $url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__; break;				
			}
			
			$operator = $result['data']['operator'];

			//循环operator
			$my_total_income = 0;
			$pay_status = 0;

			foreach($operator as $operator_item)
			{						
				$my_total_income += ($operator_item['recharge_direct'] * $operator_item['recharge_direct_shared']) + ($operator_item['recharge_subordinate'] * $operator_item['recharge_subordinate_shared']) + ($operator_item['recharge_under_subordinate'] * $operator_item['recharge_under_subordinate_shared']);				
				$pay_status += $operator_item['pay_status'];
			}
			$result['data']['pay_status'] = $pay_status;

			$my_total_income = sprintf("%.2f", $my_total_income);
			$response['data'] = $my_total_income;
			$response['pay_status'] = intval($result['data']['pay_status']);

		}while(false);

		return $response;
	}


	//更新kpi_new
	private function _update_income_statistics($params)
	{	
		if (empty($params['aid'])
		|| 	empty($params['start_time'])
		|| 	empty($params['end_time'])
		|| 	!isset($params['pay_status'])
		)
		{
			return false;		
		}
						
		//查询收益	
		$data_request = array(
			'mod' => 'Business'
			, 'act' => 'update_income_pay_status'
			, 'platform' => 'gfplay'
			,'aid'   => $params['aid']
			, 'pay_status'  => $params['pay_status']
			, 'start_time' => $params['start_time']
			, 'end_time' => $params['end_time']
		);

		$randkey = BaseFunction::encryptMD5($data_request);
		$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
		$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);			
		if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
			BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
			BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
			return false;
		}

		return true;
	}

	//更新kpi_new
	private function _delete_income($delete_id)
	{	
		if ( empty($delete_id)
		)
		{
			return false;		
		}

		$mcobj = BaseFunction::getMC();

		$obj_income_multi_factory =  new IncomeMultiFactory($mcobj, null, $delete_id);
		if($obj_income_multi_factory->initialize() && $obj_income_multi_factory->get())
		{
			$obj_income_multi = $obj_income_multi_factory->get();
			$obj_income_multi_item = current($obj_income_multi);
			if(isset($obj_income_multi_item))
			{
				$obj_income_multi_item->third_notify_info = "原".$obj_income_multi_item->uid;
				$obj_income_multi_item->uid = 8600000000000;

				$rawsqls[] = $obj_income_multi_item->getUpdateSql();
			}
		}
		if ($rawsqls && !BaseFunction::execute_sql_backend($rawsqls))
		{
			BaseFunction::logger($this->log, "【严重警告rawsqls】:\n" . var_export($rawsqls, true) . "\n" . __LINE__ . "\n");
			return false;
		}

		return true;
	}

	//判断每天提现次数
	private function _sum_income_time($aid)
	{
		$itime = strtotime(date("Ymd",time()));
		if(empty($aid))
		{
			return false;
		}
		$mcobj = BaseFunction::getMC();
		$sum_obj_income_list_itme = '';
		$sql = "select count(*) from `income` where uid = ".intval($aid)." and notify_status != 2 and init_time >= ".$itime." and init_time < ".($itime+86400);
		$obj_income_list_factory = new IncomeListFactory($mcobj,null, null, $sql );
		if($obj_income_list_factory->initialize() && $obj_income_list_factory->get())
		{
			$sum_obj_income_list_itme = $obj_income_list_factory->get();			
		}
		else
		{
			$obj_income_list_factory->clear;
		}		
		return $sum_obj_income_list_itme[0];
	}

	//判断提现日期是否连续
	private function _is_continued($params)
	{
		$return = false;
		if(empty($params['aid'])
		|| empty($params['start_time'])
		)
		{
			return false;
		}
		$mcobj = BaseFunction::getMC();
		$sql = "select `end_time` from `income` where uid = ".intval($params['aid'])." and notify_status != 2 order by init_time desc limit 1";
		$obj_income_list_factory = new IncomeListFactory($mcobj,null, null, $sql );
		if($obj_income_list_factory->initialize() && $obj_income_list_factory->get())
		{
			$obj_income_list_itme = $obj_income_list_factory->get();
			if($obj_income_list_itme[0] == $params['start_time'] || Config::START_INCOME_TIME == $params['start_time'] )
			{
				$return = true;
			}			
		}
		else
		{
			$obj_income_list_factory->clear();
			$sql = "select `init_time` from `agent_info` where aid = ".intval($params['aid'])." order by init_time desc limit 1";
			$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj,null,null,null,null,null,null,$sql);
			if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
			{
				$obj_agentinfo_list_itme = $obj_agentinfo_list_factory->get();

				if(!empty(Config::START_INCOME_TIME) && Config::START_INCOME_TIME > $obj_agentinfo_list_itme[0] )
				{
					$time = Config::START_INCOME_TIME;
				}
				else
				{
					$time = $obj_agentinfo_list_itme[0];
				}
				$init_date_time = strtotime(date("Ymd",$time));
				if($init_date_time == $params['start_time'])
				{
					$return = true;
				}	
			}
			else
			{
				$obj_agentinfo_list_factory->clear();
			}
		}	
		return $return;
	}

	//判断是否有未提取的收益
	private function _have_income($params)
	{
		$return = true;
		if(empty($params['aid'])
		|| empty($params['start_time'])  //2月前
		|| 	empty($params['end_time'])  //今日凌晨
		)
		{
			return false;
		}
		$mcobj = BaseFunction::getMC();
		$sql = "select `end_time` from `income` where uid = ".intval($params['aid'])." and notify_status != 2 order by init_time desc limit 1";
		$obj_income_list_factory = new IncomeListFactory($mcobj,null, null, $sql );
		if($obj_income_list_factory->initialize() && $obj_income_list_factory->get())
		{
			$obj_income_list_itme = $obj_income_list_factory->get();
			if($obj_income_list_itme[0] == $params['end_time']) //如果提现记录结束时间==昨天 
			{
				$return = false;
			}
			else
			{
				//最后一次提现结束日期 到昨天 判断是否有收益
				$params['start_time'] = $obj_income_list_itme[0];
				$income_result = $this->_self_income_statistics($params);
				if ($income_result['code'] == 0 && $income_result['sub_code'] == 0)
				{
					$income = intval($income_result['data']*100);
					if($income == 0)
					{
						$return = false;
					}
				}
				elseif($income_result['code'] == 2)  //这个人 没有kpi_new的记录
				{
					$return = true;
				}

			}	

		}
		else
		{
			//如果没有提现记录   提现开始日期到昨日 是否有收益
			$obj_income_list_factory->clear;
			$income_result = $this->_self_income_statistics($params);
			if ($income_result['code'] == 0 && $income_result['sub_code'] == 0)
			{
				$income = intval($income_result['data']*100);
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($income, true) . "\n" . __LINE__ . "\n");
				if($income == 0)
				{
					$return = false;
				}
			}
		}	
		return $return;
	}


	//获取分成比例
	private function _get_shared($aid ,$type, &$sub_shared_arr, &$shared_info ,&$third_shared_arr)
	{
		//获取分成比例
			$shared_arr = array();
			$sub_shared_arr = array();
			if($type == 9)
			{
				$marge_arr = json_decode($shared_info,true);
				$shared_arr = $marge_arr[0];
				$sub_shared_arr  = $marge_arr[1];
				$third_shared_arr  = $marge_arr[2];
			}
			elseif($type == 8)
			{
				$marge_arr = json_decode($shared_info,true);
				$shared_arr = $marge_arr[0];
				$sub_shared_arr  = $marge_arr[1];

				$third_shared_arr  = $marge_arr[2];
				$shared_info = '';
			}
			else   //type==1
			{	
				$shared_info = '';
				$mcobj = BaseFunction::getMC();
				
				$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null ,$aid);
				if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
				{
					$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
					$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
					//$type = $obj_agentinfo_multi_item->shared_info;

					$shared_json = $obj_agentinfo_multi_item->shared_info;
					$marge_arr = json_decode($shared_json,true);
					$shared_arr = $marge_arr[0];
					$sub_shared_arr  = $marge_arr[1];	
					$third_shared_arr  = $marge_arr[2];		
				}
				else
				{
					$obj_agentinfo_multi_factory->clear();
					return false;
				}
			}

			// $third_shared_arr[9] = 1;
			// $third_shared_arr[1] = 0;
			// $third_shared_arr[2] = 0;	

		return  $shared_arr;
	}


	//获取登陆这openid
	private function _get_aid_openid($params)
	{
		$response = array('code' => CatConstant::OK,'desc' => __LINE__, 'sub_code' => 0);
		$openid = '';
		do {	
			
			if( empty($params['aid']))		
			{
				$response['code'] = 1; $response['desc'] = __line__; break;
			}
			$mcobj = BaseFunction::getMC();
			$obj_user_factory = new UserMultiFactory($mcobj,null,$params['aid']);
			if($obj_user_factory->initialize() && $obj_user_factory->get())
			{
				$obj_user = $obj_user_factory->get();
				$obj_user_item = current($obj_user);
				if(isset($obj_user_item))
				{
					$openid = $obj_user_item->wx_openid;
				}
			}
			else
			{
				$obj_user_factory->clear();
			}

			$response['data'] = $openid;

		}while(false);

		return $response;
	}

    //下级所以代理  city
	private function _getSon($aid)
	{
		$data = array();

		do {
            $mcobj = BaseFunction::getMC();

			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $aid);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
                $obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

                //副会长
				if($obj_agentinfo_multi_item->type == 2)
				{
                    $data[] = $aid;
					break;
				}

				//会长
				if($obj_agentinfo_multi_item->type == 1)
				{
					$data[] = $aid;
					$obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null, '', $aid);
					if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
					{
						$obj_agentinfo_list = $obj_agentinfo_list_factory->get();
						foreach ($obj_agentinfo_list as $obj_agentinfo_list_item)
						{
							$data[] = $obj_agentinfo_list_item;
						}

						break;
					}
					else
                    {
                        break;
                    }
				}

				//城市合伙人
				if($obj_agentinfo_multi_item->type == 8)
				{
                    $obj_agentinfo_list_factory = new AgentInfoListFactory($mcobj, null, '', null, null, null,null,null,$obj_agentinfo_multi_item->p_num);
					if($obj_agentinfo_list_factory->initialize() && $obj_agentinfo_list_factory->get())
					{
						$obj_agentinfo_list = $obj_agentinfo_list_factory->get();

                        foreach ($obj_agentinfo_list as $obj_agentinfo_list_item)
						{
							$data[] = $obj_agentinfo_list_item;
						}
						break;
					}
					else
                    {
                        break;
                    }
				}

				//总公司
				if($obj_agentinfo_multi_item->type == 9)
				{
                    $data = array();break;
				}
			}
			else
			{
				$obj_agentinfo_multi_factory->clear();
				return false;
			}

		}while(false);

		return $data;
	}

	//是否是上下级关系或判断登录者身份  city
	private function _is_self_type($aid)
	{
		$type = 0;
		if( empty($aid)  //登录者
		)
		{
			return false;
		}

		$mcobj = BaseFunction::getMC();

		$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null ,$aid);
		if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
		{
			$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
			$obj_agentinfo_multi_item = current($obj_agentinfo_multi);
			$type = $obj_agentinfo_multi_item->type;
			return $type;
		}
		else
		{
			$obj_agentinfo_multi_factory->clear();
			return false;
		}

		return false;
	}

	//获取父级代理id  city
	private function _getFather($aid, &$idArray, $p_aid = 0)
    {
        $mcobj = BaseFunction::getMC();
        $obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null ,$aid);
        if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
        {
            $obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
            $obj_agentinfo_multi_item = current($obj_agentinfo_multi);
            if(!empty($p_aid) && $obj_agentinfo_multi_item->p_aid == $p_aid)
            {
            	 $idArray[] = $obj_agentinfo_multi_item->p_aid;
            	 return;
            }

            if ($obj_agentinfo_multi_item->p_aid == 0)
            {
                return;
            }

            $idArray[] = $obj_agentinfo_multi_item->p_aid;
            $this->_getFather($obj_agentinfo_multi_item->p_aid, $idArray);
        }
        else
        {
            $obj_agentinfo_multi_factory->clear();
            return false;
        }
    }


    private function _sendUserRequest($params, $line, $bool = null)
    {
    	$path = Config::USER_PATH;
    	$result = $this->_commonRequest($path, $params, $line, $bool);
    	return $result;
    }

    private function _sendPowerRequest($params, $line, $bool = null)
    {
    	$path = Config::POWER_PATH;
    	$result = $this->_commonRequest($path, $params, $line, $bool);
    	return $result;
    }

    private function _sendHttpRequest($params, $line, $bool = null)
    {
    	$path = Config::GET_USER;
    	$result = $this->_commonRequest($path, $params, $line, $bool);
    	return $result;
    }

    private function _commonRequest($path, $params, $line, $bool = null)
    {
    	$data_request = array(
    		'mod'=> 'Business',
    		'platform' => 'gfplay',
    		);

    	$data_request = array_merge($data_request, $params);

		$randkey = BaseFunction::encryptMD5($data_request);
		$url = $path . "?randkey=" . $randkey . "&c_version=0.0.1";

		if ($bool == null)
		{
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
			if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
			{
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . $line . "\n");
				BaseFunction::logger($this->log, "【result】:\n" . var_export($result, true) . "\n" . $line . "\n");
				$response['sub_code'] = 3; $response['desc'] = $line; 
				return $response;
			}
		}
		else
		{
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);
            if (!empty($result['code']) || !empty($result['sub_code'])) 
            {
                BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . $line . "\n");
                BaseFunction::logger($this->log, "【result】:\n" . var_export($result, true) . "\n" . $line . "\n");
				$response['sub_code'] = 3; $response['desc'] = $line;
                return $response;
            }
		}
	
		return $result;
	}
	
	private function _get_user_conf()
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		do {
			//远程调用play_recharge_list
			$data_request = array(
				'mod' => 'Business'
			, 'act' => 'get_conf'
			, 'platform' => 'gfplay'
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::USER_WX_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__; break;
			}

			$response['data'] = $result['data'];
		} while (false);

		return $response;
	}

	private function _get_agent_uid_num($agent_id)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		do {
			//远程调用play_recharge_list
			$data_request = array(
				'mod' => 'Business'
			, 'act' => 'getUserBelongsAnAgent'
			, 'platform' => 'gfplay'
			, 'agent_id' => $agent_id
			);

			$randkey = BaseFunction::encryptMD5($data_request);
			$url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
			$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))),true);
			if (!$result || !isset($result['code']) || $result['code'] != 0 || (isset($result['sub_code']) && $result['sub_code'] != 0)) {
				BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
				BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
				$response['sub_code'] = 3; $response['desc'] = __line__; break;
			}

			$response['amount'] = $result['data']['amount'];
		} while (false);
		
		return $response;
	}



	//更换玩家绑定的代理
	public function change_uid_bind_agent($params)
	{
		$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
		$rawsqls = array();
		$itime = time();
		$data = array();

		do{
			if(empty($params['aid_of_operator'])
			|| empty($params['aid_before_change'])
			|| empty($params['aid_after_change'])
			|| empty($params['key'])
			)
			{
				$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
			}

			//检查用户登录
			$params['aid'] = $params['aid_of_operator'];
			$login_result = $this->_login_check($params);
			if ($login_result['code'] != 0 || $login_result['sub_code'] != 0)
			{
				$response['sub_code'] = 1;$response['desc'] = __line__;	break;
			}

			$mcobj = BaseFunction::getMC();

			////////////////验证要更换的新的手机号码 是否已经存在////////////////////
			$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj, null, $params['aid_after_change']);
			if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
			{
				///////////////////////////更改用户agent_id////////////////////////////
				$data_request = array(
					'mod' => 'Business',
					'act' => 'change_user_agent',
					'platform' => 'gfplay',
					'aid_before_change' => $params['aid_before_change'],
					'aid_after_change' => $params['aid_after_change']
				);
	
				$randkey = BaseFunction::encryptMD5($data_request);
				$url = Config::GET_USER."?randkey=".$randkey."&c_version=0.0.1";
				$result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
				if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0))
				{								
					BaseFunction::logger($this->log, "【更新玩家agentid】:\n".var_export($result, true)."\n".__LINE__."\n");
					$response['sub_code'] = 3;$response['desc'] = __line__;break;
				}				
			}
								
			$response['data'] = $data;
		}while(false);

		return $response;
	}


	//微信登录 检查人员是否存在  no
	// public function agent_back($params)
	// {
	// 	$response = array('code' => CatConstant::OK, 'desc' => __LINE__, 'sub_code' => 0);
	// 	$data = '';

	// 	do {
	// 		if (empty($params['aid'])
	// 		)
	// 		{
	// 			$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
	// 		}

	// 		$mcobj = BaseFunction::getMC();

	// 		$obj_agentinfo_multi_factory = new AgentInfoMultiFactory($mcobj,null,$params['aid']);
	// 		if($obj_agentinfo_multi_factory->initialize() && $obj_agentinfo_multi_factory->get())
	// 		{
	// 			$obj_agentinfo_multi = $obj_agentinfo_multi_factory->get();
	// 			if(!empty($obj_agentinfo_multi))
	// 			{
	// 				$obj_agentinfo_multi_item = current($obj_agentinfo_multi);

	// 				if ($obj_agentinfo_multi_item->type == 9 && empty($params['is_form_user_wx']))   //游戏客户端  9不能成为工会
	// 				{
	// 					$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
	// 				}
	// 				$data = $obj_agentinfo_multi_item;
	// 			}
	// 		}
	// 		else
	// 		{
	// 			$obj_agentinfo_multi_factory->clear();
	// 			$response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
	// 		}

	// 		// if($obj_agentinfo_multi_factory)
	// 		// {
	// 		// 	$obj_agentinfo_multi_factory->writeback();
	// 		// }

	// 		$response['data'] = $data;
	// 	} while (false);

	// 	return $response;
	// }
    
}








