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
class User extends Base{
   //获取用户信息
    public function getUserInfo(){
        $id = $this->uid;
        $info['data'] = Db::name('StoreMember')->field('phone,realname,region,nickname,headimg,inviter_code,shop_id')->where(['id' => $id])->find();
        if(empty($info['data']['realname'])){
            $info['data']['realname'] = '';
        }
        $info['is_shap'] = 1;  //未开通店铺
        $info['shap_code'] = '';
        if($info['data']['shop_id'] > 0){
            $info['is_shap'] = 2; //开通店铺
            $shop = Db::name('StoreShop')->field('id,title,cover_image,qr_code')->where(['id' => $info['data']['shop_id']])->find();
            $info['shop_id'] = $shop['id'];
            $info['shop_name'] = $shop['title'];
            $info['shop_code'] = $shop['qr_code'];
            $info['shop_img'] = $shop['cover_image'];
        }
        $shop_view_count = Db::name('StoreShopView')->where(['uid' => $id])->count(); // 展厅浏览统计
        $goods_view_count = Db::name('StoreGoodsView')->where(['uid' => $id])->count(); // 商品浏览统计
        $info['view_count'] = $shop_view_count + $goods_view_count;  //总浏览统计
        $info['goods_collect_count'] = Db::name('StoreGoodsCollect')->where(['uid' => $id,'status'=> 1])->count(); // 商品收藏统计
        $info['shop_count_count'] = Db::name('StoreShopCollect')->where(['uid' => $id,'status'=> 1])->count(); // 展厅收藏统计
        $configinfo = Db::name('SystemConfig')->where(['name' => "app_customertel"])->find();
        $info['customertel'] = $configinfo['value'];
        return $this->showMsg(1,'查询成功',$info);
    }
    
    //修改个人资料
    public function saveUserInfo(){
        $user_id = $this->uid;
        $data = input();
        $info = Db::name('StoreMember')->field('id')->where('phone', 'eq', $data['info']['phone'])->find();
        if($info){
	        if($info['id'] != $user_id){
	            return $this->showMsg(-1,'手机号已存在');
	        }
        }
        $res = Db::name('StoreMember')->where('id', 'eq', $user_id)->update($data['info']);
        $this->UserOperation($user_id, "修改个人信息");
        return $this->showMsg(1,'修改成功');
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
        $info = Db::name('StoreMember')->field('password')->where(['id' => $id])->find();
        if(md5($data['usedpassword']) != $info['password']){
            return $this->showMsg(-1,'原密码不正确');
        }
        $res = Db::name('StoreMember')->where(['id' => $id])->update(['password' => md5($data['password'])]);
        //添加操作记录
        $this->UserOperation($id, "修改账号密码");
        return $this->showMsg(1,'修改成功');
    }

    //获取关于我们信息
    public function getExplainInfo(){
        $data = input();
        $info = Db::name('SystemExplain')->where('key_name = "'.$data['key_name'].'"')->find();
        return $this->showMsg(1,'',$info);
    }
    
    /**
     * 添加意见反馈
     */
    public function addFeedback(){
        $user_id = $this->uid;
        $data = input();
        $info = array(
            "user_id" => $user_id,
            "platform_type" => 2,
            "content" => $data['content'],
            "addtime" => date("Y-m-d H:i:s", time())
        );
        $res = Db::name('StoreFeedback')->insertGetId($info);
        if($res){
            //添加操作记录
            $res1 = $this->UserOperation($user_id, "提交意见反馈");
            return $this->showMsg(1, "提交成功");
        }
        return $this->showMsg(-1, "提交失败");
    }
    
    //展厅浏览接口
    public function  shopViewList(){
        $data = input();
        $uid = $this->uid;
        $page = $data['page'];
        $pagesize = 7;
        $shopList = Db::name('StoreShopView')->alias("sv")
                ->leftJoin("store_shop s","sv.shop_id = s.id")
                ->field("sv.shop_id,s.title,s.cover_image,s.labels,s.view_num,s.like_num,s.collect_num,s.address,s.longitude,s.latitude,s.province,s.city,s.area")
                ->where('sv.uid = '.$uid)
                ->order('sv.create_at desc')
                ->page($page,$pagesize)
                ->select();
        if($shopList){
            foreach ($shopList as $key=>$val){
            	$shopList[$key]['is_life'] = Db::name('StoreShop')->where(['id'=>$val['shop_id'],'is_delete'=>2,'is_freeze'=>2])->count();
            	if($val['labels']){
                     $shopList[$key]['tag'] = explode(',',$val['labels']);
                }
                $shopList[$key]['location'] =$val['province'].$val['city'].$val['area'].$val['address'];
            }
        }
        $list['data'] = $shopList;
        $list['pagesize'] = $pagesize;
        return $this->showMsg(1,'',$list);
    }
    
