<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 关键词搜索记录管理
 * Class KeywordRecord
 * @package app\admin\controller
 */
class KeywordRecord extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'SystemSearchkeywords';

    protected $typeList = array('展厅','产品');

    /**
     * 关键词搜索记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '关键词搜索记录';
        $fields = 's.*,k.title,k.keycate,m.username';
        $this->_query($this->table)->alias('s')->field($fields)->leftJoin('system_keywords k','s.kid=k.id')->leftJoin('store_member m','s.uid=m.id')->dateBetween('s.create_at#create_at')->like('k.title#title')->equal('k.keycate#type')->order('s.id desc')->page();
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as $k=>$v){
            if($v['keycate'] == 1){
                $data[$k]['type'] = '展厅';
            }else{
                $data[$k]['type'] = '产品';
            }
        }
    }
}