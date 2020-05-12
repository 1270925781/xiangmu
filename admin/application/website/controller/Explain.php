<?php



namespace app\website\controller;

use library\Controller;
use think\Db;

/**
 * 协议说明管理
 * Class News
 * @package app\website\controller
 */
class Explain extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'SystemExplain';

    /**
     * 消息信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '协议说明管理';
        $this->_query($this->table)->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
    	// var_dump($data);die;
    }

    /**
     * 添加消息信息
     * @return mixed
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑消息信息
     * @return mixed
     */
    public function edit()
    {
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
        
    }

    /**
     * 表单结果处理
     * @param boolean $result
     */
    protected function _form_result($result)
    {
        if ($result && $this->request->isPost()) {
            $this->success('操作成功！');
        }
    }
}