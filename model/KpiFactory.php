<?php
namespace bigcat\model;

use bigcat\inc\Factory;
use bigcat\model\Kpi;
use bigcat\inc\BaseFunction;
use bigcat\conf\CatConstant;
class KpiFactory extends Factory
{
    const objkey = 'gfplay_';
    private $log = './log/business.log';
    public $get_url;

    public function __construct($dbobj, $uid, $get_url) 
    {
        $time_key = date('YmdH', time()); 
        $serverkey = self::objkey;
        $objkey = self::objkey."_".$uid.MD5($get_url).$time_key;
        $this->get_url = $get_url;

        parent::__construct($dbobj, $serverkey, $objkey);
        return true;
    }

    public function retrive() 
    {
        $page = 1;
        $obj = new Kpi();

        $data_request = array(
        'mod' => 'Business'
        , 'act' => 'kpi_get'
        , 'platform' => 'gfplay'
        , 'page' => $page
        );
        $randkey = BaseFunction::encryptMD5($data_request);
        $url = $this->get_url . "?randkey=" . $randkey . "&c_version=0.0.1";
        $result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
        if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
            BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
            BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
        }
        else
        {
            $obj->data = $result; //data Kpi()中的
        }         
            
        return $obj;
    }


    //  public function retrive() 
    // {

    //     $page = 1;
    //     foreach (CatConstant::GET_USER as $key => $get_user) 
    //     { 
    //         $data_request = array(
    //         'mod' => 'Business'
    //         , 'act' => 'kpi_get'
    //         , 'platform' => 'gfplay'
    //         , 'page' => $page
    //         );
    //         $randkey = BaseFunction::encryptMD5($data_request);
    //         $url = $get_user . "?randkey=" . $randkey . "&c_version=0.0.1";
    //         $result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
    //         if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
    //             BaseFunction::logger($this->log, "【data_request】:\n" . var_export($data_request, true) . "\n" . __LINE__ . "\n");
    //             BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
    //             $response['code'] = CatConstant::ERROR; $response['desc'] = __line__; break;
    //         }

    //         if(isset($result->data->kpi_list[0]))
    //         {          
    //             $data[$key][] = $result->data->kpi_list[0];                                 
    //         }

    //         if(isset($result->data->kpi_list[1]))
    //         {
    //             $data[$key][] = $result->data->kpi_list[1];                                 
    //         }           
    //     }

    //     $obj = $data;    
    //     return $obj;
    // }
}

