<?php



namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use library\getui\GeTui;
use think\Db;

/**
 * 消息管理
 * Class News
 * @package app\website\controller
 */
class Message extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreMessage';

    /**
     * 消息信息管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '消息信息管理';
        $this->_query($this->table)->equal('type,push_obj,jump_type,is_push')->like('title')->where(['is_delete' => '2'])->order('id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
        $push[1] = array('id' => 1,'name' => "展厅平台");
        $push[2] = array('id' => 2,'name' => "用户平台");
        $type[1] = array('id' => 1,'name' => "推送消息");
        $type[2] = array('id' => 2,'name' => "站内消息");
        $is_push[1] = array('id' => 1,'name' => "已发送");
        $is_push[2] = array('id' => 2,'name' => "未发送");
        $jumptype[1] = array('id' => 1,'name' => "展厅");
        $jumptype[2] = array('id' => 2,'name' => "展会");
        $jumptype[3] = array('id' => 3,'name' => "活动");
        $jumptype[4] = array('id' => 4,'name' => "产品");
        $jumptype[5] = array('id' => 5,'name' => "新闻");
        $jumptype[6] = array('id' => 6,'name' => "指定路径");
        $this->push = $push;
        $this->type = $type;
        $this->is_push = $is_push;
        $this->jumptype = $jumptype;
        foreach ($data as &$vo) {
            if($vo['type'] == 1){
                $vo['type_name'] = "推送消息";
            }else if($vo['type'] == 2){
                $vo['type_name'] = "站内消息";
            }
            if($vo['push_obj'] == 1){
                $vo['push_name'] = "展厅平台";
            }else if($vo['push_obj'] == 2){
                $vo['push_name'] = "用户平台";
            }
            if($vo['jump_type'] == 1){
                $vo['jump_type_name'] = "展厅";
            }else if($vo['jump_type'] == 2){
                $vo['jump_type_name'] = "展会";
            }else if($vo['jump_type'] == 3){
                $vo['jump_type_name'] = "活动";
            }else if($vo['jump_type'] == 4){
                $vo['jump_type_name'] = "产品";
            }else if($vo['jump_type'] == 5){
                $vo['jump_type_name'] = "新闻";
            }else if($vo['jump_type'] == 6){
                $vo['jump_type_name'] = "指定路径";
            }
        }
    }

    /**
     * 添加消息信息
     * @return mixed
     */
    public function add()
    {
        return $this->_form($this->table, 'add_form');
    }

    /**
     * 编辑消息信息
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
        if($this->request->isPost()){
            if(!isset($data['id'])){
                $data['add_uid'] = session('user.id');
            }else{
                if($data['jump_type'] == 6){
                    $data['jump_id'] = 0;
                }else{
                    $data['jump_url'] = "";
                }
            }
        }
        $push[1] = array('id' => 1,'name' => "展厅平台");
        $push[2] = array('id' => 2,'name' => "用户平台");
        $type[1] = array('id' => 1,'name' => "推送消息");
        $type[2] = array('id' => 2,'name' => "站内消息");
        $jumptype[1] = array('id' => 1,'name' => "展厅");
        $jumptype[2] = array('id' => 2,'name' => "展会");
        $jumptype[3] = array('id' => 3,'name' => "活动");
        $jumptype[4] = array('id' => 4,'name' => "产品");
        $jumptype[5] = array('id' => 5,'name' => "新闻");
        $jumptype[6] = array('id' => 6,'name' => "指定路径");
        $this->push = $push;
        $this->type = $type;
        $this->jumptype = $jumptype;
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
     * 消息发送
     */
    public function push()
    {        
        $id = $_POST['id'];
        $updatetime = date('Y-m-d H:i:s', time());
        $adminid = session('user.id');
        $messageinfo = Db::name('StoreMessage')->where(['id' => $id])->find();
        $save['is_push'] = 1;
        $save['update_uid'] = $adminid;
        $save['updatetime'] = $updatetime;
        if($messageinfo['type'] == 2){
            $res = Db::name('StoreMessage')->where(['id' => $id])->update(['is_push' => '1','update_uid' => $adminid,'updatetime' => $updatetime]);
        }else{
            $arr['url'] = "";
            if($messageinfo['jump_type'] == 1){
                $type = 1;
                $arr['type'] = "1";
            }else if($messageinfo['jump_type'] == 2){
                $type = 2;
                $arr['type'] = "2";
            }else if($messageinfo['jump_type'] == 3){
                $type = 3;
                $arr['type'] = "3";
            }else if($messageinfo['jump_type'] == 4){
                $type = 4;
                $arr['type'] = "4";
            }else if($messageinfo['jump_type'] == 5){
                $type = 5;
                $arr['type'] = "5";
            }else if($messageinfo['jump_type'] == 6){
                $type = 6;
                $arr['type'] = "6";
                $arr['url'] = $messageinfo['jump_url'];
            }
            $arr['id'] = $messageinfo['jump_id'];
            $val = json_encode($arr)."";
            $gtui = new GeTui();
            $ress = $gtui->pushMessageToApp($messageinfo['title'],$messageinfo['content'],$type,$val,$messageinfo['push_obj']);
            $res = Db::name('StoreMessage')->where(['id' => $id])->update(['is_push' => '1','update_uid' => $adminid,'updatetime' => $updatetime]);
        }
        $this->success('消息发送成功！');
    }
    
    /**
     * 删除消息信息
     */
    public function remove()
    {
        $this->_save($this->table, ['is_delete' => '1']);
    }

}