<?php



namespace app\store\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 意见反馈管理
 * Class News
 * @package app\store\controller
 */
class Feedback extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreFeedback';

    /**
     * 意见反馈管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '意见反馈管理';
		$platform_type = input('platform_type')?input('platform_type'):1;//默认用户端
		if($platform_type==1){
			$fields = "f.*,m.username";
			$this->_query($this->table)->alias('f')->field($fields)->leftJoin('store_member m','f.user_id=m.id')->like('username')->equal('platform_type')->dateBetween('f.addtime#addtime')->where(['f.is_delete' => '2'])->order('f.id desc')->page();
		}else{
			$fields = "f.*,u.username";
			$this->_query($this->table)->alias('f')->field($fields)->leftJoin('system_user u','f.user_id=u.id')->like('username')->equal('platform_type')->dateBetween('f.addtime#addtime')->where(['f.is_delete' => '2'])->order('f.id desc')->page();
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
        foreach($data as &$vo){
			$vo['platform']=$vo['platform_type']==1?'展厅平台':'用户平台';
         }
    }


    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function _form_filter(&$data)
    {
        
    }

    /**
     * 表单结果处理
     * @param boolean $result
     */
    protected function _form_result($result)
    {
       
    }
    
    /**
     * 意见处理
     */
    public function handle()
    {
        $id = $_POST['id'];
        $updatetime = date('Y-m-d H:i:s', time());
        $adminid = session('user.id');
        $res = Db::name('StoreFeedback')->where(['id' => $id])->update(['is_handle' => '1','update_uid' => $adminid,'updatetime' => $updatetime]);
        $this->success('处理成功！');
    }

}