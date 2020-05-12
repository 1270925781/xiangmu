<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 展厅标签记录管理
 * Class ShopLabel
 * @package app\store\controller
 */
class ShopLabel extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreShopLabel';

    /**
     * 展厅标签记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '展厅标签记录';
        $this->_query($this->table)->like('title')->equal('is_freeze')->where(['is_delete'=>2])->order('sort desc,id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _page_filter(array &$data)
    {
        $shopList = Db::name('StoreShop')->whereIn('id', array_unique(array_column($data, 'shop_id')))->select();
        foreach ($data as &$vo) {
            foreach ($shopList as $shop) if ($shop['id'] === $vo['shop_id']) {
                $vo['shop_name'] = $shop['title'];
            }
        }
    }

    /**
     * 禁用标签
     */
    public function forbid()
    {
        $this->_save($this->table, ['is_freeze' => '1']);
    }

    /**
     * 启用标签
     */
    public function resume()
    {
        $this->_save($this->table, ['is_freeze' => '2']);
    }
    
    /**
     * 删除标签
     */
    public function remove()
    {
        $this->_save($this->table,['is_delete'=>1]);
    }
}