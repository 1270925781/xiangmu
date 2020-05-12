<?php
/**
 * 公共函数文件
 * @param string $node
 * @return boolean
 */

use org\Qrcode;
use think\facade\Env;

if (!function_exists('alisendsms')) {
    /**
     * 阿里云短信发送
     *
     * @param  string    $PhoneNumbers 手机号码
	 * @param  string    $TemplateCode 短信模板ID
     * @param  mixed     $TemplateParam  短信模板变量 {"code":"1111"}
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    function alisendsms($PhoneNumbers, $TemplateCode,$TemplateParam)
    {
		$Template=array(
			'SMS_186430066'=>array('SMS_186430066','身份验证验证码','验证码${code}，您正在进行身份验证，打死不要告诉别人哦！'),
			'SMS_186430065'=>array('SMS_186430065','登录确认验证码','验证码${code}，您正在登录，若非本人操作，请勿泄露。'),
			'SMS_186430064'=>array('SMS_186430064','登录异常验证码','验证码${code}，您正尝试异地登录，若非本人操作，请勿泄露。'),
			'SMS_186430063'=>array('SMS_186430063','用户注册验证码','验证码${code}，您正在注册成为新用户，感谢您的支持！'),
			'SMS_186430062'=>array('SMS_186430062','修改密码验证码','验证码${code}，您正在尝试修改登录密码，请妥善保管账户信息。'),
			'SMS_186430061'=>array('SMS_186430061','信息变更验证码','验证码${code}，您正在尝试变更重要信息，请妥善保管账户信息。'),
			'SMS_189017985'=>array('SMS_189017985','您申请的展厅已通过审核。商户端登录账号：${username}，密码：${password}，请尽快前往商户后台修改密码。'),
		);
		$SignName=sysconf('ali_signname');
		$AccessKeyID=sysconf('ali_accesskeyid');
		$AccessKeySecret=sysconf('ali_accesskeysecret');
		$SignatureNonce='yshl_'.time();
		$Signature="";//请求签名
		$Params=array(
			'PhoneNumbers'=>$PhoneNumbers,
			'SignName'=>$SignName,
			'TemplateCode'=>$TemplateCode,
			'TemplateParam'=>$TemplateParam,
			'Signature'=>$Signature,
			'AccessKeyId'=>$AccessKeyID,
			'Action'=>'SendSms',
			'Format'=>'json',
			'RegionId'=>'cn-hangzhou',
			'SignatureMethod'=>'HMAC-SHA1',
			'SignatureNonce'=>$SignatureNonce,
			'SignatureVersion'=>'1.0',
			'Timestamp'=>gmdate("Y-m-d\TH:i:s\Z"),
			'Version'=>'2017-05-25',			
			'OutId'=>'yunshanghulian'
		);
		unset($Params['Signature']);
		ksort($Params);
		foreach($Params as $k=>$v){
			$Paramsurl[]=str_replace(array("+","*","%7E"),array("%20","%2A","~"),urlencode($k))."=".str_replace(array("+","*","%7E"),array("%20","%2A","~"),urlencode($v));
		}
		$Param_=join("&",$Paramsurl);//数组转字符串
		$Param_str="GET&".str_replace(array("+","*","%7E"),array("%20","%2A","~"),urlencode("/"))."&".str_replace(array("+","*","%7E"),array("%20","%2A","~"),urlencode($Param_));
		$Signature = base64_encode(hash_hmac("sha1", $Param_str, $AccessKeySecret."&", true));
		$Params['Signature']=$Signature;//将签名接入请求字符串
		//请求字符串
		$QueryString="Signature=".str_replace(array("+","*","%7E"),array("%20","%2A","~"),urlencode($Signature))."&".$Param_;
        $QueryUrl="http://dysmsapi.aliyuncs.com/?".$QueryString;
		//dump($QueryUrl);
		//发送GET请求
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $QueryUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);
		$output=json_decode($output,true);
		if(strtolower($output['Code'])=='ok'){
			return $output['Message'];
		}else{
			return $output['Message'];
		}
    }
}

/**
 * 唯一数字编码
 * @param integer $length
 * @return string
 */
function uniqidNumberCode($length = 15)
{
	$time = time() . '';
	if ($length < 10) $length = 10;
	$string = ($time[0] + $time[1]) . substr($time, 2) . rand(0, 9);
	while (strlen($string) < $length) $string .= rand(0, 9);
	return $string;
}

/**
 * 唯一日期编码
 * @param integer $length
 * @return string
 */
function uniqidDateCode($length = 14)
{
	if ($length < 14) $length = 14;
	$string = date('Ymd') . (date('H') + date('i')) . date('s');
	while (strlen($string) < $length) $string .= rand(0, 9);
	return $string;
}

/**
 * 生成32位订单号
 * @return string
 */
function createOrderId(){
    $d = explode(" ", microtime());
    return date('YmdHis').substr($d[0], 2);
}


function uuid2($prefix = "") {
	mt_srand ( ( double ) microtime () * 1000000 );
	$uuid =  substr (md5 ( uniqid ( rand (), true ) ),8,16);
	return $prefix.$uuid;
}

/** 
* create_uuid
*
* uuid生成字符串辅助函数 
*
*/
function create_uuid($prefix = "arpz~"){    //可以指定前缀
    return $prefix.md5(uniqid(mt_rand(),true)).mt_rand(1000,9999);
}


/**
 * __mkdirs
 *
 * 循环建立目录的辅助函数
 *
 * @param dir    目录路径
 * @param mode    文件权限
 */
function __mkdirs($dir, $mode = 0777) {
    if (!is_dir($dir)) {
        __mkdirs(dirname($dir), $mode);
        @mkdir($dir, $mode);
        return true;
    }
    return true;
}

//生成原始的二维码(不生成图片文件) 
//https://www.cnblogs.com/endv/p/8647611.html
//$abc=qrcodepng($url='http://baidu.com',$filename="qrcode.png");
function qrcodepng($url='',$filename="qrcode.png"){
	$value = $url;                  //二维码内容  
	$errorCorrectionLevel = 'L';    //容错级别   
	$matrixPointSize = 5;           //生成图片大小    
	//生成二维码图片  
	$QRcode=new QRcode();	
	$filename=Env::get('root_path').'public/qr_code/'.date('Ymd',time()).'/'.$filename;
	if(!file_exists($filename)){
		__mkdirs(pathinfo($filename, PATHINFO_DIRNAME));
		$QRcode::png($value,$filename,$errorCorrectionLevel, $matrixPointSize, 2); 
	}
	$data=array(
		'url'=>Config::get('web_url').str_replace(Env::get('root_path').'public/',"",$filename),
		'path'=>$filename,
	);
	return $data;
} 