<?php
namespace app\mapi\controller;

use think\Controller;
use think\Db;
use think\facade\Config;

class Common extends Controller
{

    public function __construct(){
        $key = array_key_exists('HTTP_KEY', $_SERVER) ? $_SERVER['HTTP_KEY']:'';
        $publicKey = Config::get('mapi.APP_KEY');
        if(!$key){
            echo json_encode(-6);
            exit();
        }
        if($key != $publicKey){
            echo json_encode(-6);
        exit();
        }
    }

    //统一输出函数
    function showMsg_bak($code=0,$msg='',$data=[]){
        $outdata=array(
            'code'=>$code,
            'msg'=>$msg,
            'data'=>$data
        );
        echo json_encode($outdata);
        exit();
    }

	/**
     * 输出返回数据
     * @param string $code 业务状态码
	 * @param string $msg 提示消息内容     
     * @param mixed $data 要返回的数据
     * @return Response
     */
    function showMsg($code=0, $msg='',$data=[])
    {
		$info = array(
			'code' => $code, 
			'msg' => $msg, 
			'data' => $data
		);
		return json($info);
    }
    
    /**用户登录记录
     * 传递参数：
     * 用户ID：user_id
     * 平台类型 1商户端 2用户端：platform_type
     * 手机系统 1安卓 2苹果:system_type 
     **/
    function loginJournal($user_id,$system_type=0,$platform_type){
         $logininfo = array('user_id' => $user_id, 'addtime' => date("Y-m-d H:i:s",time()), 'system_type' => $system_type, 'platform_type' =>$platform_type);
         $res = Db::name('StoreLoginJournal')->insert($logininfo);
         return $res;
    }
    
    //获取服务协议信息
    public function getExplainInfo(){
        $data = input();
        $info = Db::name('SystemExplain')->where('key_name = "'.$data['key_name'].'"')->find();
        return $this->showMsg(1,'',$info);
    }
    
    //获取展厅端启动广告图
    public function getAds(){
        $today = date('Y-m-d H:i:s',time());
        $info = Db::name('StoreAds')->field('ad_img,ad_type,route_id,route')
                ->where('is_delete = 2 and status = 1 and platform_type = 1 and location_id = 4')
                ->whereTime('endtime','>',$today)
                ->whereTime('starttime','<=',$today)
                ->order('sort desc')->select();
        return $this->showMsg(1,'',$info);
    }
    
}