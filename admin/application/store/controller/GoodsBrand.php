<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 产品品牌记录管理
 * Class GoodsBrand
 * @package app\store\controller
 */
class GoodsBrand extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreGoodsBrand';

    /**
     * 产品品牌记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '产品品牌记录';
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
//        $shopList = Db::name('StoreShop')->whereIn('id', array_unique(array_column($data, 'shop_id')))->select();
//        foreach ($data as &$vo) {
//            foreach ($shopList as $shop) if ($shop['id'] === $vo['shop_id']) {
//                $vo['shop_name'] = $shop['title'];
//            }
//        }
    }

    /**
     * 添加产品品牌
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑产品品牌
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit(){
        return $this->_form($this->table, 'form');
    }

	/**
     * 表单数据处理
     * @param $data
     */
    protected function _form_filter(&$data){
        if($this->request->post()){
            if($data['title']){
                if(Db::name($this->table)->where(['title'=>$data['title']])->count()>0){
                    $this->error('品牌名称重复');
                }
            }
            
        }
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
     * 禁用品牌
     */
    public function forbid()
    {
    	$id = input('id');
    	$goodsList = Db::name('StoreGoods')->where(['brand_id'=>$id,'is_delete'=>2])->select();
        if($goodsList){
            $this->error('禁用失败，此品牌下还存在使用中的产品');
        }
        $this->_save($this->table, ['is_freeze' => '1']);
    }

    /**
     * 启用品牌
     */
    public function resume()
    {
        $this->_save($this->table, ['is_freeze' => '2']);
    }
    
    /**
     * 删除品牌
     */
    public function remove()
    {
    	$id = input('id');
    	$goodsList = Db::name('StoreGoods')->where(['brand_id'=>$id,'is_delete'=>2])->select();
        if($goodsList){
            $this->error('删除失败，此品牌下还存在使用中的产品');
        }
    	
        $this->_save($this->table,['is_delete'=>1]);
    }
}