<?php
namespace app\uapi\controller;
use  think\Db;

class Goods extends Common
{

    /**
     * 获取产品列表
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsList(){
        $data = input();
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and s.is_freeze = 2";
        if($data['search']){
            $search = $data['search'];
            $where .= " and g.title like '%$search%'";
        }
        if($data['cate_id']){
            if($data['cate_id'] != 0){
                $where .= " and g.cate_id=".$data['cate_id'];
            }
        }
        if($data['subcate_id']){
            $where .= " and g.subcate_id=".$data['subcate_id'];
        }
        if($data['brand_id']){
            $where .= " and g.brand_id=".$data['brand_id'];
        }
        $time = time();
        if($data['is_new']){
            $where .= " and g.is_new=1 and g.new_start_time<=$time and g.new_end_time>$time";
            $sort = 'g.new_sort desc,g.sort desc,g.id desc';
        }else{
            $sort = 'g.sort desc,g.id desc';
        }
        if($data['is_hot']){
            $where .= " and g.is_hot=1 and g.hot_start_time<=$time and g.hot_end_time>$time";
            $sort = 'g.hot_sort desc,g.sort desc,g.id desc';
        }else{
            $sort = 'g.sort desc,g.id desc';
        }
        $page = $data['page'];
        $pageLimit = 15;
        $field = 'g.id as gid,g.title,g.cover_image,g.view_num,g.like_num,g.collect_num,s.id as sid,s.title as shop_name';
        if($data['near_me']) {
            $data['longitude'] = '116.224513';
            $data['latitude'] = '40.004293';
            $lm = (intval($data['page']) - 1) * $pageLimit;
            $limit = " limit $lm,$pageLimit";

            $list['goods_list'] = $this->getDistance($data['longitude'], $data['latitude'], $field, $where, $limit,2);
        }else{
            $list['goods_list'] = Db::name('StoreGoods')->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->field($field)->where($where)->order($sort)->page($page,$pageLimit)->select();
        }
        $list['page_limit'] =$pageLimit;

        return $this->showMsg(1,'获取成功',$list);
    }

    /**
     * 获取产品分类
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsCate()
    {
        $data = input();
        $where = "is_delete=2 and status=1";
        if($data['cate_id'] == 0){
            $where .= " and pid=0";
        }else{
            $where .= " and pid=".$data['cate_id'];
        }
        $list = Db::name('StoreGoodsCate')->field('id,title')->where($where)->order('sort desc')->select();

        foreach ($list as &$vo){
            $subCate = Db::name('StoreGoodsCate')->field('id,title')->where(['pid'=>$vo['id'],'is_delete'=>2,'status'=>1])->order('sort desc,id desc')->select();
            array_unshift($subCate,array('id'=>100,'title'=>'全部'));
            $vo['sub_cate'] = $subCate;
        }

        return $this->showMsg(1,'获取成功',$list);
    }

    /**
     * 获取产品品牌
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsBrand(){
        $brandList = Db::name('StoreGoodsBrand')->field('id,title')->where(['is_freeze'=>2,'is_delete'=>2])->order('sort desc,id desc')->select();
        return $this->showMsg(1,'',$brandList);
    }

}