<?php
namespace app\store\controller;

use library\Controller;
use think\Db;

/**
 * 标签搜索管理
 * Class LabelRecord
 * @package app\admin\controller
 */
class LabelRecord extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'SystemSearchlabel';

    /**
     * 标签搜索记录管理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '标签搜索记录';
        $fields      = 's.*,l.title,m.username';
        $this->_query($this->table)->alias('s')->field($fields)->leftJoin('store_shop_label l', 's.lid=l.id')->leftJoin('store_member m', 's.uid=m.id')->leftJoin('store_shop_label sl','s.lid=sl.id')->dateBetween('s.create_at#create_at')->like('l.title#title')->order('s.id desc')->page();
    }
}