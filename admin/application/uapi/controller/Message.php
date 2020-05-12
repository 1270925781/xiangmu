<?php
namespace app\uapi\controller;
use think\Db;

class Message extends Base
{
    /**
     * 获取用户未读消息条数
     */
    public function getCount(){
    	$uid = $this->uid;
        $create_at = Db::name('StoreMember')->field('create_at')->where('id = '.$uid)->find()['create_at'];
        $where = "is_delete=2 and type=2 and is_push=1 and push_obj=2 and updatetime>='$create_at'";
    	$messageList = Db::name('StoreMessage')->field('id')->where($where)->select();
    	$allCount = count($messageList);
    	$readCount = 0;
    	foreach ($messageList as &$vo){
        	$readCount += Db::name('StoreMessageRead')->where(['uid'=>$uid,'mid'=>$vo['id']])->count();
    	}
    	$unreadCount = $allCount-$readCount;
    	if($unreadCount == 0){
    		$unreadCount = -1;
    	}
    	return $this->showMsg(1,'获取成功',$unreadCount);
    }
    
    /**
     * 获取用户消息列表
     */
    public function messageList(){
        $data = input();
        $uid = $this->uid;
        $page = $data['page'];
        $pagesize = 12;
        $uinfo = Db::name('StoreMember')->field('create_at')->where('id = '.$uid)->find();
        $list['data'] = Db::name('StoreMessage')
                ->field('id,title,updatetime')
                ->where('type = 2 and push_obj = 2 and is_push = 1 and is_delete = 2 and updatetime >= "'.$uinfo['create_at'].'"')
                ->order('id desc')
                ->page($page,$pagesize)
                ->select();
        if($list['data']){
            foreach ($list['data'] as $key=>$val){
                $res = Db::name('StoreMessageRead')
                ->where('uid = '.$uid.' and mid = '.$val['id'])
                ->find();
                if($res){
                    $list['data'][$key]['state'] = 2;
                }else{
                    $list['data'][$key]['state'] = 1;
                }
            }
        }
        $list['pagesize'] = $pagesize;
        return $this->showMsg(1,'',$list);
    }
    
    //获取消息详情
    public function getMessageInfo(){
        $data = input();
        $uid = $this->uid;
        $id = $data['id'];
        $info = Db::name('StoreMessage')
                ->field('id,title,content,jump_type,jump_id,jump_url')
                ->where('id = '.$id)
                ->find();
        $res = Db::name('StoreMessageRead')
                ->where('uid = '.$uid.' and mid = '.$id)
                ->find();
        if($res){
            return $this->showMsg(1,'',$info);
        }else{
            $save['uid'] = $uid;
            $save['mid'] = $id;
            $save['read_time'] = date("Y-m-d H:i:s",time());
            $res = Db::name('StoreMessageRead')->insertGetId($save);
            if($res > 0){
                return $this->showMsg(1,'',$info);
            }else{
                return $this->showMsg(-1);
            }
        }
    }
    //判断是否有新消息
    public function newMessage(){
        $data = input();
        $uid = $this->uid;
        $id = $data['id'];
        $uinfo = Db::name('StoreMember')->field('create_at')->where('id = '.$uid)->find();
        $info = Db::name('StoreMessage')
                ->where('type = 2 and push_obj = 2 and is_push = 1 and is_delete = 2 and updatetime >= "'.$uinfo['create_at'].'"'.' and id > '.$id)
                ->count();
        return $this->showMsg(1,'',$info);
    }
}