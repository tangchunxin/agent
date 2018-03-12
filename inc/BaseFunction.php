<?php
/**
 * @author xuqiang76@163.com
 * @final 20160929
 */

namespace bigcat\inc;
use bigcat\inc\CatMemcache;
use bigcat\conf\Config;
//use bigcat\inc\PHPExcel;


class BaseFunction
{
	static $db_instance = null;

	//通过前端授权码code获得用户的微信openid
	public static function code_get_openid($code, $appid, $appsecret)
	{
		//获取openid
		$openid = '';
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
		$result = self::https_request($url);

		$jsoninfo = json_decode($result, true);
		if(isset($jsoninfo["openid"]))
		{
			$openid = $jsoninfo["openid"];//从返回json结果中读出openid
		}
		return $openid;
	}

	//通过前端授权码code获得用户的微信openid
	public static function code_get_wx_user($access_token, $openid)
	{
		//获取openid
		$wx_user_info = [];
		//$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid";
		$result = self::https_request($url);

		$jsoninfo = json_decode($result, true);
		if(isset($jsoninfo["openid"]) && isset($jsoninfo["nickname"]) && isset($jsoninfo["sex"]) && isset($jsoninfo["headimgurl"]) && isset($jsoninfo["city"]) && isset($jsoninfo["province"]))
		{
			$wx_user_info = $jsoninfo;
		}
		return $wx_user_info;
	}

	//通过前端授权码appid appsercret 获得用户的微信access_token
	public static function get_access_token()
	{
		//获取openid
		$access_token = [];	
		$appid = Config::WX_APPID;
		$appsecret = Config::WX_APPSECRET;	
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
		$result = self::https_request($url);
		$access_token = json_decode($result, true);
		if(empty($access_token['access_token']))
		{
			BaseFunction::logger("./log/business.log", "【tmp-access_token】:\n" . var_export($access_token['access_token'], true) . "\n" . __LINE__ . "\n");
			return false;
		}
		return $access_token;
	}

