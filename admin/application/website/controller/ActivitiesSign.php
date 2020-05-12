<?php
namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 活动信息管理
 * Class Activities
 * @package app\website\controller
 */
class ActivitiesSign extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreActivitiesSign';

	/**
     * 活动报名信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '活动/展会报名信息管理';
        $this->_query($this->table)->alias('s')
			 ->leftJoin('store_activities a','a.id=s.act_id')
			 ->equal('s.status#status,s.platform_type#platform_type')
			 ->like('s.linkman#linkman,s.linkphone#linkphone,a.name#name')
			 ->dateBetween('s.create_at#create_at')
			 ->field('s.*,a.name')
			 ->where('s.is_delete=2')
			 ->order('s.id desc')
			 ->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
        foreach ($data as &$vo) {
            if($vo['status'] == 1){
                $vo['status_name'] = '已处理';
            }else{
                $vo['status_name'] = '未处理';
            }
        }
    }

    /**
     * 标记处理报名
     */
    public function signstatus()
    {
        $this->_save($this->table, ['status' => '1']);
    }

	/**
     * 删除活动报名信息
     */
    public function signremove()
    {
        $this->_save($this->table,['is_delete'=>1]);
    }

}