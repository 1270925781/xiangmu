<?php



namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 新闻信息管理
 * Class News
 * @package app\website\controller
 */
class News extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreNews';

    /**
     * 新闻信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '新闻信息管理';
        $this->_query($this->table)->equal('status,cate_id')->like('title')->where(['is_delete' => '2'])->order('sort desc,id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
        $this->clist = Db::name('StoreNewsCate')->where(['is_delete' => '2', 'status' => '1'])->select();
        foreach ($data as &$vo) {
            $cateInfo = Db::name('StoreNewsCate')->where(['id'=>$vo['cate_id']])->find();
            $vo['cate_name'] = $cateInfo['title'];
            $vo['content'] = strip_tags ($vo['content']);
        }
    }

    /**
     * 添加新闻信息
     * @return mixed
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑新闻信息
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
        $this->cates = Db::name('StoreNewsCate')->where(['is_delete' => '2', 'status' => '1'])->order('sort asc,id desc')->select();
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
     * 禁用新闻信息
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用新闻信息
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除新闻信息
     */
    public function remove()
    {
        $this->_save($this->table, ['is_delete' => '1']);
    }

}