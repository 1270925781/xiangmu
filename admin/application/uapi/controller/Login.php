<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\uapi\controller;
use think\Db;

/**
 * Description of LoginApi
 *
 * @author Administrator
 */
class Login extends Common{
    //put your code here
    
    /**
     * 获取logo
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLogo(){
        $logo = Db::name('SystemConfig')->where(['id'=>6])->find()['value'];
        return $this->showMsg(1,'获取成功',$logo);
    }
    
    /**
     * 用户端登录接口
     * 传递参数：
     * 手机号：phone  varchar
     * 密码：password  varchar
     * 验证码：code
     * 登录类型:login_type 1密码登录 2验证码登录
     * 手机系统:system_type 1安卓 2苹果
     */
    public function login(){
        $data = input();
       
            if($data['login_type'] == 1){
                $userInfo = Db::name('StoreMember')->whereOr([['username','=',$data['phone']],['phone','=',$data['phone']]])->find();
                if($userInfo){
                    if($userInfo['status'] == 1){
                        return $this->showMsg(-1,"账号已禁用");
                    }
                }else{
                    return $this->showMsg(-1,"账号错误");
                }
                //账号密码登录
                if($userInfo['password'] != md5($data['password'])){
                    return $this->showMsg(-1,"账号密码错误");
                }
            }else{
                $userInfo = Db::name('StoreMember')->where('phone = '.$data['phone'])->find();
                if($userInfo){
                    if($userInfo['status'] == 1){
                        return $this->showMsg(-1,"账号已禁用");
                    }
                }else{
                    return $this->showMsg(-1,"此账号不存在");
                }
                //验证码验证
                $res = $this->verifySms($data['phone'], $data['code'],1);
                if($res == 2){
                    return $this->showMsg(-1,'验证码已失效');
                }else if($res == 3){
                    return $this->showMsg(-1,'验证码错误');
                }
            }
            $timestamp = time();
            $token = md5($userInfo['username'].$userInfo['id'].$timestamp);
            $is_token = Db::name('StoreToken')->where(['user_type' => 2,'user_id' => $userInfo['id']])->find();
            if($is_token){
                Db::name('StoreToken')->where(['user_type' => 2,'user_id' => $userInfo['id']])->update(['token' => $token]);
            }else{
                $tokeninfo = array(
                    "user_id" => $userInfo['id'],
                    "token" => $token,
                    "user_type" => 2
                );
                $res = Db::name('StoreToken')->insertGetId($tokeninfo); 
            }
            //调用登录记录方法
            $res = $this->loginJournal($userInfo['id'], $data['system_type'],2);
            return $this->showMsg(1,"登录成功",$token);
    }
    
    //忘记密码
    public function resetpwd(){
        $data = input();
        if(empty($data['phone'])){
            return $this->showMsg(-1,'手机号不能为空');
        }
        if(empty($data['code'])){
            return $this->showMsg(-1,'验证码不能为空');
        }
        if(empty($data['password'])){
            return $this->showMsg(-1,'新密码不能为空');
        }
        //验证码验证
        $res = $this->verifySms($data['phone'], $data['code'],3);
        if($res == 2){
            return $this->showMsg(-1,'验证码已失效');
        }else if($res == 3){
            return $this->showMsg(-1,'验证码错误');
        }
        $password = md5($data['password']);
        $pwd = Db::name('StoreMember')->where("phone = ".$data['phone'])->update(['password' => $password]);
        $ids = Db::name('StoreMember')->field('id')->where("phone = ".$data['phone'])->find();
        if($pwd){
            //添加操作记录
            $res1 = $this->UserOperation($ids['id'], "找回密码");
            return $this->showMsg(1,'重置密码成功');
        }else{
            return  $this->showMsg(-1,'重置密码失败');
        }
    }
    //修改账号密码
    public function updatePassword(){
        $id = $this->uid;
        $data = input();
        //验证码验证
        $res = $this->verifySms($data['phone'], $data['code'],3);
        if($res == 2){
            return $this->showMsg(-1,'验证码已失效');
        }else if($res == 3){
            return $this->showMsg(-1,'验证码错误');
        }
        $info = Db::name('StoreMember')->field('password,phone')->where(['id' => $id])->find();
        if($data['phone'] != $info['phone']){
            return $this->showMsg(-1,'手机号不是该用户的');
        }
        if(md5($data['usedpassword']) != $info['password']){
            return $this->showMsg(-1,'原密码不正确');
        }
        $res = Db::name('StoreMember')->where(['id' => $id])->update(['password' => md5($data['password'])]);
        if($res){
            //添加操作记录
            $res = $this->UserOperation($id, "修改账号密码");
            return $this->showMsg(1,'修改成功');
        }
        return $this->showMsg(-1,'修改失败');
    }
    
