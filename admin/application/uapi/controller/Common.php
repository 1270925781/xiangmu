<?php
namespace app\uapi\controller;

use think\Controller;
use think\Db;
use think\facade\Config;
use think\ChuanglanSmsApi;
use think\Cache;


class Common extends Controller
{

    public function __construct(){
        $key = array_key_exists('HTTP_KEY', $_SERVER) ? $_SERVER['HTTP_KEY']:'';
        $publicKey = Config::get('uapi.APP_KEY');
        if(!$key){
            echo json_encode(-6);
            exit();
        }
        if($key != $publicKey){
            echo json_encode(-6);
            exit();
        }
        $token = array_key_exists('HTTP_TOKEN', $_SERVER) ? $_SERVER['HTTP_TOKEN']:'';
        $tokenInfo = Db::name('StoreToken')->where(['token' => $token,'user_type'=>2])->find();
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
    
    /**用户登录记录
     * 传递参数：
     * 用户ID：user_id
     * 平台类型 1商户端 2用户端：platform_type
     * 手机系统 1安卓 2苹果:system_type 
     **/
    function loginJournal($user_id,$system_type=0,$platform_type=2){
         $logininfo = array('user_id' => $user_id, 'addtime' => date("Y-m-d H:i:s",time()), 'system_type' => $system_type, 'platform_type' =>$platform_type);
         $res = Db::name('StoreLoginJournal')->insert($logininfo);
         return $res;
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
    
    //生成随机邀请码
    function random(){
        $pattern = '1234567890ABCDEFGHIJKLOMNOPQRSTUVWXYZ'; 
        $key = "";
        for($i=0;$i<6;$i++)  
        {  
         $key .= $pattern{mt_rand(0,35)}; 
        }
        $member = Db::name('StoreMember')->where(['inviter_code' => $key])->find();
        if($member){
            $this->random();
        }
        return $key;
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

    /**
     * 发送短信
     * @param $phone
     * @param string $msg
     * @param $type
     * @return array|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
   function send_sms($phone,$type ){
       $data = input();
        $typename = '';
        if($type == 1){
            $typename = '登录';
        }
        if($type == 2){
            $typename = '注册';
        }
        if($type == 3){
            $typename = '修改密码';
        }
        if($type == 4){
            $typename = '换绑手机号';
        }
        if($type == 5){
            $typename = '绑定手机号';
        }
        $sms = new ChuanglanSmsApi();
        $code = mt_rand(100000, 999999);
        //$message = '【' . C('SMS_SINATURE') . '】您正在'.$typename.'，短信验证码(' .$code.'),30分钟内有效。请勿泄露给他人';
        $message = "3333";
        $res = Db::name('StoreSms')->insert(['phone' => $phone, 'code' => $code, 'message' => $message, 'addtime' => date('Y-m-d H:i:s',time()), 'type' => $type]);
        if(!$res){
            return 3; //发送失败
        }
        $result = $sms->sendSMS($phone, $message);
        if(!is_null(json_decode($result))){
            $output = json_decode($result, true);
            if(isset($output['code'])  && $output['code'] == '0'){
                return ['status' =>5, 'code' =>$code];
            }else{
                return -10;
            }
        }else{
            return -10;
        }
    }

    /**
     * 根据定位的经纬度计算距离
     * @param $lon
     * @param $lat
     * @param $where
     * @return mixed
     *
     */
   public function getDistance($lon,$lat,$field,$where='where 1',$limit='',$type='1'){
       if($type == 1){
           $sql = "select $field, ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($lat*PI()/180-latitude*PI()/180)/2),2)+COS($lat*PI()/180)*COS(latitude*PI()/180)*POW(SIN(($lon*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance FROM store_shop where  ".$where." order by distance asc ".$limit;
       }elseif($type == 2){
           $sql = "select $field, ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($lat*PI()/180-s.latitude*PI()/180)/2),2)+COS($lat*PI()/180)*COS(s.latitude*PI()/180)*POW(SIN(($lon*PI()/180-s.longitude*PI()/180)/2),2)))*1000) AS distance FROM store_goods as g LEFT JOIN store_shop as s on g.shop_id=s.id where  ".$where." order by distance asc ".$limit;
       }

        $data = Db::query($sql);

        if(sizeof($data)>0){
            foreach ($data as $k=>$v){
                if ($v['distance'] <= 1000) {
                    $data[$k]['distance'] = $v['distance'].'m';    //米
                } else {
                    $data[$k]['distance'] = round($v['distance']/1000,1).'km';  //千米
                }
            }
        }else{
            $data=array();
        }

        return $data;
   }
   
   //获取用户端启动广告图
    public function getAds(){
        $today = date('Y-m-d H:i:s',time());
        $info = Db::name('StoreAds')->field('ad_img,ad_type,route_id,route')
                ->where('is_delete = 2 and status = 1 and platform_type = 2 and location_id = 5')
                ->whereTime('endtime','>',$today)
                ->whereTime('starttime','<=',$today)
                ->order('sort desc')->select();
        return $this->showMsg(1,'',$info);
    }

}