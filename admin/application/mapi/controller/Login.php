<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\mapi\controller;
use think\Db;
use app\mapi\controller\Common;

/**
 * Description of LoginApi
 *
 * @author Administrator
 */
class Login extends Common{
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
     * 商户端登录接口
     * 传递参数：
     * 手机号：phone  varchar
     * 密码：password  varchar
     * 验证码：code
     * 手机系统:system_type 1安卓 2苹果
     */
    public function login(){
        $data = input();
        $userInfo = Db::name('SystemUser')->where(['authorize' => 2,'username' => $data['phone']])->find();
        if($userInfo){
            //账号密码登录
            if($userInfo['password'] != md5($data['password'])){
                return $this->showMsg(-1,"账号密码错误");
            }else if($userInfo['status'] == 0){
                return $this->showMsg(-1,"账号已禁用");
            }else{
                $timestamp = time();
                $token = md5($userInfo['username'].$userInfo['id'].$timestamp);
                $is_token = Db::name('StoreToken')->where(['user_type' => 1,'user_id' => $userInfo['id']])->find();
                if($is_token){
                    Db::name('StoreToken')->where(['user_type' => 1,'user_id' => $userInfo['id']])->update(['token' => $token]);
                }else{
                    $tokeninfo = array(
                        "user_id" => $userInfo['id'],
                        "token" => $token,
                        "user_type" => 1
                    );
                    $res = Db::name('StoreToken')->insertGetId($tokeninfo); 
                }
                $login_num = $userInfo['login_num'] + 1;
                Db::name('SystemUser')->where(['id' => $userInfo['id']])->update(['login_num' => $login_num,'system_type' => $data['system_type']]);
                //调用登录记录方法
                $res = $this->loginJournal($userInfo['id'], $data['system_type'],1);
                return $this->showMsg(1,"登录成功",$token);
            }
        }else{
            return $this->showMsg(-1,"账号错误");
        }
    }
}