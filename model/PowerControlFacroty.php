<?php
namespace bigcat\model;

use bigcat\inc\Factory;
use bigcat\inc\BaseFunction;
use bigcat\conf\Config;


class PowerControlFacroty extends Factory 
{
    const objkey = 'agent_management_power_control_key_';
    public function __construct($dbobj) 
    {
        $objkey = self::objkey;
        parent::__construct($dbobj, $objkey, $objkey, 1800);
        return true;
    }

    public function retrive() 
    {
        //global $POWER_PATH;

        $data_request = array(
        'mod' => 'Business'
        , 'act' => 'get_conf'
        , 'platform' => 'tocar'
        );

        $randkey = BaseFunction::encryptMD5($data_request);
        $url = Config::POWER_PATH . "?randkey=" . $randkey . "&c_version=0.0.1";
        $power_result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))), true);
        if (!$power_result || !isset($power_result['code']) || $power_result['code'] != 0 || (isset($power_result['sub_code']) && $power_result['sub_code'] != 0)) {
            return null;
        }
        if(!isset($power_result['data']['power_modular']) || !isset($power_result['data']['city_arr']) ) {
            return null;
        }

        $obj = new PowerControl();
        $obj->power_modular = $power_result['data'];

        return $obj;
    }
}

