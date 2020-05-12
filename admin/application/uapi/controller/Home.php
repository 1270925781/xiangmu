<?php
namespace app\uapi\controller;
use think\Db;

class Home extends Common{

    /**
     * 获取首页热搜关键词
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHotSearch(){
        $data = input();
        $type = $data['type'];
        $words = Db::name('SystemKeywords')->where(['keycate'=>$type,'is_delete'=>2])->order('sort desc,count desc')->select();
        return $this->showMsg(1,'获取成功',$words);
    }

    /**
     * 获取首页数据
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHomeData(){
        $info = [
            'slide_show'   =>   $this->getSlideshow(),
            'news'      =>   $this->getNews(),
            'hot_goods'    =>   $this->getHotGoods(),
            'new_goods'    =>   $this->getNewGoods(),
            'recom_shop'   =>   $this->getRecomShop()
        ];
        return $this->showMsg(1,'获取成功',$info);
    }

    /**
     * 获取首页轮播图
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSlideshow(){
        $today = date('Y-m-d H:i:s',time());
        $where = "location_id=6 and status=1 and is_delete=2 and platform_type=2";
        $list = Db::name('StoreAds')->field('id,ad_img,ad_type,route_id,route,endtime')->where($where)->whereTime('endtime','>',$today)->whereTime('starttime','<=',$today)->order('sort desc,id desc')->select();
        if($list){
        	return $list;
        }else{
        	return array();
        }
    }

    /**
     * 获取头条快讯
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNews(){
        $message = Db::name('StoreNews')->field('id,title')->where(['is_delete'=>2,'status'=>1,'cate_id'=>3])->order('id desc')->limit(1)->select();
        if($message){
        	return $message[0];
        }else{
        	return array();
        }
    }

    /**
     * 获取爆款产品
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHotGoods(){
        $time = time();
        $where = "g.is_delete=2 and g.is_show_main=1 and g.is_show_second=1 and g.is_hot=1 and g.hot_start_time<=$time and g.hot_end_time>$time and s.is_freeze=2";
        $goods = Db::name('StoreGoods')->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->field('g.id,g.title,g.cover_image')->where($where)->order('g.hot_sort desc,g.id desc')->select();
        if($goods){
        	return $goods;
        }else{
        	return array();
        }
    }

    /**
     * 获取新品产品
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNewGoods(){
        $time = time();
        $where = "g.is_delete=2 and g.is_show_main=1 and g.is_show_second=1 and g.is_new=1 and g.new_start_time<=$time and g.new_end_time>$time and s.is_freeze=2 ";
        $field = 'g.id,g.shop_id,g.title as goods_name,g.cover_image,s.title as shop_name';
        $goods = Db::name('StoreGoods')->field($field)->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->where($where)->order('g.new_sort desc,id desc')->limit(10)->select();
        if($goods){
        	return $goods;
        }else{
        	return array();
        }
    }

    /**
     * 获取推荐展厅
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRecomShop(){
        $time = time();
        $where = "is_freeze=2 and is_delete=2 and is_home=1 and home_start_time<=$time and home_end_time>$time";
        $field = 'id,home_cover_image,cover_image,title as shop_name,province,city,area,address,labels';
        $goods = Db::name('StoreShop')->field($field)->where($where)->order('home_sort desc,id desc')->select();
        foreach ($goods as &$vo){
            if($vo['labels']) {
                $vo['labels'] = array_slice(explode(',', $vo['labels']), 0, 6);
            }
            $vo['location'] = $vo['address'];
        }
        if($goods){
        	return $goods;
        }else{
        	return array();
        }
    }

    /**
     * 获取附近展厅
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNearShop(){
        $data = input();
        $area = $data['area'];
        $pageLimit = 3;
        $longitude = isset($data['longitude'])?$data['longitude']:'116.224513';
        $latitude = isset($data['latitude'])?$data['latitude']:'40.004293';
        if(!$latitude){
        	 $longitude='116.224513';
        }
        if(!$latitude){
        	 $latitude='40.004293';
        }
        $field = 'id,title,cover_image,labels,view_num,like_num,collect_num,province,city,area,address';
        $lm    = (intval($data['page']) - 1) * $pageLimit;
        $limit = " limit $lm,$pageLimit";
        $where = "is_freeze=2 and is_delete=2";
        
        if(!empty($area)){
        	$where .= " and province='$area'";
        }
        $shopList = $this->getDistance($longitude,$latitude,$field,$where,$limit);
        if($shopList){
            foreach ($shopList as &$vo){
                if($vo['labels']) {
                    $vo['labels'] = array_slice(explode(',', $vo['labels']), 0, 6);
                }
                $vo['location'] = $vo['address'];
            }
        }

        $list = [];
        $list['shop_list'] = $shopList;
        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'获取成功',$list);
    }
    
    /**
     * 获取用户端app版本号
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function getShopAppVersion()
    {
        $data['ios'] = sysconf('app_iosver');
        $data['android'] = sysconf('app_androidver');
        return $this->showMsg(1,'',$data);
    }
    
    /**
     * 增加关键词热度
     * @return Response
     * @throws \think\Exception
     */
    public function addHeat(){
        $word = input('word');
        Db::name('SystemKeywords')->where(['title'=>$word])->setInc('count');
        return $this->showMsg(1,'增加成功');
    }
    
