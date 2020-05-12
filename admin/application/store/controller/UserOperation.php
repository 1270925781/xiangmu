<?php



namespace app\store\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 用户操作记录
 * Class News
 * @package app\store\controller
 */
class UserOperation extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreUserOperation';

    /**
     * 用户操作记录
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '用户操作记录';
        $fields = "up.*,m.username,m.phone";
        $platform_type = 2;
        if(isset($_GET['platform_type'])){
            $platform_type = $_GET['platform_type'];
        }
        if($platform_type == 2){
            $this->_query($this->table)->alias('up')->field($fields)->leftJoin('store_member m','up.user_id=m.id')->where('up.platform_type = 2')->equal('username,phone')->like('content')->dateBetween('up.addtime#addtime')->order('up.id desc')->page();
        }else{
            $this->_query($this->table)->alias('up')->field($fields)->leftJoin('system_user m','up.user_id=m.id')->where('up.platform_type = 1')->equal('username,phone')->like('content')->dateBetween('up.addtime#addtime')->order('up.id desc')->page();
        }        
        
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
        $platform_type[1] = array('id' => 2,'name' => "用户端");
        $platform_type[2] = array('id' => 1,'name' => "展厅端");
        $this->platform_type = $platform_type;
    }
}