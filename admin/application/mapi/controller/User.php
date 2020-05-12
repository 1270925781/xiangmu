<?php
namespace app\mapi\controller;
use think\Db;
use think\Request;
use think\File;
use think\facade\Config;
use think\facade\Env;
use Endroid\QrCode\QrCode;

class User extends Base
{
    /**
     * 获取用户信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserInfo(){
        $user_id = $this->uid;
        $data = Db::name('SystemUser')->field('username,nickname,real_name,head_img,phone,region,shop_id')->where('id', 'eq', $user_id)->find();
        $config_info = Db::name('SystemConfig')->field('name,value')->where('name in ("app_customertel","app_customertime")')->select();
        if($data['shop_id']){
        	$shopInfo = Db::name('StoreShop')->field('id,title,qr_code')->where('id','eq',$data['shop_id'])->find();
        }
        $customertel = "";
        $app_customertime = "";
        foreach ($config_info as $val){
            switch ($val['name'])
            {
                case "app_customertel":
                     $customertel = $val['value'];
                    break;
                case "app_customertime":
                     $app_customertime = $val['value'];
                    break;
            }
        }
        $info['data'] = $data;
        $info['customertel'] = $customertel;
        $info['customertime'] = $app_customertime;
		$info['shopinfo'] = $shopInfo;        
        return $this->showMsg(1,'',$info);
    }

    /**
     * 修改用户信息
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function saveUserInfo(){
        $user_id = $this->uid;
        $data = input();
        $info = Db::name('SystemUser')->field('id')->where('phone', 'eq', $data['info']['phone'])->find();
        if($info){
	        if($info['id'] != $user_id){
	            return $this->showMsg(-1,'手机号已存在');
	        }
        }
        $res = Db::name('SystemUser')->where('id', 'eq', $user_id)->update($data['info']);
        if($res){
            //添加操作记录
            $res = $this->UserOperation($user_id, "修改个人信息");
            return $this->showMsg(1,'修改成功');
        }
        return $this->showMsg(-1,'修改失败');
    }

    /**
     * 添加意见反馈
     */
    public function addFeedback(){
        $user_id = $this->uid;
        $data = input();
        $info = array(
            "user_id" => $user_id,
            "platform_type" => 1,
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

    /**
     * 获取展厅服务列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function servicesList(){
        $list = Db::name('StoreServices')->field('id,title,explain,price,dates')->where('is_freeze=2 and is_delete=2')->order('sort desc')->select();
        if($list){
            foreach ($list as $key=>$val){
                $list[$key]['id'] = strval($val['id']);
            }
        }
        $config = Db::name('SystemConfig')->field('value')->where('name = "app_customertel"')->find();
        $info['customertel'] = $config['value'];
        $info['list'] = $list;
        return $this->showMsg(1,'',$info);
    }

//    /**
//     * 获取服务信息(线下)
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\ModelNotFoundException
//     * @throws \think\exception\DbException
//     */
//    public function servicesInfo(){
//        $data = input();
//        $info = Db::name('StoreServices')->field('id,title,explain,price,dates')->where('id = '.$data['id'])->find();
//        $config_info = Db::name('SystemConfig')->field('name,value')->where('name in ("service_bank_name","service_bank_card","service_bank_openbank")')->select();
//        foreach ($config_info as $val){
//            switch ($val['name'])
//            {
//                case "service_bank_name":
//                     $info['bank_name'] = $val['value'];
//                    break;
//                case "service_bank_card":
//                     $info['bank_card'] = $val['value'];
//                    break;
//                case "service_bank_openbank":
//                     $info['bank_openbank'] = $val['value'];
//                    break;
//            }
//        }
//        return $this->showMsg(1,'',$info);
//    }

    /**
     * 获取服务信息（线上）
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function servicesInfo(){
        $data = input();
        $info = Db::name('StoreServicesOrder')->field('order_sn,pay_price')->where('id = '.$data['id'])->find();
        $info['pay_price'] = $info['pay_price']/100;
        $config_info = Db::name('SystemConfig')->field('name,value')->where('name in ("service_bank_name","service_bank_card","service_bank_openbank","service_weixin_img","service_alipay_img")')->select();

        foreach ($config_info as $val){
            switch ($val['name'])
            {
                case "service_bank_name":
                    $info['bank_name'] = $val['value'];
                    break;
                case "service_bank_card":
                    $info['bank_card'] = $val['value'];
                    break;
                case "service_bank_openbank":
                    $info['bank_openbank'] = $val['value'];
                    break;
                case "service_weixin_img":
                     $info['weixin_img'] = $val['value'];
                    break;
                case "service_alipay_img":
                     $info['alipay_img'] = $val['value'];
                    break;
            }
        }
        return $this->showMsg(1,'',$info);
    }

    /**
     * 获取用户购买的服务信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userServicesInfo(){
        $data = input();
        $uid = $this->uid;
        $UsersService = Db::name('StoreUsersService')->where('service_id = '.$data['id'].' and uid = '.$uid)->find();
        $info = Db::name('StoreServices')->field('title,explain,dates')->where('id = '.$data['id'])->find();
        if($UsersService){
            //商家购买过该服务
            $endtime = strtotime($UsersService['end_time']);
            if(time() > $endtime){
                //服务已过期
                $info['start_time'] = date('Y-m-d H:i:s',time());
                $endtime = time()+$info['dates']*24*3600;
                $info['end_time'] = date('Y-m-d H:i:s',$endtime);
            }else{
                //服务未过期
                $info['start_time'] = $UsersService['start_time'];
                $endtime = $endtime+$info['dates']*24*3600;
                $info['end_time'] = date('Y-m-d H:i:s',$endtime);
            }
        }else{
            //商家没有购买过该服务
            $info['start_time'] = date('Y-m-d H:i:s',time());
            $endtime = time()+$info['dates']*24*3600;
            $info['end_time'] = date('Y-m-d H:i:s',$endtime);
        }

        return $this->showMsg(1,'',$info);
    }

    /**
     * 购买服务
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function servicesPay(){
        $data = input();
        $uid = $this->uid;
        $res = Db::name('StoreServices')->field('id,title,explain,price,dates')->where('id = '.$data['id'])->find();
        $info = array(
            "uid" => $uid,
            "service_id" => $data['id'],
            "price" => $res['price'],
            "type" => $data['pay_type'],
            "pay_img" => $data['imgs'][0],
            "create_at" => date('Y-m-d H:i:s', time())
        );
        $res1 = Db::name('StoreServicesOrder')->insertGetId($info);
        if($res1){
            //添加操作记录
            $res2 = $this->UserOperation($uid, "购买名称为【".$res['title']."】的服务");
            return $this->showMsg(1, "支付成功");
        }
        return $this->showMsg(-1, "支付失败");
    }

    /**
     * 我的展厅信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myShop(){
        $info = Db::name('StoreShop')->field('id,logo,title,labels,video_url,vr_url,intro,cate_id,business_license,cover_image,business_certificate,contacts,phone,wechat,province,city,area,address,qr_code,slide_show,longitude,latitude,details')->where(['id'=>$this->shopId])->find();
        if($info){
            if($info['labels']){
                $data['label'] = explode(',',$info['labels']);
            }else{
                $data['label'] = array();
            }
            if($info['slide_show']){
                $data['slide_list'] = explode('|',$info['slide_show']);
            }else{
                $data['slide_list'] = array();
            }
            $cateInfo = Db::name('StoreShopCate')->where(['id'=>$info['cate_id']])->field('title')->find();
            $data['cate_name'] = $cateInfo['title'];
            $data['location'] = $info['province'].$info['city'].$info['area'];
        }
        $data['info'] = $info;
        return $this->showMsg(1,'',$data);
    }
    /**
     * 我的展厅信息(店铺分享使用)
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shopShare(){
        $info = Db::name('StoreShop')->field('qr_code')->where(['id'=>$this->shopId])->find();
        return $this->showMsg(1,'',$info['qr_code']);
    }

    /**
     * 编辑展厅
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function editShop(){
        $data = input('info');
    	$id = $data['id'];
    	if(Db::name('StoreShop')->where("is_delete = 2 and id <> ".$id." and title = '".$data['title']."'")->count() > 0){
            return $this->showMsg(-1,'展厅名称已存在');
        }
    	$labels = str_replace('，',',',trim($data['labels']));
        $shopLabes = Db::name('StoreShop')->field('labels')->where(['id'=>$id])->find();
        $oldLabels = explode(',',$shopLabes['labels']);
        $labels = explode(',',$labels);
        foreach ($labels as $vo){
        	if(Db::name('StoreShopLabel')->where(['title'=>$vo,'is_freeze'=>1])->count()>0){
        		return $this->showMsg(-1,'标签（'.$vo.'）已被禁用，不能使用！');
            }
        }
        $data['labels'] = join(',',array_filter($labels));
        $diffArr1 = array_diff($oldLabels,$labels);
        $diffArr2 = array_diff($labels,$oldLabels);
        if($diffArr1) {
            foreach ($diffArr1 as $vo) {
                $nowCount = Db::name('StoreShopLabel')->field('nowcount')->where(['title'=>$vo])->find()['nowcount'];
                if($nowCount==1){
                    Db::name('StoreShopLabel')->where(['title' => $vo])->delete();
                }elseif($nowCount>1){
                    Db::name('StoreShopLabel')->where(['title' => $vo])->setDec('nowcount');
                }
            }
        }
        if($diffArr2){
            foreach ($diffArr2 as $vo){
                $word = Db::name('StoreShopLabel')->where(['title'=>$vo])->find();
                if(empty($word)){
                    Db::name('StoreShopLabel')->insert([
                        'title'=>$vo,
                        'shop_id'=>$data['id'],
                    ]);
                }else{//使用数+1
                    Db::name('StoreShopLabel')->where(['title'=>$vo])->setInc('hotcount');
                    Db::name('StoreShopLabel')->where(['title'=>$vo])->setInc('nowcount');
                }
            }
        }
        Db::name('StoreShop')->where(['id'=>$data['id']])->update($data);
        $this->UserOperation($this->uid,'编辑ID：'.$data['id'].'，展厅名称：【'.$data['title'].'】');
        return $this->showMsg(1,'编辑成功');
    }
}