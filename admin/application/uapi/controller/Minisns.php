<?php
namespace app\uapi\controller;
use think\Db;

class Minisns extends Common
{
	/**
     * 获取微社区展厅列表
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        $data = input();
        $page = $data['page'];
        $pageLimit = 2;
        $time = time();
        $where = "is_freeze=2 and is_delete=2 and is_minisns=1 and minisns_start_time<=$time and minisns_end_time>$time";
        $shopList = Db::name('StoreShop')->where($where)->order('minisns_sort desc,sort desc,id desc')->page($page,$pageLimit)->select();
        foreach ($shopList as &$vo){
            if($vo['labels']){
                $vo['labels'] = explode(',',$vo['labels']);
            }
            $vo['recommend_list'] = $this->getRecommendGoods($vo['id']);
        }
        $list['shop_list'] = $shopList;
        $list['page_limit'] = $pageLimit;

        return $this->showMsg(1,'获取成功',$list);
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
        $where = "is_show_main=1 and is_show_second=1 and is_delete=2 and is_recommend=1 and shop_id=$id";
        $list = Db::name('StoreGoods')->field('id,title,cover_image,view_num,like_num,collect_num')->where($where)->order('recommend_sort desc,sort desc,id desc')->limit(4)->select();
        return $list;
    }
}