<?php
namespace app\website\controller;

use library\Controller;
use library\tools\Data;
use think\Db;

/**
 * 广告管理
 * Class News
 * @package app\website\controller
 */
class ads extends Controller
{
    /**
     * 指定数据表
     * @var string
     */
    protected $table = 'StoreAds';

    /**
     * 广告管理
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '广告管理';
        $this->_query($this->table)->equal('location_id,ad_type,platform_type')->like('ad_name')->where(['is_delete' => '2'])->order('sort desc,id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data){
        $this->clist = Db::name('StoreAdLocation')->where(['is_delete' => '2', 'status' => '1'])->select();
        $adtype[1] = array('id' => 1,'name' => "展厅详情");
        $adtype[2] = array('id' => 2,'name' => "展会详情");
        $adtype[3] = array('id' => 3,'name' => "活动详情");
        $adtype[4] = array('id' => 4,'name' => "商品详情");
        $adtype[5] = array('id' => 5,'name' => "新闻详情");
        $adtype[6] = array('id' => 6,'name' => "指定路径");
        $platform_type[1] = array('id' => 1,'name' => "展厅端");
        $platform_type[2] = array('id' => 2,'name' => "用户端");
        $this->adtype = $adtype;
        $this->platform_type = $platform_type;
        foreach ($data as &$vo) {
            $cateInfo = Db::name('StoreAdLocation')->where(['id'=>$vo['location_id']])->find();
            $vo['name'] = $cateInfo['name'];
            if($vo['ad_type'] == 1){
                $vo['type_name'] = "展厅详情";
            }else if($vo['ad_type'] == 2){
                $vo['type_name'] = "展会详情";
            }else if($vo['ad_type'] == 3){
                $vo['type_name'] = "活动详情";
            }else if($vo['ad_type'] == 4){
                $vo['type_name'] = "商品详情";
            }else if($vo['ad_type'] == 5){
                $vo['type_name'] = "新闻详情";
            }else if($vo['ad_type'] == 6){
                $vo['type_name'] = "指定路径";
            }else{
            	$vo['type_name'] = "";
            }
            if($vo['platform_type'] == 1){
                $vo['platform_name'] = "展厅端";
            }else if($vo['platform_type'] == 2){
                $vo['platform_name'] = "用户端";
            }else{
            	$vo['platform_name'] = "";
            }
        }
    }

    /**
     * 添加广告信息
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
    	// $phone = '18729290751';
    	// $TemplateParam = json_encode(array("username"=>$phone,"password"=>$phone));
    	// $res = alisendsms($phone, "SMS_189017985",$TemplateParam);
    	// echo $res;
        $this->title = '添加广告信息';
        return $this->_form($this->table, 'add_form');
    }

    /**
     * 编辑广告信息
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑广告信息';
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
    {   if($this->request->isPost()){
            if($data){
                if($data['ad_type'] == 6){
                    $data['route_id'] = 0;
                }else{
                    $data['route'] = '';
                }
            }
        }
        if(!isset($data['id'])){
             $this->adlocation = Db::name('StoreAdLocation')->where(['is_delete' => '2', 'status' => '1','platform_type' => '1'])->select();
        }else{
             $this->adlocation = Db::name('StoreAdLocation')->where(['is_delete' => '2', 'status' => '1','platform_type' => $data['platform_type']])->select();
        }
        $adtype[1] = array('id' => 1,'name' => "展厅详情");
        $adtype[2] = array('id' => 2,'name' => "展会详情");
        $adtype[3] = array('id' => 3,'name' => "活动详情");
        $adtype[4] = array('id' => 4,'name' => "商品详情");
        $adtype[5] = array('id' => 5,'name' => "新闻详情");
        $adtype[6] = array('id' => 6,'name' => "指定路径");
        $platform_type[1] = array('id' => 1,'name' => "展厅端");
        $platform_type[2] = array('id' => 2,'name' => "用户端");
        $this->adtype = $adtype;
        $this->platform_type = $platform_type;
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
     * 获取平台类型下的广告位置
     */
    public function getLocation(){
        $id = $this->request->post('id');
        $locationList = Db::name('StoreAdLocation')->field('id,name')->where(['is_delete' => '2', 'status' => '1','platform_type' => $id])->select();
        return $locationList;
    }
    
     /**
     * 禁用广告
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用广告
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除广告
     */
    public function remove()
    {
        $this->_save($this->table, ['is_delete' => '1']);
    }

}