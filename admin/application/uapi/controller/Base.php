<?php
namespace app\uapi\controller;

use think\Controller;
use think\Db;
use think\facade\Config;
use think\Response;

class Base extends Controller
{
    protected  $uid;
    public function __construct(){
        $token = array_key_exists('HTTP_TOKEN', $_SERVER) ? $_SERVER['HTTP_TOKEN']:'';
        if(!$token){
            echo json_encode(array('code'=>-6));die;
        }
        $tokenInfo = Db::name('StoreToken')->where(['token' => $token,'user_type'=>2])->find();
        if(empty($tokenInfo)){
            echo json_encode(array('code'=>-6));die;
        }
        if($tokenInfo['status'] == 0){
            echo json_encode(array('code'=>-6));die;
        }
        $this->uid = $tokenInfo['user_id'];
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
    
    /**用户操作记录
     * 传递参数：
     * 用户ID：user_id
     * 平台类型 1展厅端 2用户端：platform_type
     * 操作内容:content 
     **/
    function UserOperation($user_id,$content,$platform_type = 2){
         $logininfo = array('content' => $content, 'user_id' => $user_id, 'addtime' => date("Y-m-d H:i:s",time()), 'platform_type' =>$platform_type);
         $res = Db::name('StoreUserOperation')->insert($logininfo); 
         return $res;
    }
    
    /**
    * 验证码是否有效
    * @param string $phone
    * @param string $code
    * @return number
    */
   function verifySms($phone, $code, $type){
       $where = 'phone = '.$phone.' and code = '.$code.' and is_use = 2 and type = '.$type;
       $res = Db::name('StoreSms')->field('id,addtime')->where($where)->order('addtime DESC')->find();
       if($res){
           $addtime = strtotime($res['addtime']);
           Db::name('StoreSms')->where('id = '.$res['id'])->update(['is_use' => 1]);
           $time = time() -Config::get('uapi.SMS_OVER_TIME');
           if($addtime > $time){
               return 1;
           }else{
               return 2;  //验证码已失效
           }
       }else{
           return 3;  //验证码错误
       }
   }
}

