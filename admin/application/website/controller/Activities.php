<?php
namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 活动信息管理
 * Class Activities
 * @package app\website\controller
 */
class Activities extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreActivities';

    /**
     * 活动信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '活动/展会信息管理';
        $this->_query($this->table)->equal('status,cate,platform_type')->like('name,title')->where(['is_delete' => '2'])->order('sort desc,id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
        foreach ($data as &$vo) {
            if($vo['cate'] == 1){
                $vo['cate'] = '活动';
            }else{
                $vo['cate'] = '展会';
            }
            if($vo['type'] == 1){
                $vo['type'] = '收费';
            }else{
                $vo['type'] = '免费';
            }
            if($vo['platform_type'] == 1){
                $vo['plat_type'] = "展厅平台";
            }else if($vo['platform_type'] == 2){
                $vo['plat_type'] = "用户平台";
            }
			$vo['start_time'] = date("Y-m-d H:i",$vo['start_time']);
			$vo['end_time'] = date("Y-m-d H:i",$vo['end_time']);
            //$vo['content'] = strip_tags ($vo['content']);
        }
    }

    /**
     * 添加活动信息
     * @return mixed
     */
    public function add()
    {
        //$this->title = '添加活动信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑活动信息
     * @return mixed
     */
    public function edit()
    {
        //$this->title = '编辑活动信息';
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
        if ($this->request->isPost()) {
        	if($data['act_time']) {
                $data['act_time'] = strtotime($data['act_time']);
            }else{
                $data['act_time'] = date('Y-m-d H:i:s',time());
            }
            if($data['start_time']) {
                $data['start_time'] = strtotime($data['start_time']);
            }
            // else{
            //     $data['start_time'] = date('Y-m-d H:i:s',time());
            // }
            if($data['end_time']){
                $data['end_time'] = strtotime($data['end_time']);
            }
            // else{
            //     $data['end_time'] = date('Y-m-d H:i:s',time());
            // }
            if(empty($data['host_logo'])){
                $this->error('请上传主办方公司logo');
            }
            if(empty($data['cover_image'])){
                $this->error('请上传活动封面图');
            }
            if(empty($data['details_image'])){
                $this->error('请上传活动详情图');
            }
            if(empty($data['longitude'])){
                $this->error('请获取场地位置');
            }
             if(empty($data['content'])){
                $this->error('请输入活动介绍');
            }
        }else{
            if(empty($data)){
            	$data['longitude'] = '';
            	$data['latitude'] = '';
            	// $data['act_time'] = date('Y-m-d H:i:s',time());
             //   $data['start_time'] = date('Y-m-d H:i:s',time());
             //   $data['end_time'] = date('Y-m-d H:i:s',time());
            }else{
            	 if($data['act_time']){
                    $data['act_time'] = date('Y-m-d H:i:s',$data['act_time']);
                }else{
                    $data['act_time'] = date('Y-m-d H:i:s',time());
                }
                if($data['start_time']){
                    $data['start_time'] = date('Y-m-d H:i:s',$data['start_time']);
                }else{
                    $data['start_time'] = date('Y-m-d H:i:s',time());
                }
                if($data['end_time']){
                    $data['end_time'] = date('Y-m-d H:i:s',$data['end_time']);
                }else{
                    $data['end_time'] = date('Y-m-d H:i:s',time());
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
     * 禁用活动信息
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用活动信息
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除活动信息
     */
    public function remove()
    {
        $this->_save($this->table,['is_delete'=>1]);
    }
}