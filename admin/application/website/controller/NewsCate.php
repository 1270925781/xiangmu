<?php



namespace app\website\controller;

use library\Controller;

/**
 * 新闻分类管理
 * Class NewsCate
 * @package app\website\controller
 */
class NewsCate extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreNewsCate';

    /**
     * 新闻分类管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '新闻分类管理';
        $where = ['is_delete' => '2'];
        $this->_query($this->table)->like('title')->equal('status')->where($where)->order('sort asc,id desc')->page();
    }

    /**
     * 添加新闻分类
     * @return mixed
     */
    public function add()
    {
        $this->title = '添加新闻分类';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑添加新闻分类
     * @return mixed
     */
    public function edit()
    {
        $this->title = '编辑新闻分类';
        return $this->_form($this->table, 'form');
    }

    /**
     * 禁用添加新闻分类
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用新闻分类
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除新闻分类
     */
    public function remove()
    {
        $this->_save($this->table,['is_delete'=>1]);
    }

}