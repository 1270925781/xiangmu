<?php
namespace app\uapi\controller;
use think\Db;

class Goodscate extends Common
{
    
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
        $where = "is_delete=2 and status = 1";
        if($data['cate_id'] == 0){
            $where .= " and pid=0";
        }else{
            $where .= " and pid=".$data['cate_id'];
        }
        $cateList = Db::name('StoreGoodsCate')->field('id,title')->where($where)->order('sort desc')->select();

        foreach ($cateList as &$vo){
            $vo['select'] = true;
        }

        return $this->showMsg(1,'获取成功',$cateList);
    }

    /**
     * 获取产品分类页轮播图
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBanner(){
        $today = date('Y-m-d H:i:s',time());
        $where = "location_id=3 and status=1 and is_delete=2 and platform_type=2";
        $list = Db::name('StoreAds')->field('id,ad_img,ad_type,route_id,route,endtime')->where($where)->whereTime('endtime','>',$today)->whereTime('starttime','<=',$today)->order('sort desc')->select();
        return $this->showMsg(1,'获取成功',$list);
    }

    /**
     * 获取产品列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsList()
    {
        $data = input();
        $where = "g.is_show_main=1 and g.is_show_second=1 and g.is_delete=2 and s.is_freeze = 2";
        if($data['cate_id']){
            $cate_id = $data['cate_id'];
            if($cate_id != 0){
                $where .= " and g.cate_id=$cate_id";
            }
        }
        if($data['subcate_id']){
            $subCate_id = $data['subcate_id'];
            if($subCate_id != 0){
                $where .= " and g.subcate_id=$subCate_id";
            }
        }
        $page = $data['page'];
        $pageLimit = 15;
        $list['goods_list'] = Db::name('StoreGoods')->alias('g')->leftJoin('store_shop s','g.shop_id=s.id')->field('g.id,g.title,g.cover_image')->where($where)->order('g.id desc')->page($page,$pageLimit)->select();

        $list['page_limit'] =$pageLimit;

        return $this->showMsg(1,'获取成功',$list);
    }


}