    /**
     * 验证token是否有效
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isToken(){
        $token = input('token');
        $tokenInfo = Db::name('StoreToken')->where(['token' => $token,'user_type'=>2])->find();
        if($tokenInfo){
            return $this->showMsg(1,'有效');
        }else{
            return $this->showMsg(1,'无效');
        }
    }
    
    /**
     * 获取产品信息
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsInfo(){
        $data = input();
        $id = $data['id'];
        Db::name('StoreGoods')->where(['id'=>$id])->setInc('view_num');
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and g.id=$id";
        $field = "g.*,s.contacts,s.phone,s.title as shop_name,s.province,s.city,s.area,s.address,s.longitude,s.latitude";
        $info = Db::name('StoreGoods')->alias('g')->field($field)->leftJoin('store_shop s','g.shop_id=s.id')->where($where)->find();
        $info['location'] = $info['province'].$info['city'].$info['area'].$info['address'];
        $info['image'] = explode('|',$info['image']);
        if($info['content_img']){
            $imgArr = explode('|',$info['content_img']);
            $str = '';
            foreach ($imgArr as $vo){
                $str .= "<p><img border='0' style='max-width:100%' title='image' src='$vo'/></p>";
            }
            $info['content'] .= $str;
        }

        return $this->showMsg(1,'',$info);
    }
    
    /**
     * 获取展厅推荐产品
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRecommendGoods()
    {
        $data = input();
        $id = $data['goods_id'];
        $shopId = $data['shop_id'];
        $page = $data['page'];
        $pageLimit = 4;
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and g.is_recommend=1 and g.shop_id=$shopId and g.id!=$id";
        $list['goods_list'] = Db::name('StoreGoods')->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->field('g.id,g.title as goods_name,g.cover_image,s.title as shop_name')->where($where)->order('g.recommend_sort desc,g.sort desc,g.id desc')->page($page,$pageLimit)->select();

        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'获取成功',$list);
    }
    
     /**
     * 获取展厅信息
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopInfo()
    {
        $data = input();
        $id = $data['id'];
        $where = "is_freeze=2 and is_delete=2 and id=$id";
        $info = Db::name('StoreShop')->where($where)->find();

        if($info['slide_show']){
            $info['slide_show'] = explode('|',$info['slide_show']);
        }
        if($info['labels']){
            $info['labels'] = explode(',',$info['labels']);
        }
        $info['location'] = $info['province'].$info['city'].$info['area'].$info['address'];

        $info['recommend_goods'] = $this->getShopRecommendGoods($id);

        return $this->showMsg(1,'',$info);
    }
    
     /**
     * 获取本店推荐产品
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopRecommendGoods($id)
    {
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and g.is_recommend=1 and g.shop_id=$id";
        $list = Db::name('StoreGoods')->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->field('g.id,g.title as goods_name,g.cover_image,s.title as shop_name')->where($where)->order('g.recommend_sort desc,g.sort desc,g.id desc')->select();
        return $list;
    }
    
    /**
     * 获取展厅全部产品
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopGoods(){
        $data = input();
        $id = $data['id'];
        $page = $data['page'];
        $pageLimit = 8;
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and g.shop_id=$id";
        $list['goods_list'] = Db::name('StoreGoods')->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->field('g.id,g.title as goods_name,g.cover_image,s.title as shop_name')->where($where)->order('g.sort desc,g.id desc')->page($page,$pageLimit)->select();
        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'获取成功',$list);
    }
}