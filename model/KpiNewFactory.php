<?php
namespace bigcat\model;

use bigcat\inc\Factory;
use bigcat\model\KpiNew;
use bigcat\inc\BaseFunction;
use bigcat\conf\Config;
class KpiNewFactory extends Factory
{
    const objkey = 'gfplay_';
    private $log = './log/business.log';
    private $aid;
    private $type;
    private $aidstr;

    public function __construct($dbobj, $aid, $type, $aidstr) 
    {
        $time_key = date('YmdH', (time() - 60*5)); 
        $serverkey = self::objkey;
        $objkey = self::objkey."_".$aid.$time_key;
        $this->aid = $aid;
        $this->type = $type;
        $this->aidstr = $aidstr;
  
        parent::__construct($dbobj, $serverkey, $objkey);
        return true;
    }

    public function retrive() 
    {
        $obj = new KpiNew();

        $data_request = array(
            'mod' => 'Business'
        , 'act' => 'kpi_get_new'
        , 'platform' => 'gfplay'
        , 'aid' => $this->aid
        , 'type' => $this->type
        , 'aidArray' => $this->aidstr
        );
        $randkey = BaseFunction::encryptMD5($data_request);
        $url = Config::GET_USER . "?randkey=" . $randkey . "&c_version=0.0.1";
        $result = json_decode(BaseFunction::https_request($url, array('parameter' => json_encode($data_request))));
        if (!$result || !isset($result->code) || $result->code != 0 || (isset($result->sub_code) && $result->sub_code != 0)) {
            BaseFunction::logger($this->log, "【login_check】:\n" . var_export($result, true) . "\n" . __LINE__ . "\n");
        }
        else
        {
            $obj->id = $this->aid;
            $obj->data = $result->data;
            $obj->before_writeback();
        }   
        

        return $obj;
    }


}

