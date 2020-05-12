<?php



namespace app\store\controller;

use library\Controller;

/**
 * 省份管理
 * Class Area
 * @package app\store\controller
 */
class ExpressProvince extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreExpressProvince';

    /**
     * 省份管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '省份管理';
        $this->_query($this->table)->like('title')->equal('status')->dateBetween('create_at')->where(['is_delete'=>2])->order('sort asc,id desc')->page();
    }


    /**
     * 添加省份
     */
    public function add()
    {
        $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑省份
     */
    public function edit()
    {
        $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 启用省份
     */
    public function resume()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 禁用省份
     */
    public function forbid()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 删除省份
     */
    public function remove()
    {
        $this->applyCsrfToken();
        $this->_save($this->table,['is_delete'=>1]);
    }

}