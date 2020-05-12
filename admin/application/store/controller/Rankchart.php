<?php

namespace app\store\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 排行榜和统计曲线
 * Class Rankchart
 * @package app\store\controller
 */
class Rankchart extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreGoods';

    /**
     * 排行榜首页
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function ranklist()
    {
        $this->title = '数据排行榜';
		/*
		$shop_id=session('user.shop_id');
		$shop_id=$shop_id?$shop_id:0;
		$this->assign('shopinfo',  Db::name('StoreShop')->where(['id' => $shop_id])->find());
		*/
		return $this->fetch();
    }
	
	/**
     * 排行榜详情
     * @throws \think\Exception
     */
	public function ranknum(){
		//类型
		$ranktype= input('get.ranktype');
		//权限
		$shop_id=session("user.shop_id");//展厅id
		$authorize=session("user.authorize");//权限
		if($authorize==2){//展厅权限
			if($shop_id){
				$where=" and g.shop_id=".$shop_id;
			}else{
				$where=" and g.shop_id=-1";
			}			
		}else{
			$where=" and 1=1 ";
		}
		switch($ranktype){
			////////
			case "shopviewnum"://展厅浏览次数前100
				$query = $this->_query('StoreShop')->field("title,contacts,phone,view_num as count,create_at")->where(' is_delete=2')->order("view_num desc,id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '浏览量');
				return $this->fetch('ranknumshop',$query);
				break;
			case "shopcollectnum"://展厅收藏次数前100
				$query = $this->_query('StoreShop')->field("title,contacts,phone,collect_num as count,create_at")->where('is_freeze=2 and is_delete=2')->order("collect_num desc,id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '收藏量');
				return $this->fetch('ranknumshop',$query);
				break;
			case "shopsharenum"://展厅分享次数前100
				$query = $this->_query('StoreShop')->field("title,contacts,phone,share_num as count,create_at")->where(' is_delete=2')->order("share_num desc,id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '分享量');
				return $this->fetch('ranknumshop',$query);
				break;
			case "shoplikenum"://展厅点赞次数前100
				$query = $this->_query('StoreShop')->field("title,contacts,phone,like_num as count,create_at")->where('is_freeze=2 and is_delete=2')->order("like_num desc,id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '点赞量');
				return $this->fetch('ranknumshop',$query);
				break;
			case "shopcallnum"://展厅拨打电话次数前100
				$query = $this->_query('StoreShop')->field("title,contacts,phone,call_num as count,create_at")->where('is_freeze=2 and is_delete=2')->order("call_num desc,id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '拨打量');
				return $this->fetch('ranknumshop',$query);
				break;
			////////
			case "goodsviewnum"://产品浏览次数前100
				$query = $this->_query('StoreGoods')->alias('g')->field("g.title,s.title as s_title,g.view_num as count,g.create_at")->join('store_shop s','g.shop_id = s.id')->where('g.is_show_main=1 and g.is_show_second=1'.$where)->order("g.view_num desc,g.id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '浏览量');
				return $this->fetch('ranknumgoods',$query);
				break;
			case "goodscollectnum"://产品收藏次数前100
				$query = $this->_query('StoreGoods')->alias('g')->field("g.title,s.title as s_title,g.collect_num as count,g.create_at")->join('store_shop s','g.shop_id = s.id')->where('g.is_show_main=1 and g.is_show_second=1'.$where)->order("g.collect_num desc,g.id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '收藏量');
				return $this->fetch('ranknumgoods',$query);
				break;
			case "goodssharenum"://产品分享次数前100
				$query = $this->_query('StoreGoods')->alias('g')->field("g.title,s.title as s_title,g.share_num as count,g.create_at")->join('store_shop s','g.shop_id = s.id')->where('g.is_show_main=1 and g.is_show_second=1'.$where)->order("g.share_num desc,g.id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '分享量');
				return $this->fetch('ranknumgoods',$query);
				break;
			case "goodslikenum"://产品点赞次数前100
				$query = $this->_query('StoreGoods')->alias('g')->field("g.title,s.title as s_title,g.like_num as count,g.create_at")->join('store_shop s','g.shop_id = s.id')->where('g.is_show_main=1 and g.is_show_second=1'.$where)->order("g.like_num desc,g.id asc")->page(false, false, false, 100);
				$this->assign('numtitle', '点赞量');
				return $this->fetch('ranknumgoods',$query);
				break;
			////////
			case "searchkey"://搜索关键字前500
				$query = $this->_query('SystemKeywords')->order("count desc,id asc")->page(false, false, false, 500);
				return $this->fetch('ranknumother',$query);
				break;
			case "labelhot"://展厅标签热度前100
				$query = $this->_query('StoreShopLabel')->order("count desc,id asc")->page(false, false, false, 100);
				return $this->fetch('ranknumother',$query);
				break;
			case "labeluse"://展厅标签累计使用前100
				$query = $this->_query('StoreShopLabel')->field("title,hotcount as count,create_at")->order("hotcount desc,id asc")->page(false, false, false, 100);
				return $this->fetch('ranknumother',$query);
				break;
		}
	}

    /**
     * 统计曲线首页
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function yschart()
    {
		//权限
		$shop_id=session("user.shop_id");//展厅id
		$authorize=session("user.authorize");//权限
		if($authorize==2){//展厅权限
			if($shop_id){
				$where=" and shop_id=".$shop_id;
			}else{
				$where=" and shop_id=-1";
			}			
		}else{
			$where=" and 1=1 ";
		}
		$this->assign('shop_id', $shop_id);//根据shop_id判断一些标签显示

        $this->title = '数据统计曲线';
		$starttime=date("Y-m-d 00:00:00");$endtime=date("Y-m-d 23:59:59");
		//当日展厅浏览总数量
		$shopviewcount=Db::name('StoreShopView')->where("create_at>='".$starttime.' 00:00:00'."' and create_at<='".$endtime." 23:59:59'".$where)->count();
		$this->assign('shopviewcount', $shopviewcount);
		//当日展厅收藏总数量
		$shopcollectcount=Db::name('StoreShopCollect')->where("create_at>='".$starttime.' 00:00:00'."' and create_at<='".$endtime." 23:59:59' and status=1".$where)->count();
		$this->assign('shopcollectcount', $shopcollectcount);
		//浏览用户区域数
        $shopviewareacount = Db::name('StoreShopView')->group('area')->count();
        $this->assign('shopviewareacount', $shopviewareacount);
         //收藏用户区域数
        $shopcollectareacount = Db::name('StoreShopCollect')->where(['status'=>1])->group('area')->count();
        $this->assign('shopcollectareacount', $shopcollectareacount);
		//当日用户增长总数
		$membercount=Db::name('store_member')->where("status=2 and create_at>='".$starttime.' 00:00:00'."' and create_at<='".$endtime." 23:59:59'")->count();
		$this->assign('membercount', $membercount);
		return $this->fetch();
    }

	/**
     * 统计曲线详情
     * @throws \think\Exception
     */
	public function yschartline(){
		//类型
		$linetype=input('get.linetype');$this->assign('linetype', $linetype);
		//权限
		$shop_id=session("user.shop_id");//展厅id
		$authorize=session("user.authorize");//权限
		if($authorize==2){//展厅权限
			if($shop_id){
				$where=" and shop_id=".$shop_id;
			}else{
				$where=" and shop_id=-1";
			}			
		}else{
			$where=" and 1=1 ";
		}
		//搜索时间条件
		$create_at= input('get.create_at')?input('get.create_at'):"";
		if($create_at){
			$create_at_=explode(" - ",$create_at);
			$starttime=$create_at_[0];$endtime=$create_at_[1];
			$this->assign('create_at', $create_at);
		}else{
			$starttime=date('Y-m-d',strtotime('-30 day',time()));//默认最近30天数据
			$endtime=date('Y-m-d',time());
			$this->assign('create_at', $starttime." - ".$endtime);
		}
		//
		switch($linetype){
			case "shopviewline"://展厅浏览日曲线
				$daylist=$this->get_daylist($starttime,$endtime);
				foreach($daylist as $k=>$v){
					$count=Db::name('StoreShopView')->where("create_at>='".$v.' 00:00:00'."' and create_at<='".$v." 23:59:59'".$where)->count();
					$tongji_day[$k]=$v;
					$tongji_count[$k]=$count;
					unset($count);
				}
				$this->assign('tongji_day', $daylist);
				$this->assign('tongji_count', json_encode($tongji_count));
				$this->assign('unit', '次');
				return $this->fetch('yschartlineuser');
				break;
			case "shopcollectline"://展厅收藏日曲线
				$daylist=$this->get_daylist($starttime,$endtime);
				foreach($daylist as $k=>$v){
					$count=Db::name('StoreShopCollect')->where("create_at>='".$v.' 00:00:00'."' and create_at<='".$v." 23:59:59' and status=1".$where)->count();
					$tongji_day[$k]=$v;
					$tongji_count[$k]=$count;
					unset($count);
				}
				$this->assign('tongji_day', $daylist);
				$this->assign('tongji_count', json_encode($tongji_count));
				$this->assign('unit', '次');
				return $this->fetch('yschartlineuser');
				break;
			case "shopviewarea"://展厅浏览用户区域分布
				$list=Db::name('store_shop_view')->where("create_at>='".$starttime.' 00:00:00'."' and create_at<='".$endtime." 23:59:59'".$where)->field('count(*) as counts,area')->order("counts desc")->group('area')->select();
				$this->assign('numtitle', '浏览量');
				return $this->fetch('yschartarea',['list'=>$list]);
				break;
			case "shopcollectarea"://展厅收藏用户区域分布
				$list=Db::name('store_shop_collect')->where("create_at>='".$starttime.' 00:00:00'."' and create_at<='".$endtime." 23:59:59' and status=1".$where)->field('count(*) as counts,area')->order("counts desc")->group('area')->fetchSql(false)->select();
				$this->assign('numtitle', '收藏量');
				return $this->fetch('yschartarea',['list'=>$list]);
				break;
			case "labeluse"://用户增加曲线
				$daylist=$this->get_daylist($starttime,$endtime);
				foreach($daylist as $k=>$v){
					$count=Db::name('store_member')->where("status=2 and create_at>='".$v.' 00:00:00'."' and create_at<='".$v." 23:59:59'")->count();
					$tongji_day[$k]=$v;
					$tongji_count[$k]=$count;
					unset($count);
				}
				$this->assign('tongji_day', $daylist);
				$this->assign('tongji_count', json_encode($tongji_count));
				$this->assign('unit', '人');
				return $this->fetch('yschartlineuser');
				break;
		}
	}
	//获取两个日期段内所有日期
	protected function get_daylist($startdate,$enddate){
		$stimestamp = strtotime($startdate);
		$etimestamp = strtotime($enddate);
		// 计算日期段内有多少天
		$days = ($etimestamp-$stimestamp)/86400+1;
		// 保存每天日期
		$date = array();
		for($i=0; $i<$days; $i++){
			$date[] = date('Y-m-d', $stimestamp+(86400*$i));
		}
		return $date;
	}
}