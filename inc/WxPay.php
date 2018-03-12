<?php
namespace bigcat\inc;

use bigcat\conf\Config;
use bigcat\inc\BaseFunction;

class WxPay{
	private $values = array();
	private $sign_data = array();
	private $log = './log/business.log';

	public function  __construct($sign_data)
	{
		$this->sign_data = $sign_data;
		$this->values['mch_appid'] =$this->sign_data['wx_appid'];
		$this->values['mchid'] = $this->sign_data['wx_mchid'];
		$this->values['openid'] = $this->sign_data['openid'];
		$this->values['nonce_str'] = $this->getNonceStr();
		$this->values['partner_trade_no'] = $this->sign_data['partner_trade_no'];
		$this->values['check_name'] = 'NO_CHECK';  //验证微信实名
		$this->values['amount'] = $this->sign_data['income'];
		$this->values['desc'] =  $this->sign_data['desc'];
		$this->values['spbill_create_ip'] = (string)BaseFunction::get_client_ip();
	}

	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams()
	{
		$buff = "";
		foreach ($this->values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign()
	{
		//签名步骤一：按字典序排序参数
		ksort($this->values);
		$string = $this->ToUrlParams();
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$this->sign_data['wx_key'];
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}

	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )
		{  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}

		
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
	 public function FromXml($xml)
	 {	
		 if(!$xml)
		 {
			 BaseFunction::logger($this->log, "【tmp】:\n" . var_export('数组数据异常！', true) . "\n" . __LINE__ . "\n");
		 }
		 //将XML转为array
		 //禁止引用外部xml实体
		 libxml_disable_entity_loader(true);
		 $result_arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		 return $result_arr;
	 }

	/**
	 * 输出xml字符
	 * @throws WxPayException
	**/
	public function ToXml()
	{
		$this->values['sign'] = $this->MakeSign();	;
		if(!is_array($this->values) 
			|| count($this->values) <= 0)
		{
			BaseFunction::logger($this->log, "【tmp】:\n" . var_export('数组数据异常！', true) . "\n" . __LINE__ . "\n");
    	}
    	
    	$xml = "<xml>";
    	foreach ($this->values as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
		$xml.="</xml>";
        return $xml; 
	}

	
	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 */
	 public  function postXmlCurl($xml, $useCert = false, $second = 30)
	 {			
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; 
		$ch = curl_init();
		 //设置超时
		 curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		 
		 //如果有配置代理这里就设置代理
		//  if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0" 
		// 	 && WxPayConfig::CURL_PROXY_PORT != 0){
		// 	 curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
		// 	 curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
		//  }
		 curl_setopt($ch,CURLOPT_URL, $url);
		 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
		 curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
		 //设置header
		 curl_setopt($ch, CURLOPT_HEADER, FALSE);
		 //要求结果为字符串且输出到屏幕上
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		 if($useCert == true){
			 //设置证书
			 //使用证书：cert 与 key 分别属于两个.pem文件
			 curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			 curl_setopt($ch,CURLOPT_SSLCERT, Config::SSLCERT_PATH);
			 curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			 curl_setopt($ch,CURLOPT_SSLKEY, Config::SSLKEY_PATH);
		 }
		 //post提交方式
		 curl_setopt($ch, CURLOPT_POST, TRUE);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		 //运行curl
		 $data = curl_exec($ch);
		 //返回结果
		 if($data){
			 curl_close($ch);
			 return $data;
		 } else { 
			 $error = curl_errno($ch);
			 curl_close($ch);
			 BaseFunction::logger($this->log, "【tmp】:\n" . var_export($error, true) . "\n" . __LINE__ . "\n");
		 }
	 }
	 


}

