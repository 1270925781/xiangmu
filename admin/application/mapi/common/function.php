<?php
/**
 * 发送验证码
 * @param string $phone
 * @return number
 */
function send_sms($phone, $msg = '',$type ){
    $typename = '';
    if($type == 1){
        $res = M('user')->where(['username' => $phone])->find();
        if(!$res){
            return -2;
        }
        if($res['is_freeze'] != 2){
            return -4;
        }
        $typename = '登录';
    }
    if($type == 2){
        $typename = '注册';
    }
    if($type == 3){
        $typename = '修改密码';
    }
    if($type == 4){
        $typename = '换绑手机号';
    }
    if($type == 5){
        $typename = '绑定手机号';
    }
    $sms = new \Org\Util\ChuanglanSmsApi();
    $code = mt_rand(100000, 999999);
    if($msg){
        $message = $msg;
    } else {
        $message = '【' . C('SMS_SINATURE') . '】您正在'.$typename.'，短信验证码(' .$code.'),30分钟内有效。请勿泄露给他人';
    }
    $res = M('sms')->add(['s_phone' => $phone, 's_code' => $code, 's_message' => $message, 's_addtime' => time(), 's_type' => $type]);
    if(!$res){
        return -10;
    }
    $result = $sms->sendSMS($phone, $message);
    if(!is_null(json_decode($result))){
        $output = json_decode($result, true);
        if(isset($output['code'])  && $output['code'] == '0'){
            return ['status' =>5, 'code' =>$code];
        }else{
            return -10;
        }
    }else{
        return -10;
    }
}

/**
 * 验证码是否有效
 * @param string $phone
 * @param string $code
 * @return number
 */
function verify_sms($phone, $code, $type){
    $where = 's_phone = '.$phone.' and s_code = '.$code.' and is_use = 2 and s_type = '.$type;
    $res = M('sms')->field('s_addtime')->where($where)->order('s_addtime DESC')->find();
    if($res){
        $info['is_use'] = 1;
        M('sms')->where($where)->save($info);
        $time = time() - C('SMS_OVER_TIME');
        if($res['s_addtime'] > $time){
            return 1;
        }else{
            return -11;
        }
    }else{
        return -18;
    }
}