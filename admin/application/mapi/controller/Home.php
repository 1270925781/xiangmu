<?php
namespace app\mapi\controller;
use think\Db;

class Home extends Base
{
    /**
     * 获取首页数据
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHomeData(){
        $shopId = $this->shopId;
        $info = Db::name('StoreShop')->field('title')->where('id='.$shopId)->find();
        $list['shop_name'] = $info['title'];
        $list['slide_show'] = $this->getSlideshow();
        $list['count'] = $this->getCount($shopId);
        $list['message'] = $this->getMessage();
        $list['activity'] = $this->getNewActivities();
        $list['trade_show'] = $this->getNewTradeshow();
        return $this->showMsg(1,'',$list);
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
        $where = "location_id=1 and status=1 and is_delete=2 and platform_type=1";
        $list = Db::name('StoreAds')->field('id,ad_img,ad_type,route_id,route,endtime')->where($where)->whereTime('endtime','>',$today)->whereTime('starttime','<=',$today)->order('sort desc')->select();
        return $list;
    }

    /**
     * 获取首页统计数
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCount($shopId){
        $cateField = 'c.id,g.id as goods_id,g.shop_id';
        $list['cate_count'] = Db::name('StoreGoodsCate')->alias('c')->field($cateField)->leftJoin('store_goods g','c.id=g.cate_id')->where(['c.is_delete'=>2,'c.status'=>1,'g.shop_id'=>$shopId,'c.pid'=>0])->group('c.id')->count();
    	$list['goods_count'] = Db::name('StoreGoods')->where(['is_delete'=>2,'shop_id'=>$shopId])->count();
        $list['goods_sale_count'] = Db::name('StoreGoods')->where(['is_delete'=>2,'shop_id'=>$shopId,'is_show_second'=>1])->count();
        $list['goods_unsale_count'] = Db::name('StoreGoods')->where("is_delete=2 and shop_id=$shopId and  is_show_second=2")->count();

        $list['view_count'] = Db::name('StoreShopView')->where(['shop_id'=>$shopId])->count();
        $list['collect_count'] = Db::name('StoreShopCollect')->where(['shop_id'=>$shopId,'status'=>1])->count();
        $list['like_count'] = Db::name('StoreShopLike')->where(['shop_id'=>$shopId,'status'=>1])->count();
        $list['share_count'] = Db::name('StoreShopShare')->where(['shop_id'=>$shopId])->count();

        return $list;
    }


    /**
     * 获取首页最新公告
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMessage(){
       $newsInfo = Db::name('StoreNews')->field('id,title,intro,content,create_at')->where(['is_delete'=>2])->limit('1')->order('id','desc')->select();
        if($newsInfo){
            $newsInfo = $newsInfo[0];
            $newsInfo['year'] = date('Y/m',strtotime($newsInfo['create_at']));
            $newsInfo['date'] = date('d',strtotime($newsInfo['create_at']));

            unset($newsInfo['create_at']);
            return $newsInfo;
        }
        return array();
    }

    /**
     * 获取展厅平台最新活动
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNewActivities(){
        $actList = Db::name('StoreActivities')->field('id,name,cover_image')->where('cate=1 and platform_type=1 and status=1 and is_delete=2')->limit(3)->order('sort desc')->select();

        return $actList;
    }

    /**
     * 获取展厅平台最新展会
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNewTradeshow(){
        $showList = Db::name('StoreActivities')->field('id,name,cover_image')->where('cate=2 and platform_type=1 and status=1 and is_delete=2')->limit(3)->order('sort desc')->select();

        return $showList;
    }

    /**
     * 获取展厅端app版本号
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function getShopAppVersion()
    {
        $data['ios'] = sysconf('app_shopiosver');
        $data['android'] = sysconf('app_shopandroidver');
        return $this->showMsg(1,'',$data);
    }




}