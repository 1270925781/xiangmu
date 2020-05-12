<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 会员信息管理
 * Class Member
 * @package app\store\controller
 */
class Member extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreMember';

    /**
     * 会员信息管理
     * @auth true  # 表示需要验证权限
     * @menu true  # 在菜单编辑的节点可选项
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '会员信息管理';
        $query = $this->_query($this->table)->like('nickname,phone')->equal('status');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     */
    protected function _page_filter(&$data = [])
    {
        foreach ($data as &$vo) {
            $vo['nickname'] = emoji_decode($vo['nickname']);
        }
    }
    
    /**
     * 禁用用户信息
     */
    public function forbid()
    {
        $id = input('id');
        $name = $this->getUsername($id);
        _syslog('禁用用户','禁用用户ID:'.$id.',用户名：'.$name);
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 启用用户信息
     */
    public function resume()
    {
        $id = input('id');
        $name = $this->getUsername($id);
        _syslog('启用用户','启用用户ID:'.$id.',用户名：'.$name);
        $this->_save($this->table, ['status' => '2']);
    }

    /**
     * 获取用户名
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUsername($id){
        $info = Db::name('StoreMember')->field('username')->where(['id'=>$id])->find();
        return $info['username'];
    }

}