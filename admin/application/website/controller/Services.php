<?php
namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 服务信息管理
 * Class Services
 * @package app\website\controller
 */
class Services extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreServices';

    /**
     * 服务信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '服务信息管理';
        $this->_query($this->table)->equal('is_freeze')->like('title')->where('is_delete=2')->order('sort asc,id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     */
    protected function _index_page_filter(&$data){
    }

    /**
     * 添加服务信息
     * @return mixed
     */
    public function add()
    {
        //$this->title = '添加服务信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑服务信息
     * @return mixed
     */
    public function edit()
    {
        //$this->title = '编辑服务信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $data
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

    /**
     * 禁用服务信息
     */
    public function forbid()
    {
        $this->_save($this->table, ['is_freeze' => '1']);
    }

    /**
     * 启用服务信息
     */
    public function resume()
    {
        $this->_save($this->table, ['is_freeze' => '2']);
    }

    /**
     * 删除服务信息
     */
    public function remove()
    {
        $this->_save($this->table, ['is_delete' => '1']);
    }

}