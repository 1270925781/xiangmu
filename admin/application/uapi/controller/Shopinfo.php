<?php
namespace app\uapi\controller;
use think\Db;

class Shopinfo extends Base
{
    protected $_table;
    public function __construct()
    {
        parent::__construct();
        $this->_table = 'StoreShop';
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
        $area = $data['area'];
        $view = ['shop_id'=>$id,'uid'=>$this->uid];
        if($area){
            $view['area'] = $area;
        }
        Db::name('StoreShopView')->insert($view);
        Db::name($this->_table)->where(['id'=>$id])->setInc('view_num');
        $where = "is_freeze=2 and is_delete=2 and id=$id";
        $info = Db::name($this->_table)->where($where)->find();

        if($info['slide_show']){
            $info['slide_show'] = explode('|',$info['slide_show']);
        }
        if($info['labels']){
            $info['labels'] = explode(',',$info['labels']);
        }
        $info['location'] = $info['address'];

        $info['status'] = $this->getStatus($id);
        $info['recommend_goods'] = $this->getRecommendGoods($id);

        return $this->showMsg(1,'',$info);
    }

    /**
     * 获取展厅点赞/收藏状态
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStatus($id){
        $likeStatus = Db::name('StoreShopLike')->where(['shop_id'=>$id,'uid'=>$this->uid])->field('status')->find()['status'];
        $collectStatus = Db::name('StoreShopCollect')->where(['shop_id'=>$id,'uid'=>$this->uid])->field('status')->find()['status'];
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
     * 展厅点赞接口
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function shopLike(){
        $data = input();
        $id = $data['id'];
        $type = $data['type'];
        $table = 'StoreShopLike';
        if($type == 1){                 //点赞
            $likeInfo = Db::name($table)->where(['shop_id'=>$id,'uid'=>$this->uid])->field('id')->find();
            if(empty($likeInfo)){
                Db::name($table)->insert([
                    'shop_id'=>$id,
                    'uid'=>$this->uid,
                    'status'=>1
                ]);
            }else{
                Db::name($table)->where(['shop_id'=>$id,'uid'=>$this->uid])->update([
                    'status'=>1,
                    'create_at'=>date("Y-m-d H:i:s",time())
                ]);
            }
            Db::name($this->_table)->where(['id'=>$id])->setInc('like_num');
        }elseif ($type == 2){           //取消点赞
            Db::name($table)->where(['shop_id'=>$id,'uid'=>$this->uid])->update([
                'status'=>2
            ]);
            $like_num = Db::name($this->_table)->where(['id'=>$id])->field('like_num')->find();
            if($like_num['like_num'] > 0){
            	Db::name($this->_table)->where(['id'=>$id])->setDec('like_num');
            }
        }

        return $this->showMsg(1);

    }

    /**
     * 展厅收藏接口
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function shopCollect(){
        $data = input();
        $id = $data['id'];
        $type = $data['type'];
        $area = $data['area'];
        $table = 'StoreShopCollect';
        if($type == 1){                 //收藏
            $collectInfo = Db::name($table)->where(['shop_id'=>$id,'uid'=>$this->uid])->field('id')->find();
            if(empty($collectInfo)){
                Db::name($table)->insert([
                    'shop_id'=>$id,
                    'uid'=>$this->uid,
                    'status'=>1,
                    'area'=>$area
                ]);
            }else{
                Db::name($table)->where(['shop_id'=>$id,'uid'=>$this->uid])->update([
                    'status'=>1
                ]);
            }
            Db::name($this->_table)->where(['id'=>$id])->setInc('collect_num');
        }elseif ($type == 2){           //取消收藏
            Db::name($table)->where(['shop_id'=>$id,'uid'=>$this->uid])->update([
                'status'=>2
            ]);
            $collect_num = Db::name($this->_table)->where(['id'=>$id])->field('collect_num')->find();
        	if($collect_num['collect_num'] > 0){
            	Db::name($this->_table)->where(['id'=>$id])->setDec('collect_num');
        	}
        }

        return $this->showMsg(1);

    }

    /**
     * 获取本店推荐产品
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRecommendGoods($id)
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

    /**
     * 开通展厅接口
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addShop(){
        $data = input();
        if(Db::name('StoreShop')->where("is_delete = 2 and title = '".$data['name']."'")->count() > 0){
            return $this->showMsg(-1,'展厅名称已存在');
        }
        $data['uid'] = $this->uid;
        $data['create_at'] = date("Y-m-d H:i:s",time());
        $shopid = Db::name('StoreShopAudit')->insertGetId($data);
        if($shopid > 0){
            return $this->showMsg(1,'申请成功');
        }
         return $this->showMsg(-1,'申请失败');
    }
}