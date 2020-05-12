<?php
namespace app\mapi\controller;
use think\Db;

class Activities extends Base
{
    /**
     * 获取活动/展会详情
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getActDetails(){
        $id = input('id');
        $actInfo = Db::name('StoreActivities')->field('id,details_image,content,end_time')->where(['id'=>$id])->find();
        $Isapply = Db::name('StoreActivitiesSign')->where(['act_id'=>$id,'uid'=>$this->uid])->find();
        // if($Isapply){
        //     $actInfo['is_apply'] = 1;
        // }else{
        //     $actInfo['is_apply'] = 0;
        // }
        if($actInfo['end_time']>=time()){
            $actInfo['is_end'] = 1;
        }else{
            $actInfo['is_end'] = 2;
        }
        return $this->showMsg(1,'',$actInfo);
    }

    /**活动\展会报名
     * @return \think\Response
     */
    public function apply(){
        $data = input('info');
        if($data['type'] == 1){
            $type = 1;
        }else{
            $type = 2;
        }
        Db::name('StoreActivitiesSign')->insert([
            'act_id'=>$data['id'],
            'uid'=>$this->uid,
            'platform_type'=>$type,
            'linkman'=>$data['linkman'],
            'linkphone'=>$data['linkphone'],
            'join_num'=>$data['join_num'],
        ]);
        return $this->showMsg(1,'报名成功');
    }
}