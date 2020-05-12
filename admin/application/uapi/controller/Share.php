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
class Share extends Base{
    //获取新闻快讯
    public function getNewsList(){
        $data = input();
        $page = $data['page'];
        $pagesize = 7;
        
        $list['data'] = Db::name('StoreNews')->where('is_delete = 2 and status = 1 and cate_id = 3')->order('sort desc,id desc')->page($page,$pagesize)->select();
        $list['pagesize'] = $pagesize;
        return $this->showMsg(1,'',$list);
    }
    
    //获取新闻快讯详情
    public function getNewsInfo(){
        $data = input();
        $id = $data['id'];
        $info = Db::name('StoreNews')->where('id = '.$id)->find();
        Db::name('StoreNews')->where('id = '.$id)->setInc('view_num');
        return $this->showMsg(1,'',$info);
    }
    
    //判断是否有新的新闻快讯
    public function newNews(){
        $data = input();
        $id = $data['id'];
        $info = Db::name('StoreNews')->where('is_delete = 2 and status = 1 and cate_id = 3 and id > '.$id)->count();
        return $this->showMsg(1,'',$info);
    }
    
    //获取活动/展会列表
    public function getList(){
        $data = input();
        $page = $data['page'];
        $type = $data['type'];
        $pagesize = 5;
        $list['data'] = Db::name('StoreActivities')->where('is_delete = 2 and status = 1 and platform_type = 2 and cate = '.$type)->order('id desc')->page($page,$pagesize)->select();
        if($list['data']){
            foreach ($list['data'] as $key=>$val){
                $list['data'][$key]['start_time'] = date('Y.m.d',$val['start_time']);
            }
        }
        $list['pagesize'] = $pagesize;
        return $this->showMsg(1,'',$list);
    }
    
    //获取活动/展会详情
    public function getInfo(){
        $data = input();
        $id = $data['id'];
        $info = Db::name('StoreActivities')->where('id = '.$id)->find();
        if($info['end_time']>=time()){
            $info['is_end'] = 1;
        }else{
            $info['is_end'] = 2;
        }
        $info['start_time'] = date('Y.m.d',$info['start_time']);
        return $this->showMsg(1,'',$info);
    }
    
    //活动/展会报名
    public function signUp(){
        $data = input();
        $info['uid'] = $this->uid;
        $info['act_id'] = $data['id'];
        $info['platform_type'] = 2;
        $info['linkman'] = $data['linkman'];
        $info['linkphone'] = $data['linkphone'];
        $info['join_num'] = $data['join_num'];
        $info['create_at'] = date("Y-m-d H:i:s",time());
        $sid = Db::name('StoreActivitiesSign')->insertGetId($info);
        if($sid > 0){
            //添加操作记录
            $res1 = $this->UserOperation($this->uid, "活动展会报名");
            return $this->showMsg(1,'报名成功');
        }
        return $this->showMsg(-1,'报名失败');
    }
}