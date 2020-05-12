<?php



namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 广告位管理
 * Class News
 * @package app\website\controller
 */
class AdLocation extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreAdLocation';

    /**
     * 广告位管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '广告位管理';
        $this->_query($this->table)->where(['is_delete' => '2'])->equal('platform_type')->like('name')->order('id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
        $platform_type[1] = array('id' => 1,'name' => "展厅端");
        $platform_type[2] = array('id' => 2,'name' => "用户端");
        $this->platform_type = $platform_type;
		foreach ($data as &$vo) {
			if($vo['platform_type'] == 1){
                $vo['platform_name'] = "展厅端";
            }else if($vo['platform_type'] == 2){
                $vo['platform_name'] = "用户端";
            }else{
            	$vo['platform_name'] = "";
            }
		}
//		print_r($data);die;
    }

    /**
     * 添加广告位信息
     * @return mixed
     */
    public function add()
    {
        $this->title = '添加广告位信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑广告位信息
     * @return mixed
     */
    public function edit()
    {
        $this->title = '编辑广告位信息';
        return $this->_form($this->table, 'form');
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
        $platform_type[1] = array('id' => 1,'name' => "展厅端");
        $platform_type[2] = array('id' => 2,'name' => "用户端");
        $this->platform_type = $platform_type;
    }

    /**
     * 表单结果处理
     * @param boolean $result
     */
    protected function _form_result($result)
    {
        if ($result && $this->request->isPost()) {
            $this->success('广告位保存成功！');
        }
    }
    
     /**
     * 禁用广告位
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用广告位
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除广告位
     */
    public function remove()
    {
        $this->_save($this->table, ['is_delete' => '1']);
    }

}