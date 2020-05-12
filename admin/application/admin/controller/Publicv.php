<?php



namespace app\admin\controller;

use library\Controller;
use think\Db;

/**
 * 公共控件
 * Class Publicv
 * @package app\admin\controller
 */
class Publicv extends Controller
{


    /**
     * 高德地图选坐标
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
	 * @<span class="color-green" style="cursor:pointer;" data-iframe="{:url('admin/publicv/amap')}?longitude={$vo.longitude|default=''}&latitude={$vo.latitude|default=''}" data-title="高德地图获取坐标">坐标</span>
     */
    public function amap()
    {
		$this->assign('longitude', input('get.longitude')?input('get.longitude'):"108.946908");
		$this->assign('latitude', input('get.latitude')?input('get.latitude'):"34.259614");
        return $this->fetch();
    }


	/**
     * 点选展厅
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
	 * @<span class="color-green" style="cursor:pointer;" data-iframe="{:url('admin/publicv/shop')}?selected_id=反填框ID" data-title="标题">按钮</span>
     */
    public function shop()
    {
		$this->assign('selected_id', input('get.selected_id')?input('get.selected_id'):"selected_id");
        $list = Db::name('StoreShop')->alias('s')->where(['s.is_delete' => '2','s.is_freeze' => '2'])->field('s.id')->select();
        $count = count($list);
		$query = $this->_query('StoreShop')->alias('s')
			 ->leftjoin("store_shop_cate c",'c.id=s.cate_id')
			 ->like('s.title#title')
			 ->equal('s.cate_id#cate_id')
			 ->where(['s.is_delete' => '2','s.is_freeze' => '2'])
			 ->order('s.sort asc,s.id desc')
			 ->field('s.id,s.title,s.create_at,c.title as ctitle')
			 ->page(true, true, $count, 5, 1);
		$this->assign('shopcate', Db::name('StoreShopCate')->where("status=1 and is_delete=2")->field('id,title')->order('sort asc')->select());
		return $this->fetch('shop',$query);
    }

	/**
     * 点选商品
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
	 * @<span class="color-green" style="cursor:pointer;" data-iframe="{:url('admin/publicv/goods')}?selected_id=反填框ID" data-title="标题">按钮</span>
     */
    public function goods()
    {
		$this->assign('selected_id', input('get.selected_id')?input('get.selected_id'):"selected_id");
        $list = Db::name('StoreGoods')->alias('g')->where(['g.is_delete' => '2','g.is_show_main' => '1','g.is_show_second'=>1])->order('g.sort asc,g.id desc')->field('g.id')->select();
        $count = count($list);
		$query = $this->_query('StoreGoods')->alias('g')->leftjoin("store_shop s",'s.id=g.shop_id')->leftjoin("store_goods_cate c",'c.id=g.cate_id')->like('g.title#title,s.title#stitle')->equal('g.cate_id#cate_id')->where(['g.is_delete' => '2','g.is_show_main' => '1','is_show_second'=>1])->order('g.sort asc,g.id desc')->field('g.id,g.title,c.title as ctitle,s.title as stitle,g.create_at')->page(true, true, $count, 5, 1);
//        print(Db::getLastSql());die;
		$this->assign('goodscate', Db::name('store_goods_cate')->where("status=1 and is_delete=2 and pid=0")->field('id,title')->order('sort asc')->select());
		return $this->fetch('goods',$query);
    }

	/**
     * 点选活动
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
	 * @<span class="color-green" style="cursor:pointer;" data-iframe="{:url('admin/publicv/huodong')}?selected_id=反填框ID" data-title="标题">按钮</span>
     */
    public function huodong()
    {
		$this->assign('selected_id', input('get.selected_id')?input('get.selected_id'):"selected_id");
        $list = Db::name('StoreActivities')->where(['is_delete' => '2','cate' => '1','status'=>1])->field('id')->select();
        $count = count($list);
		$query = $this->_query('StoreActivities')->like('name')->equal('platform_type')->where(['is_delete' => '2','cate' => '1','status'=>1])->order('sort asc,id desc')->field('id,name,create_at,platform_type')->page(true, true, $count, 5, 1);
		return $this->fetch('huodong',$query);
    }
	// 当前列表数据处理
    protected function _huodong_page_filter(&$data){
         foreach($data as &$vo){
			$vo['platform']=$vo['platform_type']==1?'展厅平台':'用户平台';
         }
    }

	/**
     * 点选展会
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
	 * @<span class="color-green" style="cursor:pointer;" data-iframe="{:url('admin/publicv/zhanhui')}?selected_id=反填框ID" data-title="标题">按钮</span>
     */
    public function zhanhui()
    {
		$this->assign('selected_id', input('get.selected_id')?input('get.selected_id'):"selected_id");
        $list = Db::name('StoreActivities')->where(['is_delete' => '2','cate' => '2','status'=>1])->field('id')->select();
        $count = count($list);
		$query = $this->_query('StoreActivities')->like('name')->equal('platform_type')->where(['is_delete' => '2','cate' => '2','status'=>1])->order('sort asc,id desc')->field('id,name,create_at,platform_type')->page(true, true, $count, 5, 1);
		return $this->fetch('zhanhui',$query);
    }
	// 当前列表数据处理
    protected function _zhanhui_page_filter(&$data){
         foreach($data as &$vo){
			$vo['platform']=$vo['platform_type']==1?'展厅平台':'用户平台';
         }
    }

	/**
     * 点选新闻
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
	 * @<span class="color-green" style="cursor:pointer;" data-iframe="{:url('admin/publicv/news')}?selected_id=反填框ID" data-title="标题">按钮</span>
     */
    public function news()
    {
		$this->assign('selected_id', input('get.selected_id')?input('get.selected_id'):"selected_id");
        $list = Db::name('StoreNews')->alias('n')->where(['n.is_delete' => 2,'n.status' => 1])->field('n.id')->select();
        $count = count($list);
		$query = $this->_query('StoreNews')->alias('n')->like('n.title#title')->equal('n.cate_id#cate_id')->leftjoin("store_news_cate c",'c.id=n.cate_id')->where(['n.is_delete' => 2,'n.status' => 1])->order('n.sort asc,n.id desc')->field('n.id,n.title,c.title as ctitle,n.create_at')->page(true, true, $count, 5, 1);
		$this->assign('newscate', Db::name('store_news_cate')->where("status=1 and is_delete=2")->field('id,title')->order('sort asc')->select());
		return $this->fetch('news',$query);
    }

}