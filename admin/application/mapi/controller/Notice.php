<?php
namespace app\mapi\controller;
use think\Db;

class Notice extends Base
{

    /**
     * 获取公告列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        $data = input();
        $page = $data['page'];
        $pageLimit = 10;
        $newsList = Db::name('StoreNews')->where('cate_id=4 and status=1 and is_delete=2')->field('id,title,intro,create_at')->page($page,$pageLimit)->order('id desc')->select();
        if($newsList){
            foreach ($newsList as $key=>$value){
                $newsList[$key]['year'] = date('Y/m',strtotime($value['create_at']));
                $newsList[$key]['date'] = date('d',strtotime($value['create_at']));
            }
        }
        $list['news_list'] = $newsList;
        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'',$list);
    }

    /**获取公告详情
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListDetails(){
        $data = input();
        $id = $data['id'];
        $res =Db::name('StoreNews')->where(['id'=>$id])->field('id,title,content,create_at')->find();
		//增加浏览次数
		Db::name('StoreNews')->where(['id'=>$id])->setInc('view_num');
        $res = Db::name('StoreNews')->where(['id'=>$id])->field('id,title,content,create_at')->find();
        $res['add_time'] = date('Y.m.d',strtotime($res['create_at']));
        return $this->showMsg(1,'',$res);
    }
    
    /**
     * 判断是否有最新公告
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function newNotice(){
        $data = input();
        $id = $data['id'];
        $info = Db::name('StoreNews')->where('cate_id=4 and status=1 and is_delete=2 and id > '.$id)->count();
        return $this->showMsg(1,'',$info);
    }
}