    //注册
    public function register(){
        $data = input();
        if(empty($data['phone'])){
            return $this->showMsg(-1,'手机号不能为空');
        }
        if(empty($data['code'])){
            return $this->showMsg(-1,'验证码不能为空');
        }
        if(empty($data['password'])){
            return $this->showMsg(-1,'密码不能为空');
        }
        //验证码验证
        $res = $this->verifySms($data['phone'], $data['code'],2);
        if($res == 2){
            return $this->showMsg(-1,'验证码已失效');
        }else if($res == 3){
            return $this->showMsg(-1,'验证码错误');
        }
        if(!empty($data['inviter_code'])){
            $inviter_id = Db::name('StoreMember')->where(['inviter_code' => $data['inviter_code']])->find();
            if(!$inviter_id){
                return $this->showMsg(-1,'推荐码错误');
            }
            $info['inviter_uid'] = $inviter_id['id'];
        }
        $inviter_code = $this->random();
        $info['username'] = $data['username'];
        $info['password'] = md5($data['password']);
        $info['phone'] = $data['phone'];
        $info['nickname'] = $data['nickname'];
        $info['realname'] = $data['realname']; 
        $info['system_type'] = $data['system_type'];
        $info['inviter_code'] = $inviter_code;
        $info['region'] = $data['region'];
        $info['create_at'] = date('Y-m-d H:i:s',time());
        $uid = Db::name('StoreMember')->insertGetId($info);
        $token = md5($info['username'].$uid.time());
        $tokeninfo = array(
            "user_id" => $uid,
            "token" => $token,
            "user_type" => 2
        );
        $res = Db::name('StoreToken')->insertGetId($tokeninfo);
        //添加操作记录
        $res1 = $this->UserOperation($uid, "新用户注册");
        return $this->showMsg(1,"注册成功",$token);
    }
    
