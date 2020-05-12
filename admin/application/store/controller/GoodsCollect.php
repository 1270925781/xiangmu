<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 产品收藏记录管理
 * Class GoodsCollect
 * @package app\store\controller
 */
class GoodsCollect extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreGoodsCollect';

    /**
     * 产品收藏记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '产品收藏记录';
        $user = session('user');
        $fields = "v.*,g.title,m.username";
        $where = '1=1';
        if($user['authorize'] == 2){
            $where .= " and g.shop_id=".$user['shop_id'];
        }
        $this->_query($this->table)->alias('v')->field($fields)->leftJoin('store_goods g','v.goods_id=g.id')->leftJoin('store_member m','v.uid=m.id')->dateBetween('v.create_at#create_at')->like('g.title#title,v.area#area,m.username#username')->where($where)->order('v.id desc')->page();
    }
}