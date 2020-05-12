<?php
namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 服务审核信息管理
 * Class ServicesOrder
 * @package app\website\controller
 */
class ServicesOrder extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreServicesOrder';

    /**
     * 缴费续费订单
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '缴费续费订单';
        $user = session('user');
        $fields = "o.*,u.username";
        $where = '1=1';
        if($user['authorize'] == 2){
            $where = "s.shop_id=".$user['shop_id'];
        }
        $this->_query($this->table)->alias('o')->field($fields)->leftJoin('store_services s','o.service_id=s.id')->leftJoin('system_user u','o.uid=u.id')->like('o.service_name#service_name,u.username#username')->equal('o.type#type,o.pay_status#pay_status')->where($where)->order('o.id desc')->page();

    }

    /**
     * 列表数据处理
     * @param $data
     */
    public function _index_page_filter(&$data){
        foreach ($data as $k=>$v){
            $data[$k]['pay_price'] = $v['pay_price']/100;
        }
    }

}