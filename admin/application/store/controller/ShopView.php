<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 展厅浏览记录管理
 * Class ShopView
 * @package app\store\controller
 */
class ShopView extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreShopView';

    /**
     * 展厅浏览记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '展厅浏览记录';
        $user = session('user');
        $fields = "v.*,s.title,m.username";
        $where = '1=1';
        if($user['authorize'] == 2){
            $where .= " and v.shop_id=".$user['shop_id'];
        }
        $this->_query($this->table)->alias('v')->field($fields)->leftJoin('store_shop s','v.shop_id=s.id')->leftJoin('store_member m','v.uid=m.id')->dateBetween('v.create_at#create_at')->like('s.title#title,v.area#area,m.username#username')->where($where)->order('v.id desc')->page();

    }
}