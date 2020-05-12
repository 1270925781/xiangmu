<?php



namespace app\store\controller;

use library\Controller;

/**
 * 展厅分类管理
 * Class ShopCate
 * @package app\store\controller
 */
class ShopCate extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreShopCate';

    /**
     * 展厅分类管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '展厅分类管理';
        $where = ['is_delete' => '2'];
        $this->_query($this->table)->like('title')->equal('status')->where($where)->order('id desc')->page();
    }

    /**
     * 添加店铺分类
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '添加展厅分类';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑添加展厅分类
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑展厅分类';
        return $this->_form($this->table, 'form');
    }

    /**
     * 禁用添加展厅分类
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用展厅分类
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除展厅分类
     */
    public function remove()
    {
        $this->_save($this->table,['is_delete'=>1]);
    }

}