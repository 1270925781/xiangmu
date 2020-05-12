<?php
namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 用户服务信息管理
 * Class UsersService
 * @package app\website\controller
 */
class UsersService extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreUsersService';

    /**
     * 展厅服务信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '商户服务列表';
        $fields = 'us.*,ws.title,m.username';
        $user = session('user');
        $where = '1=1';
        if($user['authorize'] == 2){
            $where = "us.uid=".$user['id'];
        }
        $this->_query($this->table)->alias('us')->field($fields)->leftJoin('store_services ws', 'us.service_id=ws.id')->leftJoin('system_user m','us.uid=m.id')->like('ws.title#title')->equal('m.username#username')->dateBetween('us.end_time#end_time')->where($where)->order('us.id desc')->page();

    }

    /**
     * 数据列表处理
     * @param array $data
     */
    protected function _index_page_filter(&$data){
        foreach ($data as $k=>$v){
            $change = time() + 604800;
            if(strtotime($v['end_time']) >= $change){
                $data[$k]['is_over'] = 0;
            }else{
                $data[$k]['is_over'] = 1;
            }
        }
    }


}