    //商品浏览接口
    public function  goodsViewList(){
        $data = input();
        $uid = $this->uid;
        $page = $data['page'];
        $pagesize = 7;
        $list['data'] = Db::name('StoreGoodsView')->alias("gv")
                ->leftJoin("store_goods g","gv.goods_id = g.id")
                ->leftJoin("store_shop s","s.id = g.shop_id")
                ->field("gv.goods_id,g.title,g.cover_image,g.view_num,g.like_num,g.collect_num,s.title as shop_name")
                ->where('gv.uid = '.$uid)
                ->order('gv.create_at desc')
                ->page($page,$pagesize)
                ->select();
        foreach ($list['data'] as $key=>$vo){
        	$list['data'][$key]['is_life'] = Db::name('StoreGoods')->where(['id'=>$vo['goods_id'],'is_delete'=>2,'is_show_main'=>1,'is_show_second'=>1])->count();
        }        
        $list['pagesize'] = $pagesize;
        return $this->showMsg(1,'',$list);
    }
    
    //展厅收藏接口
    public function  shopCollectList(){
        $data = input();
        $uid = $this->uid;
        $page = $data['page'];
        $pagesize = 7;
        $shopList = Db::name('StoreShopCollect')->alias("sc")
                ->leftJoin("store_shop s","sc.shop_id = s.id")
                ->field("sc.id,sc.shop_id,s.title,s.cover_image,s.labels,s.view_num,s.like_num,s.collect_num,s.address,s.longitude,s.latitude,s.province,s.city,s.area")
                ->where('sc.status = 1 and sc.uid = '.$uid)
                ->order('sc.create_at desc')
                ->page($page,$pagesize)
                ->select();
        if($shopList){
            foreach ($shopList as $key=>$val){
            	$shopList[$key]['is_life'] = Db::name('StoreShop')->where(['id'=>$val['shop_id'],'is_delete'=>2,'is_freeze'=>2])->count();
            	if($val['labels']){
            		 $shopList[$key]['tag'] = explode(',',$val['labels']);
            	}
                $shopList[$key]['checked'] = false;
                $shopList[$key]['location'] =$val['province'].$val['city'].$val['area'].$val['address'];
            }
        }
        $list['data'] = $shopList;
        $list['pagesize'] = $pagesize;
        return $this->showMsg(1,'',$list);
    }
    
    //展厅收藏取消接口
    public function shopCollectCancel(){
        $data = input();
        $ids = $data['ids'];
        $ids = rtrim($data['ids'], "|");
        $arr_id = explode("|", $ids);
        foreach ($arr_id as $val){
             Db::name('StoreShopCollect')->where('id = '.$val)->update(['status'=>2]);
        }
        return $this->showMsg(1,'操作成功');
    }


    //商品收藏接口
    public function  goodsCollectList(){
        $data = input();
        $uid = $this->uid;
        $page = $data['page'];
        $pagesize = 7;
        $goodsList = Db::name('StoreGoodsCollect')->alias("gc")
                ->leftJoin("store_goods g","gc.goods_id = g.id")
                ->leftJoin("store_shop s","s.id = g.shop_id")
                ->field("gc.id,gc.goods_id,g.title,g.cover_image,g.view_num,g.like_num,g.collect_num,s.title as shop_name")
                ->where('gc.status = 1 and gc.uid = '.$uid)
                ->order('gc.create_at desc')
                ->page($page,$pagesize)
                ->select();
        if($goodsList){
            foreach ($goodsList as $key=>$val){
            		$goodsList[$key]['is_life'] = Db::name('StoreGoods')->where(['id'=>$val['goods_id'],'is_delete'=>2,'is_show_main'=>1,'is_show_second'=>1])->count();
                $goodsList[$key]['checked'] = false;
            }
        }
        $list['data'] = $goodsList;
        $list['pagesize'] = $pagesize;
        return $this->showMsg(1,'',$list);
    }
    
    //展厅收藏取消接口
    public function goodsCollectCancel(){
        $data = input();
        $ids = $data['ids'];
        $ids = rtrim($data['ids'], "|");
        $arr_id = explode("|", $ids);
        foreach ($arr_id as $val){
             Db::name('StoreGoodsCollect')->where('id = '.$val)->update(['status'=>2]);
        }
        return $this->showMsg(1,'操作成功');
    }
    
    //展厅分享接口
    public function shopShare(){
        $data = input();
        Db::name('StoreShopShare')->insert([
            'shop_id'=>$data['shop_id'],
            'uid'=>$this->uid
        ]);
        Db::name('StoreShop')->where(['id'=>$data['shop_id']])->setInc('share_num');
        $this->showMsg(1,'记录成功');
    }
    
    //产品分享接口
    public function goodsShare(){
        $data = input();
        Db::name('StoreGoodsShare')->insert([
            'goods_id'=>$data['goods_id'],
            'uid'=>$this->uid
        ]);
        Db::name('StoreGoods')->where(['id'=>$data['goods_id']])->setInc('share_num');
        $this->showMsg(1,'记录成功');
    }

    //展厅电话被拨打接口
    public function shopCall(){
        $data = input();
        Db::name('StoreShopCall')->insert([
            'shop_id'=>$data['shop_id'],
            'uid'=>$this->uid
        ]);
        Db::name('StoreShop')->where(['id'=>$data['shop_id']])->setInc('call_num');
        $this->showMsg(1,'记录成功');
    }
}