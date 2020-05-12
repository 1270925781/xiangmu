<?php
namespace app\uapi\controller;
use think\Db;

class Shop extends Common
{
    protected $_table;
    public function __construct()
    {
    	parent::__construct();
        $this->_table = 'StoreShop';
    }

    /**
     * 获取展厅列表
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopList()
    {
        $data = input();
        $pageLimit = 5;
        $where = 'is_freeze=2 and is_delete=2';
        if($data['label']){
            $label = $data['label'];
            $where .= " and find_in_set('$label',labels)";
            $labelId = Db::name('StoreShopLabel')->field('id')->where(['title'=>$label])->find()['id'];
            if($this->uid){
                $search['uid'] = $this->uid;
            }
            $search['lid'] = $labelId;
            Db::name('SystemSearchlabel')->insert($search);
            Db::name('StoreShopLabel')->where(['title'=>$data['label']])->setInc('count');
        }
        if($data['search']){
            $search = $data['search'];
            $where .= " and title like '%$search%'";
        }
        if($data['is_recom']){
            $time = time();
            $where .= " and is_home=1 and home_start_time<=$time and home_end_time>$time";
            $sort = 'home_sort desc,sort desc,id desc';
        }else{
            $sort = 'sort desc,id desc';
        }
        if($data['area']){
            $area = $data['area'];
            $where .= " and province= '$area' ";
        }
        
        if($data['is_factory']){
            $where .= ' and cate_id=2';
        }
        $field = 'id,title,labels,view_num,like_num,collect_num,province,city,area,address,cover_image';
        if($data['near_me']){
            $longitude = isset($data['longitude'])?$data['longitude']:'116.224513';
        	$latitude = isset($data['latitude'])?$data['latitude']:'40.004293';
            $lm    = (intval($data['page']) - 1) * $pageLimit;
            $limit = " limit $lm,$pageLimit";
            $shopList = $this->getDistance($longitude,$latitude,$field,$where,$limit);
        }else{
            $shopList = Db::name($this->_table)->field($field)->where($where)->page($data['page'],$pageLimit)->order($sort)->select();
        }
        if($shopList){
            foreach ($shopList as &$vo){
                if($vo['labels']){
                    $vo['labels'] = explode(',',$vo['labels']);
                }
                $vo['location'] =$vo['address'];
            }
        }

        $list = [];
        $list['shop_list'] = $shopList;
        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'',$list);
    }

    /**
     * 获取展厅所有标签
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLabels(){
         $labels = Db::name('StoreShopLabel')->where('is_freeze=2 and is_delete=2 and nowcount!=0')->order('sort desc,nowcount desc,id desc')->limit(50)->select();
        return $this->showMsg(1,'获取成功',$labels);
    }


}