    /**
     * 发送验证码
     * 传递参数：
     * 手机号：phone  varchar
     * 验证码类型：code_type  int 1登录 2注册 3修改密码 4换绑手机号 5绑定手机号
     */
    public function sendSms(){
    	$data = input();
        $type = array_key_exists('type', $data) ? $data['type']:0;
        if(($data['code_type'] == 2) || ($data['code_type'] == 4) || ($data['code_type'] == 5)){
            $info = Db::name('StoreMember')->field('id,qq_openid,wx_openid')->where(['phone' => $data['phone']])->find();
            if($info['id'] > 0){
                if($type == 1){
                    if($info['qq_openid'] != ""){
                         return $this->showMsg(-1,"该手机号已占用");
                    }
                }else if($type == 2){
                    if($info['wx_openid'] != ""){
                         return $this->showMsg(-1,"该手机号已占用");
                    }
                }else{
                    return $this->showMsg(-1,"该手机号已占用");
                }
            }
        }
        $code = mt_rand(100000, 999999);
        $TemplateParam = json_encode(array("code"=>$code));
        switch ($data['code_type']){
            case 1:
                $TemplateCode = 'SMS_186430065';
                $message = "验证码".$code."，您正在登录，若非本人操作，请勿泄露。";
                break;
            case 2:
                $TemplateCode = 'SMS_186430063';
                $message = "验证码".$code."，您正在注册成为新用户，感谢您的支持！";
                break;
            case 3:
                $TemplateCode = 'SMS_186430062';
                $message = "验证码".$code."，您正在尝试修改登录密码，请妥善保管账户信息。";
                break;
            case 4:
                $TemplateCode = 'SMS_186430066';
                $message = "验证码".$code."，您正在进行身份验证，打死不要告诉别人哦！";
                break;
            case 5:
                $TemplateCode = 'SMS_186430066';
                $message = "验证码".$code."，您正在进行身份验证，打死不要告诉别人哦！";
                break;
        }
        
        $res = alisendsms($data['phone'], $TemplateCode,$TemplateParam);
        if(($res == 1) || ($res == "OK")){
            $res = Db::name('StoreSms')->insert(['phone' => $data['phone'], 'code' => $code, 'message' => $message, 'addtime' => date('Y-m-d H:i:s',time()), 'type' => $data['code_type']]);   
            return $this->showMsg(1,"发送成功",$res);
        }
        return $this->showMsg(-1,"发送失败，请核实手机号",$res);
    }
    /**
     * 验证QQ,微信是否登录过
     * type  1qq 2微信
     */
    public function isTxLogin(){
        $data = input();
        $type = $data['type'];
        if($data['type'] == 1){
            $where = "qq_openid = '".$data['openid']."'";
        }else{
            $where = "wx_openid = '".$data['openid']."'";
        }
        $userInfo = Db::name('StoreMember')->field('id,username,status')->where($where)->find();
        if($userInfo){
        	if($userInfo['status'] == 1){
                return $this->showMsg(-1,"账号已禁用");
            }
            $timestamp = time();
            $token = md5($userInfo['username'].$userInfo['id'].$timestamp);
            Db::name('StoreToken')->where(['user_type' => 2,'user_id' => $userInfo['id']])->update(['token' => $token]);
            //调用登录记录方法
            $res = $this->loginJournal($userInfo['id'], $data['system_type'],2);
            return $this->showMsg(1,"登录成功",array('code'=>1,'token'=>$token));
        }else{
            return $this->showMsg(1,"",array('code'=>-1));
        }
    }
    
    
    /**
     * QQ,微信登录
     */
    public function txLogin(){
        $data = input();
        //验证码验证
        $res = $this->verifySms($data['phone'], $data['code'],5);
      if($res == 2){
            return $this->showMsg(-1,'验证码已失效');
        }else if($res == 3){
            return $this->showMsg(-1,'验证码错误');
        }
        if($data['type'] == 1){
            $info['qq_openid'] = $data['openid'];
        }else if($data['type'] == 2){
            $info['wx_openid'] = $data['openid'];
        }
        $info['system_type'] = $data['system_type'];
        $info['nickname'] = $data['nickname'];
        $info['headimg'] = $data['headimg'];
        $member = Db::name('StoreMember')->field('id,username,status')->where(['phone' => $data['phone']])->find();
        if($member['id'] > 0){
        	if($member['status'] == 1){
                return $this->showMsg(-1,"该手机号已禁用");
            }
            $is_register = 2;
            $uid = $member['id'];
            $username = $member['username'];
            //手机号已存在
            Db::name('StoreMember')->where(['id' => $member['id']])->update($info);
        } else {
            //手机号不存在，新增用户
            $is_register = 1;
            $username = $data['phone'];
            $info['phone'] = $data['phone'];
            $info['username'] = $data['phone'];
            $info['password'] = md5("123456");
            $info['region'] = $data['region'];
            $info['create_at'] = date("Y-m-d H:i:s");
            $inviter_code = $this->random();
            $info['inviter_code'] = $inviter_code;
            $uid = Db::name('StoreMember')->insertGetId($info);
        }
        $token = md5($username.$uid.time());
        $is_token = Db::name('StoreToken')->where(['user_type' => 2,'user_id' => $uid])->find();
        if($is_token){
            Db::name('StoreToken')->where(['user_type' => 2,'user_id' => $uid])->update(['token' => $token]);
        }else{
            $tokeninfo = array(
                "user_id" => $uid,
                "token" => $token,
                "user_type" => 2
            );
            $res = Db::name('StoreToken')->insertGetId($tokeninfo); 
        }
        //调用登录记录方法
        $res = $this->loginJournal($uid, $data['system_type'],2);
        return $this->showMsg(1,"登录成功",["is_register" => $is_register,"token" => $token]);
    }
    
    //获取关于协议内容信息
    public function getExplainInfo(){
        $data = input();
        $info = Db::name('SystemExplain')->where('key_name = "'.$data['key_name'].'"')->find();
        return $this->showMsg(1,'',$info);
    }
}