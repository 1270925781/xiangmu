<?php
namespace app\mapi\controller;
use think\Db;
use think\Request;
use think\facade\Config;

class Statistics extends Base
{
    
    /**
     * 展厅商品信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsList(){
        $list = Db::name('StoreGoods')->field('id,title')->where('is_delete = 2 and shop_id = '.$this->shopId)->order('sort desc,id desc')->select();
        $goods_list[0]['label'] = "全部";
        $goods_list[0]['value'] = 0;
        if($list){
            foreach ($list as $key=>$val){
                $goods_list[$key+1]['label'] = $val['title'];
                $goods_list[$key+1]['value'] = $val['id'];
            }
        }
        return $this->showMsg(1,'',$goods_list);
    }
    
    
    /**
     * 产品浏览排名
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsBrowse(){
        $list = Db::name('StoreGoods')->field('title,view_num')->where('is_delete = 2 and shop_id = '.$this->shopId)->order('view_num desc')->limit(10)->select();
        return $this->showMsg(1,'',$list);
    }
    
    /**
     * 产品收藏排名
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsCollection(){
        $list = Db::name('StoreGoods')->field('title,collect_num')->where('is_delete = 2 and shop_id = '.$this->shopId)->order('collect_num desc')->limit(10)->select();
        return $this->showMsg(1,'',$list);
    }
    
    /* 产品浏览、收藏、点赞统计
     * goods_time_type: 日期类型 1日报 2周报 3月报
     * goods_id: 产品ID
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsData(){
        $where = "sg.is_delete = 2 and sg.shop_id = ".$this->shopId;
        $data = input();
        if($data['goods_time_type'] == 1){
            $time_type = "today";
        }else if($data['goods_time_type'] == 2){
            $time_type = "week";
        }else if($data['goods_time_type'] == 3){
            $time_type = "month";
        }
        if($data['goods_id'] != 0){
            $where = $where." and sg.id = ".$data['goods_id'];
        }
        //浏览量
        $info['view_num'] = Db::name('StoreGoodsView')->alias('g')
                ->leftJoin('store_goods sg','sg.id=g.goods_id')
                ->where($where)
                ->whereTime('g.create_at', $time_type)
                ->count();
        //点赞量
        $info['like_num'] = Db::name('StoreGoodsLike')->alias('g')
                ->leftJoin('store_goods sg','sg.id=g.goods_id')
                ->where($where.' and g.status=1')
                ->whereTime('g.create_at', $time_type)
                ->count();
        //收藏量
        $info['collect_num'] = Db::name('StoreGoodsCollect')->alias('g')
                ->leftJoin('store_goods sg','sg.id=g.goods_id')
                ->where($where.' and g.status=1')
                ->whereTime('g.create_at', $time_type)
                ->count();
        return $this->showMsg(1,'',$info);
    }
    
    /* 产品浏览数据曲线统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天
     * goods_id: 产品ID
     * column_num: X轴列数
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsBrowseStatistics(){
        $data = input();
        $where = "sg.is_delete = 2 and sg.shop_id = ".$this->shopId;
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
        $interval_days = 0;
        if($data['goods_time_type'] == 1){
            $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600+2);  //开始时间
            $interval_days = 7/$data['column_num'];
        }else if($data['goods_time_type'] == 2){
            $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600+2);  //开始时间
            $interval_days = 14/$data['column_num'];
        }else if($data['goods_time_type'] == 3){
            $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600+2);  //开始时间
            $interval_days = 30/$data['column_num'];
        }
        if($data['goods_id'] != 0){
            $where = $where." and sg.id = ".$data['goods_id'];
        }
        $list = [];
        $column_num = $data['column_num'];
        $y = 0;
        $max = 0;
        for($i = $column_num; $i > 0 ; $i--){
            $endtime = date("Y-m-d H:i:s",strtotime($begintime)+$interval_days*24*3600);
            $count = Db::name('StoreGoodsView')->alias('g')
               ->leftJoin('store_goods sg','sg.id=g.goods_id')
               ->where($where)
               ->whereTime('g.create_at', 'between', [$begintime, $endtime])
               ->count();
            if($count > $max){
                $max = $count;
            }
            if($data['goods_time_type'] == 1){
                $list['categories']['data'][$y] = date("n.j",strtotime($endtime)-2);
            }else{
                $list['categories']['data'][$y] = date("n.j",strtotime($begintime))."-".date("n.j",strtotime($endtime)-2);
            }
            $list['categories']['count'][$y] = $count;
            $y++;
            $begintime = $endtime;
        }
        if($max < 5){
            $max = 5;
        }else{
            $max = ceil($max/5)*5;
        }
        $list['max'] = $max;
        return $this->showMsg(1,'',$list);
    }
    
    
    /* 产品收藏数据曲线统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天
     * goods_id: 产品ID
     * column_num: X轴列数
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsCollectStatistics(){
        $data = input();
        $where = "sg.is_delete = 2 and sg.shop_id = ".$this->shopId;
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
        $interval_days = 0;
        if($data['goods_time_type'] == 1){
            $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600+2);  //开始时间
            $interval_days = 7/$data['column_num'];
        }else if($data['goods_time_type'] == 2){
            $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600+2);  //开始时间
            $interval_days = 14/$data['column_num'];
        }else if($data['goods_time_type'] == 3){
            $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600+2);  //开始时间
            $interval_days = 30/$data['column_num'];
        }
        if($data['goods_id'] != 0){
            $where = $where." and sg.id = ".$data['goods_id'];
        }
        $list = [];
        $column_num = $data['column_num'];
        $y = 0;
        $max = 0;
        for($i = $column_num; $i > 0 ; $i--){
            $endtime = date("Y-m-d H:i:s",strtotime($begintime)+$interval_days*24*3600);
            $count = Db::name('StoreGoodsCollect')->alias('g')
               ->leftJoin('store_goods sg','sg.id=g.goods_id')
               ->where($where.' and g.status=1')
               ->whereTime('g.create_at', 'between', [$begintime, $endtime])
               ->count();
            if($count > $max){
                $max = $count;
            }
            if($data['goods_time_type'] == 1){
                $list['categories']['data'][$y] = date("n.j",strtotime($endtime)-2);
            }else{
                $list['categories']['data'][$y] = date("n.j",strtotime($begintime))."-".date("n.j",strtotime($endtime)-2);
            }
            $list['categories']['count'][$y] = $count;
            $y++;
            $begintime = $endtime;
        }
        if($max < 5){
            $max = 5;
        }else{
            $max = ceil($max/5)*5;
        }
        $list['max'] = $max;
        return $this->showMsg(1,'',$list);
    }
    
    /* 产品浏览用户分布区域统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天 4.所有
     * goods_id: 产品ID
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsBrowseUser(){
        $data = input();
        $where = "sg.is_delete = 2 and sg.shop_id = ".$this->shopId;
        if($data['goods_time_type'] < 4){
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
            if($data['goods_time_type'] == 1){
                $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 2){
                $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 3){
                $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600);  //开始时间
            }
        }
        if($data['goods_id'] != 0){
            $where = $where." and sg.id = ".$data['goods_id'];
        }
        if($data['goods_time_type'] < 4){
            $list = Db::name('StoreGoodsView')->alias('g')
                    ->field("g.area,count(g.id) as count")
                    ->leftJoin('store_goods sg','sg.id=g.goods_id')
                    ->where($where)
                    ->whereTime('g.create_at', 'between', [$begintime, $endtime])
                    ->group("g.area")
                    ->select();
        }else{
            $list = Db::name('StoreGoodsView')->alias('g')
                    ->field("g.area,count(g.id) as count")
                    ->leftJoin('store_goods sg','sg.id=g.goods_id')
                    ->where($where)
                    ->group("g.area")
                    ->select();
        }
        $info = [];
        $areaColor = Config::get('mapi.areaColor');
        foreach ($list as $key=>$val){
        	$val['area']=$val['area']?$val['area']:'其它';
        	if(strlen($val['area']) > 12){
        		$area = substr($val['area'],0,6);
        	}else{
        		$area = $val['area'];
        	}
            $info[$key]['name'] = $area;
            $info[$key]['data'] = $val['count'];
            if(array_key_exists($val['area'],$areaColor)){
                $info[$key]['color'] = $areaColor[$val['area']];
            }else{
                $str='ABCDEF1234567890';
                $randStr = str_shuffle($str);//打乱字符串
                $rands= substr($randStr,0,6);//substr(string,start,length);返回字符串的一部分
                $info[$key]['color'] = "#".$rands;
            }
        }
        return $this->showMsg(1,'',$info);
    }
    
    /* 产品收藏用户分布区域统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天 4.所有
     * goods_id: 产品ID
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsCollectUser(){
        $data = input();
         $where = "g.status = 1 and sg.is_delete = 2 and sg.shop_id = ".$this->shopId;
        if($data['goods_time_type'] < 4){
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
            if($data['goods_time_type'] == 1){
                $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 2){
                $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 3){
                $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600);  //开始时间
            }
        }
        if($data['goods_id'] != 0){
            $where = $where." and sg.id = ".$data['goods_id'];
        }
        if($data['goods_time_type'] < 4){
            $list = Db::name('StoreGoodsCollect')->alias('g')
                    ->field("g.area,count(g.id) as count")
                    ->leftJoin('store_goods sg','sg.id=g.goods_id')
                    ->where($where.' and g.status=1')
                    ->whereTime('g.create_at', 'between', [$begintime, $endtime])
                    ->group("g.area")
                    ->select();
        }else{
            $list = Db::name('StoreGoodsCollect')->alias('g')
                    ->field("g.area,count(g.id) as count")
                    ->leftJoin('store_goods sg','sg.id=g.goods_id')
                    ->where($where.' and g.status=1')
                    ->group("g.area")
                    ->select();
        }
        $info = [];
        $areaColor = Config::get('mapi.areaColor');
        foreach ($list as $key=>$val){
        	$val['area']=$val['area']?$val['area']:'其它';
            if(strlen($val['area']) > 12){
        		$area = substr($val['area'],0,6);
        	}else{
        		$area = $val['area'];
        	}
            $info[$key]['name'] = $area;
            $info[$key]['data'] = $val['count'];
            if(array_key_exists($val['area'],$areaColor)){
                $info[$key]['color'] = $areaColor[$val['area']];
            }else{
                $str='ABCDEF1234567890';
                $randStr = str_shuffle($str);//打乱字符串
                $rands= substr($randStr,0,6);//substr(string,start,length);返回字符串的一部分
                $info[$key]['color'] = "#".$rands;
            }
        }
        return $this->showMsg(1,'',$info);
    }
    
    /* 展厅浏览、收藏、点赞统计
     * type: 日期类型 1日报 2周报 3月报
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shopData(){
        $where = "shop_id = ".$this->shopId;
        $data = input();
        if($data['goods_time_type'] == 1){
            $time_type = "today";
        }else if($data['goods_time_type'] == 2){
            $time_type = "week";
        }else if($data['goods_time_type'] == 3){
            $time_type = "month";
        }
        //浏览量
        $info['view_num'] = Db::name('StoreShopView')
                ->where($where)
                ->whereTime('create_at', $time_type)
                ->count();
        //点赞量
        $info['like_num'] = Db::name('StoreShopLike')
                ->where($where.' and status=1')
                ->whereTime('create_at', $time_type)
                ->count();
        //收藏量
        $info['collect_num'] = Db::name('StoreShopCollect')
                ->where($where.' and status=1')
                ->whereTime('create_at', $time_type)
                ->count();
        return $this->showMsg(1,'',$info);
    }
    
    /* 展厅浏览数据曲线统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天
     * column_num: X轴列数
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shopBrowseStatistics(){
        $where = "shop_id = ".$this->shopId;
        $data = input();
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
        $interval_days = 0;
        if($data['goods_time_type'] == 1){
            $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600+2);  //开始时间
            $interval_days = 7/$data['column_num'];
        }else if($data['goods_time_type'] == 2){
            $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600+2);  //开始时间
            $interval_days = 14/$data['column_num'];
        }else if($data['goods_time_type'] == 3){
            $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600+2);  //开始时间
            $interval_days = 30/$data['column_num'];
        }
        $list = [];
        $column_num = $data['column_num'];
        $y = 0;
        $max = 0;
        for($i = $column_num; $i > 0 ; $i--){
            $endtime = date("Y-m-d H:i:s",strtotime($begintime)+$interval_days*24*3600);
            $count = Db::name('StoreShopView')
               ->where($where)
               ->whereTime('create_at', 'between', [$begintime, $endtime])
               ->count();
            if($count > $max){
                $max = $count;
            }
            if($data['goods_time_type'] == 1){
                $list['categories']['data'][$y] = date("n.j",strtotime($endtime)-2);
            }else{
                $list['categories']['data'][$y] = date("n.j",strtotime($begintime))."-".date("n.j",strtotime($endtime)-2);
            }
            $list['categories']['count'][$y] = $count;
            $y++;
            $begintime = $endtime;
        }
        if($max < 5){
            $max = 5;
        }else{
            $max = ceil($max/5)*5;
        }
        $list['max'] = $max;
        return $this->showMsg(1,'',$list);
    }
    
    /* 展厅收藏数据曲线统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天
     * column_num: X轴列数
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shopCollectStatistics(){
       $where = "shop_id = ".$this->shopId;
        $data = input();
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
        $interval_days = 0;
        if($data['goods_time_type'] == 1){
            $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600+2);  //开始时间
            $interval_days = 7/$data['column_num'];
        }else if($data['goods_time_type'] == 2){
            $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600+2);  //开始时间
            $interval_days = 14/$data['column_num'];
        }else if($data['goods_time_type'] == 3){
            $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600+2);  //开始时间
            $interval_days = 30/$data['column_num'];
        }
        $list = [];
        $column_num = $data['column_num'];
        $y = 0;
        $max = 0;
        for($i = $column_num; $i > 0 ; $i--){
            $endtime = date("Y-m-d H:i:s",strtotime($begintime)+$interval_days*24*3600);
            $count = Db::name('StoreShopCollect')
               ->where($where.' and status=1')
               ->whereTime('create_at', 'between', [$begintime, $endtime])
               ->count();
            if($count > $max){
                $max = $count;
            }
            if($data['goods_time_type'] == 1){
                $list['categories']['data'][$y] = date("n.j",strtotime($endtime)-2);
            }else{
                $list['categories']['data'][$y] = date("n.j",strtotime($begintime))."-".date("n.j",strtotime($endtime)-2);
            }
            $list['categories']['count'][$y] = $count;
            $y++;
            $begintime = $endtime;
        }
        if($max < 5){
            $max = 5;
        }else{
            $max = ceil($max/5)*5;
        }
        $list['max'] = $max;
        return $this->showMsg(1,'',$list);
    }
    
    /* 展厅浏览用户分布区域统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天 4.所有
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shopBrowseUser(){
        $data = input();
        $where = "shop_id = ".$this->shopId;
        if($data['goods_time_type'] < 4){
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
            if($data['goods_time_type'] == 1){
                $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 2){
                $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 3){
                $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600);  //开始时间
            }
        }
        if($data['goods_time_type'] < 4){
            $list = Db::name('StoreShopView')
                    ->field("area,count(id) as count")
                    ->where($where)
                    ->whereTime('create_at', 'between', [$begintime, $endtime])
                    ->group("area")
                    ->select();
        }else{
            $list = Db::name('StoreShopView')
                    ->field("area,count(id) as count")
                    ->where($where)
                    ->group("area")
                    ->select();
        }
        $info = [];
        $areaColor = Config::get('mapi.areaColor');
        foreach ($list as $key=>$val){
        	$val['area']=$val['area']?$val['area']:'其它';
            if(strlen($val['area']) > 12){
        		$area = substr($val['area'],0,6);
        	}else{
        		$area = $val['area'];
        	}
            $info[$key]['name'] = $area;
            $info[$key]['data'] = $val['count'];
            if(array_key_exists($val['area'],$areaColor)){
                $info[$key]['color'] = $areaColor[$val['area']];
            }else{
                $str='ABCDEF1234567890';
                $randStr = str_shuffle($str);//打乱字符串
                $rands= substr($randStr,0,6);//substr(string,start,length);返回字符串的一部分
                $info[$key]['color'] = "#".$rands;
            }
        }
        return $this->showMsg(1,'',$info);
    }
    
    /* 展厅收藏用户分布区域统计
     * goods_time_type: 日期类型 1.7天 2.14天 3.30天 4.所有
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shopCollectUser(){
        $data = input();
        $where = "status = 1 and shop_id = ".$this->shopId;
        if($data['goods_time_type'] < 4){
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $endtime = date("Y-m-d H:i:s",$endToday); //结束时间
            if($data['goods_time_type'] == 1){
                $begintime = date("Y-m-d H:i:s",$endToday-7*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 2){
                $begintime = date("Y-m-d H:i:s",$endToday-14*24*3600);  //开始时间
            }else if($data['goods_time_type'] == 3){
                $begintime = date("Y-m-d H:i:s",$endToday-30*24*3600);  //开始时间
            }
        }
        if($data['goods_time_type'] < 4){
            $list = Db::name('StoreShopCollect')
                    ->field("area,count(id) as count")
                    ->where($where.' and status=1')
                    ->whereTime('create_at', 'between', [$begintime, $endtime])
                    ->group("area")
                    ->select();
        }else{
            $list = Db::name('StoreShopCollect')
                    ->field("area,count(id) as count")
                    ->where($where.' and status=1')
                    ->group("area")
                    ->select();
        }
        $info = [];
        $areaColor = Config::get('mapi.areaColor');
        foreach ($list as $key=>$val){
        	$val['area']=$val['area']?$val['area']:'其它';
            if(strlen($val['area']) > 12){
        		$area = substr($val['area'],0,6);
        	}else{
        		$area = $val['area'];
        	}
            $info[$key]['name'] = $area;
            $info[$key]['data'] = $val['count'];
            if(array_key_exists($val['area'],$areaColor)){
                $info[$key]['color'] = $areaColor[$val['area']];
            }else{
                $str='ABCDEF1234567890';
                $randStr = str_shuffle($str);//打乱字符串
                $rands= substr($randStr,0,6);//substr(string,start,length);返回字符串的一部分
                $info[$key]['color'] = "#".$rands;
            }
        }
        return $this->showMsg(1,'',$info);
    }
    
    /**
     * 关键词搜索统计
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * zs
     *
     * @update version v1.1 lx 2020.05.10 调整搜索条件
     */
    public function keywords(){
        $list = Db::name('SystemKeywords')->where('is_delete = 1')->order('count desc')->limit(20)->select();
        return $this->showMsg(1,'',$list);
    }

}