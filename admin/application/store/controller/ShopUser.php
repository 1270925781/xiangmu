<?php



namespace app\store\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 展厅用户管理
 * Class User
 * @package app\admin\controller
 */
class ShopUser extends Controller
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'SystemUser';

    /**
     * 展厅用户管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '展厅用户管理';
        $query = $this->_query($this->table)->alias('u')->leftJoin('store_shop s','s.id=u.shop_id')->field('u.*,s.title')->like('u.username#username,u.phone#phone')->dateBetween('u.login_at#login_at');
        $query->equal('u.status#status')->where(['u.is_delete' => '2','u.authorize'=>2])->order('u.id desc')->page();
    }
    
    /**
     * 列表数据处理
     * @param $data
    */
    protected function _index_page_filter(&$data){
         foreach($data as &$vo){
			//去第一个权限
			$authorize=explode(",",$vo['authorize']);
			$title = Db::name('SystemAuth')->where(['status' => '1','id' => $authorize[0]])->value('title');
			if($title){
                $vo['authorize_'] = '('.$title.')';
            }else{
                $vo['authorize_'] = '';
            }
         }
    }

    /**
     * 用户授权管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function auth()
    {
        // $this->applyCsrfToken();
        $this->_form($this->table, 'auth');
    }

    /**
     * 添加展厅用户
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        // $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑展厅用户
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 修改用户密码
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function pass()
    {
        $this->applyCsrfToken();
        if ($this->request->isGet()) {
            $this->verify = false;
            $this->_form($this->table, 'pass');
        } else {
            $post = $this->request->post();
            if ($post['password'] !== $post['repassword']) {
                $this->error('两次输入的密码不一致！');
            }
            $result = \app\admin\service\AuthService::checkPassword($post['password']);
            if (empty($result['code'])) $this->error($result['msg']);
            $data = ['id' => $post['id'], 'password' => md5($post['password'])];
            if (Data::save($this->table, $data, 'id')) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 表单数据处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            if (Db::name($this->table)->where(['username' => $data['username']])->count() > 0) {
                $this->error('用户账号已经存在，请使用其它账号！');
            }
        } else {
            // $this->assign('authorizes', Db::name('SystemAuth')->where('status=1 and id!=2')->select());
        }
    }

    /**
     * 禁用展厅用户
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用展厅用户
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除展厅用户
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止删除！');
        }
        $this->applyCsrfToken();
        $this->_save($this->table,['is_delete'=>1]);
    }

}
