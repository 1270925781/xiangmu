<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 产品分类管理
 * Class GoodsCate
 * @package app\store\controller
 */
class GoodsCate extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreGoodsCate';

    /**
     * 产品分类管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '产品分类管理';
        $where = ['is_delete' => '2','pid'=>0];
        $this->_query($this->table)->like('title')->equal('status')->where($where)->order('sort desc,id desc')->page();
    }

    /**
     * 查看二级分类
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function view_subcate(){
        $id = $this->request->get('id');
        $where = ['is_delete' => '2','pid'=>$id];
        $this->_query($this->table)->like('title')->equal('status')->where($where)->order('sort desc,id desc')->page();

    }

    /**
     * 添加产品分类
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '添加产品分类';
        return $this->_form($this->table, 'form');
    }

    /**
     * 添加二级分类
     */
    public function add_subcate(){
        $pid = $this->request->get('pid');
        $this->assign('pid',$pid);
        $this->assign('id',0);
        $this->fetch('sub_form');
    }

    /**
     * 编辑二级分类
     */
    public function edit_subcate(){
        $data = $this->request->get();
        $pid = $data['pid'];
        $id = $data['id'];
        $this->assign('pid',$pid);
        $this->assign('id',$id);
        $where = ['is_delete' => '2','id'=>$id];
        $query = Db::name($this->table)->where($where)->order('sort asc,id desc')->find();
        $this->assign('info',$query);
        $this->fetch('sub_form');
    }

    /**
     * 保存二级分类
     */
    public function save_subcate(){
       if($this->request->post()){
           $data = $this->request->post();
           $pid = $data['pid'];
           if($this->request->post('id')>0){
               $id = $data['id'];
               if(Db::name($this->table)->where("is_delete=2 and id!=$id and (pid=$pid or pid=0) and title='".$data['title']."'")->count() > 0){
                   $this->error('产品分类名称重复！');
               }
               Db::name($this->table)->where(['id'=>$id])->update($data);
           }else{
               if(Db::name($this->table)->where("is_delete=2 and (pid=$pid or pid=0) and title='".$data['title']."'")->count() > 0 ){
                   $this->error('产品分类名称重复！');
               }
               Db('StoreGoodsCate')->insert([
                   'pid'=>$data['pid'],
                   'title'=>$data['title'],
                   'desc'=>$data['desc']
               ]);
           }
           $this->success('操作成功');
       }
    }

    /**
     * 编辑添加产品分类
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑产品分类';
        return $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _form_filter(&$data){
        if($this->request->post()){
            if($this->request->post('id')){
                $id = $this->request->post('id');
                if($data['title']){
                    if(Db::name($this->table)->where("is_delete=2 and id!=$id and title='".$data['title']."'")->count() > 0){
                        $this->error('产品分类名称重复！');
                    }
                }
            }else{
            	if($data['title']){
                    if(Db::name($this->table)->where("is_delete=2 and title='".$data['title']."'")->count() > 0){
                        $this->error('产品分类名称重复！');
                    }
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
     * 禁用添加产品分类
     */
    public function forbid()
    {
        $data = input();
        $pid = $data['pid'];
        $id = $data['id'];
        if($pid == 0){
            $cateList = Db::name('StoreGoodsCate')->where(['pid'=>$id,'is_delete'=>2,'status'=>1])->select();
            if($cateList){
                $this->error('禁用失败，此分类下还存在使用中的二级分类');
            }else{
            	$goodsList = Db::name('StoreGoods')->where(['cate_id'=>$id,'is_delete'=>2])->select();
	            if($goodsList){
	                $this->error('禁用失败，此分类下还存在使用中的产品');
	            }
                $this->_save($this->table, ['status' => '0']);
            }
        }else{
            $goodsList = Db::name('StoreGoods')->where(['subcate_id'=>$id,'is_delete'=>2,'is_show_main'=>1,'is_show_second'=>1])->select();
            if($goodsList){
                $this->error('禁用失败，此二级分类下还存在使用中的产品');
            }else{
                $this->_save($this->table, ['status' => '0']);
            }
        }
    }

    /**
     * 启用产品分类
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除产品分类
     */
    public function remove()
    {
        $data = input();
        $pid = $data['pid'];
        $id = $data['id'];
        if($pid == 0){
            $cateList = Db::name('StoreGoodsCate')->where(['pid'=>$id,'is_delete'=>2,'status'=>1])->select();
            if($cateList){
                $this->error('删除失败，此分类下还存在使用中的二级分类');
            }else{
            	$goodsList = Db::name('StoreGoods')->where(['cate_id'=>$id,'is_delete'=>2])->select();
	            if($goodsList){
	                $this->error('删除失败，此分类下还存在使用中的产品');
	            }
                $this->_save($this->table,['is_delete'=>1]);
            }
        }else{
            $goodsList = Db::name('StoreGoods')->where(['subcate_id'=>$id,'is_delete'=>2])->select();
            if($goodsList){
                $this->error('删除失败，此二级分类下还存在使用中的产品');
            }else{
                $this->_save($this->table, ['is_delete' => '1']);
            }
        }
    }

}