	//通过前端授权码access_token获得用户的微信jsapi_ticket
	public static function get_jsapi_ticket($access_token)
	{
		//获取openid
		$jsapi_ticket = [];		
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
		$result = self::https_request($url);
		$jsapi_ticket = json_decode($result, true);
		if( $jsapi_ticket['errcode'] != 0 || (isset($jsapi_ticket['errmsg']) && $jsapi_ticket['errmsg'] != 'ok'))
		{
			BaseFunction::logger("./log/business.log", "【tmp】:\n" . var_export($jsapi_ticket, true) . "\n" . __LINE__ . "\n");
			return false;
		}

		return $jsapi_ticket;
	}


//生成随机串
	public static function create_nonce_str($length = 16)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		$chars_lenth = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, $chars_lenth), 1);
		}
		return $str;
	}

	public static function get_sign($js_ticket)
	{
		
		$timestamp = (string)time();
		$nonce_str = self::create_nonce_str();
		$url = '';

		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$js_ticket&noncestr=$nonce_str&timestamp=$timestamp&url=$url";

		//$signature = sha1($string);

		$sign = array(
		"appId"     => Config::WX_APPID,
		"nonceStr"  => $nonce_str,
		"timestamp" => $timestamp,
		"url"       => $url,
		"signature" => '',
		"rawString" => $string
		);
		return $sign;
	}

	//发短信函数 阿里大鱼
	public static function sms_cz_alidayu($templateCode, $sms_param, $phone, $signname = "美车快拍")
	{
		$gearmanjson = array
		(
		'template_code'=>$templateCode
		, 'sms_param'=>$sms_param
		, 'phone'=>$phone
		, 'signname'=>$signname
		);

		try
		{
			$client= new \GearmanClient();
			$client->addServer('127.0.0.1', 4730);
			$client->doBackground('sms_cz', json_encode($gearmanjson));
		}catch(Exception $e)
		{
			self::logger('./log/sms.log', "【Exception】:\n" . var_export($e, true) . "\n" . __LINE__ . "\n");
			return false;
		}
		return true;
	}

	public static function time2str($itime)
	{
		if($itime)
		{
			return date('Y-m-d H:i:s', $itime);
		}
		return false;
	}

	public static function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	public static function output($response)
	{

		header('Cache-Control: no-cache, must-revalidate');
		header("Content-Type: text/plain; charset=utf-8");

		if(isset($_REQUEST['callback']) && $_REQUEST['callback'])
		{
			echo $_REQUEST['callback'].'('.json_encode($response).')';
		}
		else
		{
			echo json_encode($response);
		}
	}

	public static function output_html($html)
	{

		header('Cache-Control: no-cache, must-revalidate');
		header("Content-Type: text/html; charset=utf-8");

		echo ($html);
	}

	public static function encryptMD5($data)
	{
		$content = '';
		if(!$data || !is_array($data))
		{
			return $content;
		}
		ksort($data);
		foreach ($data as $key => $value)
		{
			$content = $content.$key.$value;
		}
		if(!$content)
		{
			return $content;
		}

		return self::sub_encryptMD5($content);
	}

	public static function sub_encryptMD5($content)
	{
		$content = $content.Config::RPC_KEY;
		$content = md5($content);
		if( strlen($content) > 10 )
		{
			$content = substr($content, 0, 10);
		}
		return $content;
	}

	public static function https_request($url, $data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}

	public static function logger($file,$word)
	{
		$fp = fopen($file,"a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y-%m-%d %H:%M:%S",time())."\n".$word."\n\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	public static function get_client_ip()
	{
		$s_client_ip = '';

		if (isset($_SERVER['HTTP_X_REAL_IP']))
		{
			$s_client_ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		elseif ($_SERVER['REMOTE_ADDR'])
		{
			$s_client_ip = $_SERVER['REMOTE_ADDR'];
		}
		elseif (getenv('REMOTE_ADDR'))
		{
			$s_client_ip = getenv('REMOTE_ADDR');
		}
		elseif (getenv('HTTP_CLIENT_IP'))
		{
			$s_client_ip = getenv('HTTP_CLIENT_IP');
		}
		else
		{
			$s_client_ip = 'unknown';
		}
		return $s_client_ip;
	}


	public static function getDB()
	{
		//单例
		if( empty(self::$db_instance) )
		{
			self::$db_instance = new \mysqli(Config::DB_HOST, Config::DB_USERNAME, Config::DB_PASSWD, Config::DB_DBNAME, Config::DB_PORT);
			if(empty(self::$db_instance) || !self::$db_instance->ping())
			{
				@self::$db_instance->close();
				if (!self::$db_instance->real_connect(Config::DB_HOST, Config::DB_USERNAME, Config::DB_PASSWD, Config::DB_DBNAME, Config::DB_PORT))
				{
					return false;
				}
			}
			self::$db_instance->query("set names 'utf8'");
			mb_internal_encoding('utf-8');
		}

		return  self::$db_instance;
	}

	public static function execute_sql_backend($rawsqls)
	{
		$result_arr = null;
		$is_rollback = false;

		if(!$rawsqls || !is_array($rawsqls))
		{
			return $result_arr;
		}

		$db_connect = self::getDB();
		$db_connect->autocommit(false);
		foreach ($rawsqls as $item_sql)
		{
			$result = null;
			$result = $db_connect->query($item_sql);
			if(!$result)
			{
				if($db_connect->rollback())
				{
					$is_rollback = true;
				}
				else
				{
					$db_connect->rollback();
					$is_rollback = true;
				}
				$result_arr = null;
				break;
			}
			if($db_connect->insert_id)
			{
				$result_arr[] = array('result'=>$result, 'insert_id'=>$db_connect->insert_id);
			}
			else
			{
				$result_arr[] = array('result'=>$result);
			}
		}

		if(!$is_rollback)
		{
			$db_connect->commit();
		}
		$db_connect->autocommit(true);
		return $result_arr;
	}

	public static function query_sql_backend($rawsql)
	{
		$db_connect = self::getDB();
		$result = $db_connect->query($rawsql);

		return $result;
	}

	/*
	* @inout $weights : array(1=>20, 2=>50, 3=>100);
	* @putput array
	*/
	public static function w_rand($weights)
	{

		$r = mt_rand(1, array_sum($weights));

		$offset = 0;
		foreach ( $weights as $k => $w )
		{
			$offset += $w;
			if ($r <= $offset)
			{
				return $k;
			}
		}

		return null;
	}

	public static function my_addslashes($str)
	{
		$str = str_replace(array("\r\n", "\r", "\n"), '', $str);
		return addslashes(stripcslashes($str));
	}

	public static function getMC()
	{
	     //单例
		global $gCache;
        $gCache = array();

		if( !isset($gCache['mcobj']) )
		{
			$mcobj = new CatMemcache(Config::MC_SERVERS);
			$gCache['mcobj'] = $mcobj;
		}

		return  $gCache['mcobj'];
	}

	public static function write_xls($data=array(), $title=array(), $filename='report')
	{
		if (PHP_SAPI == 'cli')
		{
			die('This example should only be run from a Web Browser');
		}

		require_once dirname(__FILE__) . '/PHPExcel.php';
		//require_once ('./inc/PHPExcel.php');

		$objPHPExcel = new \PHPExcel();
		//设置文档属性，设置中文会产生乱码，需要转换成utf-8格式！！
		$objPHPExcel->getProperties()->setCreator("ToCar");
		// ->setLastModifiedBy("ToCar")
		// ->setTitle("ToCar")
		// ->setSubject("ToCar")
		// ->setDescription("ToCar")
		// ->setKeywords("ToCar");
		$k = 0;
		foreach ($data as $key => $value)
		{	
			if($k != 0)
			{
				$objPHPExcel->createSheet();
			}

			$objPHPExcel->setActiveSheetIndex($k++);
			$key = substr($key , 0 , strpos($key,'_'));
			$objPHPExcel->getActiveSheet()->setTitle($key);  //sheet名字
			$cols = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//第一行处理
			$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');//合并单元格
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize('18');//设置字体大小
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置粗体
			$objPHPExcel->getActiveSheet()->setCellValue('A1', $key."下级人员明细");   //第一行  文字  
			$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('f69697');//背景颜色

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('19');//列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('19');

			/*$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getTop()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);*/

			//文字居中
			$objPHPExcel->getActiveSheet()->getStyle("A1:G1")->applyFromArray(array
				(
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
					
				));
			//第二行标题样式
			for($i=0,$length=count($title); $i<$length; $i++) {
				$objPHPExcel->getActiveSheet()->setCellValue($cols{$i}.'2', $title[$i]);
			}
			//设置标题样式
			$titleCount = count($title);
			$r = $cols{0}.'2';
			$c = $cols{$titleCount}.'2';
			$objPHPExcel->getActiveSheet()->getStyle("$r:$c")->applyFromArray(
				array
				(
					'font'  => array
					(
						'bold'   => true
					),
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'borders' => array
					(
						'top'   => array
						(
							'style' => \PHPExcel_Style_Border::BORDER_THIN
						)
					),
					'fill' => array
					(
						'type'    => \PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
						'rotation'  => 90,
						'startcolor' => array
						(
							'argb' => 'FFA0A0A0'
						),
						'endcolor'  => array
						(
							'argb' => 'FFFFFFFF'
						)
					)
				)
			);
			$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFill()->getStartColor()->setRGB('b6b6b6');//背景颜色
			$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
			$i = 0;
			foreach($value as $key=> $d) {
				$j = 0;

				foreach($d as $v)
				{
					$objPHPExcel->getActiveSheet()->setCellValue($cols{$j}.($i+3), $v,\PHPExcel_Cell_DataType::TYPE_STRING);
					//$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode("@");
					//$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
					$j++;
				}
				$i++;
			}

			
		}

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	public static function write_income_xls($data=array(), $title=array(), $filename='report',$first_name)
	{
		if (PHP_SAPI == 'cli')
		{
			die('This example should only be run from a Web Browser');
		}

		require_once dirname(__FILE__) . '/PHPExcel.php';
		//require_once ('./inc/PHPExcel.php');

		$objPHPExcel = new \PHPExcel();
		//设置文档属性，设置中文会产生乱码，需要转换成utf-8格式！！
		$objPHPExcel->getProperties()->setCreator("ToCar");
		// ->setLastModifiedBy("ToCar")
		// ->setTitle("ToCar")
		// ->setSubject("ToCar")
		// ->setDescription("ToCar")
		// ->setKeywords("ToCar");
		$k = 0;
		foreach ($data as $key => $value)
		{	
			if($k != 0)
			{
				$objPHPExcel->createSheet();
			}

			$objPHPExcel->setActiveSheetIndex($k++);
			//$key = substr($key , 0 , strpos($key,'_'));
			$objPHPExcel->getActiveSheet()->setTitle($key);  //sheet名字
			$cols = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//第一行处理
			$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');//合并单元格
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize('18');//设置字体大小
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置粗体
			$objPHPExcel->getActiveSheet()->setCellValue('A1', $first_name);   //第一行  文字  
			$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('f69697');//背景颜色

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('19');//列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('19');

			/*$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getTop()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);*/

			//文字居中
			$objPHPExcel->getActiveSheet()->getStyle("A1:G1")->applyFromArray(array
				(
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
					
				));
			//第二行标题样式
			for($i=0,$length=count($title); $i<$length; $i++) {
				$objPHPExcel->getActiveSheet()->setCellValue($cols{$i}.'2', $title[$i]);
			}
			//设置标题样式
			$titleCount = count($title);
			$r = $cols{0}.'2';
			$c = $cols{$titleCount}.'2';
			$objPHPExcel->getActiveSheet()->getStyle("$r:$c")->applyFromArray(
				array
				(
					'font'  => array
					(
						'bold'   => true
					),
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'borders' => array
					(
						'top'   => array
						(
							'style' => \PHPExcel_Style_Border::BORDER_THIN
						)
					),
					'fill' => array
					(
						'type'    => \PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
						'rotation'  => 90,
						'startcolor' => array
						(
							'argb' => 'FFA0A0A0'
						),
						'endcolor'  => array
						(
							'argb' => 'FFFFFFFF'
						)
					)
				)
			);
			$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFill()->getStartColor()->setRGB('b6b6b6');//背景颜色
			$objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
			$i = 0;
			foreach($value as $key=> $d) {
				$j = 0;

				foreach($d as $v)
				{
					$objPHPExcel->getActiveSheet()->setCellValue($cols{$j}.($i+3), $v,\PHPExcel_Cell_DataType::TYPE_STRING);
					//$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode("@");
					$j++;
				}
				$i++;
			}

			
		}

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	public static function write_income_list_xls($data=array(), $title=array(), $filename='report',$first_name)
	{
		if (PHP_SAPI == 'cli')
		{
			die('This example should only be run from a Web Browser');
		}

		require_once dirname(__FILE__) . '/PHPExcel.php';
		//require_once ('./inc/PHPExcel.php');

		$objPHPExcel = new \PHPExcel();
		//设置文档属性，设置中文会产生乱码，需要转换成utf-8格式！！
		$objPHPExcel->getProperties()->setCreator("ToCar");
		// ->setLastModifiedBy("ToCar")
		// ->setTitle("ToCar")
		// ->setSubject("ToCar")
		// ->setDescription("ToCar")
		// ->setKeywords("ToCar");
		$k = 0;
		foreach ($data as $key => $value)
		{	
			if($k != 0)
			{
				$objPHPExcel->createSheet();
			}

			$objPHPExcel->setActiveSheetIndex($k++);
			//$key = substr($key , 0 , strpos($key,'_'));
			$objPHPExcel->getActiveSheet()->setTitle($key);  //sheet名字
			$cols = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//第一行处理
			$objPHPExcel->getActiveSheet()->mergeCells('A1:K1');//合并单元格
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize('18');//设置字体大小
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置粗体
			$objPHPExcel->getActiveSheet()->setCellValue('A1', $first_name);   //第一行  文字  
			$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->getStartColor()->setRGB('f69697');//背景颜色

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('19');//列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('30');
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('30');
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('30');

			/*$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getTop()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);*/

			//文字居中
			$objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray(array
				(
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
					
				));
			//第二行标题样式
			for($i=0,$length=count($title); $i<$length; $i++) {
				$objPHPExcel->getActiveSheet()->setCellValue($cols{$i}.'2', $title[$i]);
			}
			//设置标题样式
			$titleCount = count($title);
			$r = $cols{0}.'2';
			$c = $cols{$titleCount}.'2';
			$objPHPExcel->getActiveSheet()->getStyle("$r:$c")->applyFromArray(
				array
				(
					'font'  => array
					(
						'bold'   => true
					),
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'borders' => array
					(
						'top'   => array
						(
							'style' => \PHPExcel_Style_Border::BORDER_THIN
						)
					),
					'fill' => array
					(
						'type'    => \PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
						'rotation'  => 90,
						'startcolor' => array
						(
							'argb' => 'FFA0A0A0'
						),
						'endcolor'  => array
						(
							'argb' => 'FFFFFFFF'
						)
					)
				)
			);
			$objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getFill()->getStartColor()->setRGB('b6b6b6');//背景颜色
			//$objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
			//$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
			//$objPHPExcel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);//防止用科学计数法显示数据
			
			$i = 0;
			foreach($value as $key=> $d) {
				$j = 0;

				foreach($d as $v)
				{
					$objPHPExcel->getActiveSheet()->setCellValue($cols{$j}.($i+3), ' '.$v,\PHPExcel_Cell_DataType::TYPE_STRING);
					//$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode("@");
					$j++;
				}
				$i++;
			}

			
		}

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	public static function write_agent_buy_xls($data=array(), $title=array(), $filename='report', $is_all_agent = 0,$tmp_name = null)
	{
		if (PHP_SAPI == 'cli')
		{
			die('This example should only be run from a Web Browser');
		}

		require_once dirname(__FILE__) . '/PHPExcel.php';
		//require_once ('./inc/PHPExcel.php');

		$objPHPExcel = new \PHPExcel();
		//设置文档属性，设置中文会产生乱码，需要转换成utf-8格式！！
		$objPHPExcel->getProperties()->setCreator("ToCar");
		// ->setLastModifiedBy("ToCar")
		// ->setTitle("ToCar")
		// ->setSubject("ToCar")
		// ->setDescription("ToCar")
		// ->setKeywords("ToCar");
		$k = 0;
		foreach ($data as $key => $value)
		{	
			if($k != 0)
			{
				$objPHPExcel->createSheet();
			}

			$objPHPExcel->setActiveSheetIndex($k++);
			//$key = substr($key , 0 , strpos($key,'_'));
			$objPHPExcel->getActiveSheet()->setTitle($key);  //sheet名字
			$cols = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			//第一行处理
			$objPHPExcel->getActiveSheet()->mergeCells('A1:K1');//合并单元格
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize('18');//设置字体大小
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置粗体
			if($is_all_agent == 0 )
			{
				$objPHPExcel->getActiveSheet()->setCellValue('A1', $key."充值明细");   //第一行  文字  
			}
			elseif($tmp_name != null)
			{
				$objPHPExcel->getActiveSheet()->setCellValue('A1', Config::NAME.'共'.$is_all_agent.$tmp_name);   //第一行  文字  
			}
			else
			{
				$objPHPExcel->getActiveSheet()->setCellValue('A1', Config::NAME.'共'.$is_all_agent."名全部代理明细");   //第一行  文字  
			}

			$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->getStartColor()->setRGB('f69697');//背景颜色

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('19');//列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('19');
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth('30');

			/*$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getTop()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
			$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);*/

			//文字居中
			$objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray(array
				(
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
					
				));
			//第二行标题样式
			for($i=0,$length=count($title); $i<$length; $i++) {
				$objPHPExcel->getActiveSheet()->setCellValue($cols{$i}.'2', $title[$i]);
			}
			//设置标题样式
			$titleCount = count($title);
			$r = $cols{0}.'2';
			$c = $cols{$titleCount}.'2';
			$objPHPExcel->getActiveSheet()->getStyle("$r:$c")->applyFromArray(
				array
				(
					'font'  => array
					(
						'bold'   => true
					),
					'alignment' => array
					(
						'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'borders' => array
					(
						'top'   => array
						(
							'style' => \PHPExcel_Style_Border::BORDER_THIN
						)
					),
					'fill' => array
					(
						'type'    => \PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
						'rotation'  => 90,
						'startcolor' => array
						(
							'argb' => 'FFA0A0A0'
						),
						'endcolor'  => array
						(
							'argb' => 'FFFFFFFF'
						)
					)
				)
			);
			$objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getFill()->getStartColor()->setRGB('b6b6b6');//背景颜色
			$objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
			$objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
			$i = 0;
			foreach($value as $key=> $d) {
				$j = 0;

				foreach($d as $v)
				{
					$objPHPExcel->getActiveSheet()->setCellValue($cols{$j}.($i+3), $v,\PHPExcel_Cell_DataType::TYPE_STRING);
					//$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode("@");
					//$objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
					//$objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);//防止用科学计数法显示数据
				
					$j++;
				}
				$i++;
			}

			
		}

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	public static function decryptRandAuth($authKey, $data)
    {
        $content = self::handleDecrypt(base64_decode($data), $authKey);
        return $content;
    }

    public static function encryptRandAuth($authKey, $data)
    {
        $content = base64_encode(self::handleDecrypt($data, $authKey));
        return $content;
    }

    public static function handleDecrypt($data, $key)
    {
        $encrypt_key = substr(md5($key), 6, 8);
        $ctr = 0;
        $content = '';
        $len_key = strlen($encrypt_key);
        $len_data = strlen($data);
        for( $i = 0; $i < $len_data; $i++ )
        {
            $ctr = ($ctr == $len_key) ? 0 : $ctr;
            $content .= $data[$i] ^ $encrypt_key[$ctr++];
        }
        return $content;
    }



}