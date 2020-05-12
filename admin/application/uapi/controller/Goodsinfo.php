<?php
namespace app\uapi\controller;
use think\Db;

class Goodsinfo extends Base
{
    protected $_table;
    public function __construct()
    {
        parent::__construct();
        $this->_table = 'StoreGoods';
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
        $area = $data['area'];
        if(Db::name('StoreGoods')->where(['id'=>$id,'is_delete'=>2,'is_show_main'=>1,'is_show_second'=>1])->count()==0){
    		return $this->showMsg(-1,'产品不存在');
        }
        $view = ['goods_id'=>$id,'uid'=>$this->uid];
        if($area){
            $view['area'] = $area;
        }
        Db::name('StoreGoodsView')->insert($view);
        Db::name($this->_table)->where(['id'=>$id])->setInc('view_num');
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and g.id=$id";
        $field = "g.*,s.contacts,s.phone,s.title as shop_name,s.province,s.city,s.area,s.address,s.longitude,s.latitude";
        $info = Db::name($this->_table)->alias('g')->field($field)->leftJoin('store_shop s','g.shop_id=s.id')->where($where)->find();
        $info['location'] = $info['province'].$info['city'].$info['area'].$info['address'];
        if($info['image']){
            $info['image'] = explode('|',$info['image']);
        }
        $info['content'] = str_replace('&times;','×',$info['content']);   //将乘号反转义
    	if($info['content_img']){
	        $imgArr = explode('|',$info['content_img']);
	        $str = '';
	        foreach ($imgArr as $vo){
	            $str .= "<p><img border='0' style='max-width:100%' title='image' src='$vo'/></p>";
	        }
        $info['content'] .= $str;
        }
        $info['content_img'] = explode('|',$info['content_img']);
        $info['status'] = $this->getStatus($id);

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
    public function getRecommendGoods()
    {
        $data = input();
        $id = $data['goods_id'];
        $shopId = $data['shop_id'];
        $page = $data['page'];
        $pageLimit = 4;
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and g.is_recommend=1 and g.shop_id=$shopId and g.id!=$id";
        $list['goods_list'] = Db::name($this->_table)->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->field('g.id,g.title as goods_name,g.cover_image,s.title as shop_name')->where($where)->order('g.recommend_sort desc,g.sort desc,g.id desc')->page($page,$pageLimit)->select();

        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'获取成功',$list);
    }

    /**
     * 获取产品点赞/收藏状态
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStatus($id){
        $likeStatus = Db::name('StoreGoodsLike')->where(['goods_id'=>$id,'uid'=>$this->uid])->field('status')->find()['status'];
        $collectStatus = Db::name('StoreGoodsCollect')->where(['goods_id'=>$id,'uid'=>$this->uid])->field('status')->find()['status'];
        if($likeStatus == 1){
            $likeStatus = true;
        }else{
            $likeStatus = false;
        }
        if($collectStatus == 1){
            $collectStatus = true;
        }else{
            $collectStatus = false;
        }
        $status['like_status'] = $likeStatus;
        $status['collect_status'] = $collectStatus;
        return $status;
    }

    /**
     * 产品点赞接口
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function goodsLike(){
        $data = input();
        $id = $data['id'];
        $type = $data['type'];
        $table = 'StoreGoodsLike';
        if($type == 1){                 //点赞
            $likeInfo = Db::name($table)->where(['goods_id'=>$id,'uid'=>$this->uid])->field('id')->find();
            if(empty($likeInfo)){
                Db::name($table)->insert([
                    'goods_id'=>$id,
                    'uid'=>$this->uid,
                    'status'=>1
                ]);
            }
            Db::name($this->_table)->where(['id'=>$id])->setInc('like_num');
        }elseif ($type == 2){           //取消点赞
        	Db::name($table)->where(['goods_id'=>$id,'uid'=>$this->uid])->update([
                	'status'=>2
            ]);
        	$num = Db::name($this->_table)->where(['id'=>$id])->field('like_num')->find();
        	if($num['like_num'] > 0){
            	Db::name($this->_table)->where(['id'=>$id])->setDec('like_num');
            	
            }
        }

        return $this->showMsg(1);

    }

    /**
     * 产品收藏接口
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function goodsCollect(){
        $data = input();
        $id = $data['id'];
        if(Db::name('StoreGoods')->where(['id'=>$id,'is_delete'=>2,'is_show_main'=>1,'is_show_second'=>1])->count()==0){
    		return $this->showMsg(-1,'产品不存在');
        }
        $type = $data['type'];
        $area = $data['area'];
        $table = 'StoreGoodsCollect';
        if($type == 1){                 //收藏
            $collectInfo = Db::name($table)->where(['goods_id'=>$id,'uid'=>$this->uid])->field('id')->find();
            if(empty($collectInfo)){
                Db::name($table)->insert([
                    'goods_id'=>$id,
                    'uid'=>$this->uid,
                    'status'=>1,
                    'area'=>$area
                ]);
            }else{
                Db::name($table)->where(['goods_id'=>$id,'uid'=>$this->uid])->update([
                    'status'=>1
                ]);
            }
            Db::name($this->_table)->where(['id'=>$id])->setInc('collect_num');
        }elseif ($type == 2){           //取消收藏
            Db::name($table)->where(['goods_id'=>$id,'uid'=>$this->uid])->update([
                'status'=>2
            ]);
            $num = Db::name($this->_table)->where(['id'=>$id])->field('collect_num')->find();
        	if($num['collect_num'] > 0){
            	Db::name($this->_table)->where(['id'=>$id])->setDec('collect_num');
            	
            }
        }

        return $this->showMsg(1);

    }




}