<?php
namespace app\admin\controller;

use library\Controller;
use think\Db;

/**
 * 热搜关键词管理
 * Class HotKeywords
 * @package app\admin\controller
 */
class HotKeywords extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'SystemKeywords';

	//热搜词类型
	protected $keycatelist = array('1'=>'展厅','2'=>'产品');

    /**
     * 热搜关键词记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '热搜关键词记录';
        $this->_query($this->table)->like('title')->equal('keycate')->where(['is_delete'=>2])->order('sort desc,id desc')->page();
		$this->assign('keycatelist', $this->keycatelist);
    }

    /**
     * 列表数据处理
     * @param $data
     */
    public function _index_page_filter(&$data){
        foreach ($data as $key=>$value){
            if(isset($value['keycate']) && $value['keycate']>0){
                $cateList = $this->keycatelist;
                $data[$key]['keycate'] = $cateList[$value['keycate']];
            }
        }
    }

    /**
     * 添加关键词
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '添加关键词';
		$this->assign('keycatelist', $this->keycatelist);
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑添加关键词
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑关键词';
        return $this->_form($this->table, 'form');
    }
    
    /**
     * 表单数据处理
     * @param $data
     */
    protected function _form_filter(&$data){
        if($this->request->post()){
            if($data['title']){
                if(Db::name($this->table)->where(['title' => $data['title'],'keycate'=>$data['keycate']])->count() > 0){
                    $this->error('关键词名称重复！');
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
     * 禁用关键词
     */
    public function forbid()
    {
        $this->_save($this->table, ['is_freeze' => '1']);
    }

    /**
     * 启用关键词
     */
    public function resume()
    {
        $this->_save($this->table, ['is_freeze' => '2']);
    }

    /**
     * 删除关键词
     */
    public function remove()
    {
        $this->_save($this->table,['is_delete'=>1]);
    }
}