<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 展厅电话被打记录管理
 * Class ShopCall
 * @package app\store\controller
 */
class ShopCall extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreShopCall';

    /**
     * 展厅电话被打记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '展厅电话被打记录';
        $fields = "v.*,s.title,m.username";
         $this->_query($this->table)->alias('v')->field($fields)->leftJoin('store_shop s','v.shop_id=s.id')->leftJoin('store_member m','v.uid=m.id')->dateBetween('v.create_at#create_at')->like('s.title#title,m.username#username')->order('v.id desc')->page();
        
    }
}