<?php
namespace app\mapi\controller;
use think\Db;
use think\facade\Config;
require_once \Env::get('ROOT_PATH')."vendor/wxpayApp/WxPay.Api.php";

class Notify extends Base
{

    public function alipay(){

        $data = input();
        $uid = $this->uid;
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = Config::get("publicKey");
        $flag = $aop->rsaCheckV1(input("post."), NULL, "RSA2");

        if($flag){
            // 消息验证通过,更改订单状态
            $states = input("trade_status");

            if($states == "TRADE_SUCCESS"){
                $ordernumber = input("out_trade_no");
                $paytime = input("gmt_payment");

                $orderInfo = Db::name('StoreServiceOrder')->where(['order_sn'=>$ordernumber])->field('pay_status')->find();

                if($orderInfo['pay_status'] == 1){
                    $data['pay_status'] = 2;
                    $data['pay_time'] = $paytime;
                    $res = Db::name('StoreServiceOrder')->where('order_sn='.$ordernumber)->save($data);
                    if($res != false && $res != 0){
                            echo "success";
                    }
                }
            }
            echo "success";
        }
        echo 'filed';
    }

    public function wxpay(){
        $post = $_REQUEST;
        $uid = $this->uid;
        if ($post == null) {
            $post = file_get_contents("php://input");
        }

        if ($post == null) {
            $post = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        }

        if (empty($post) || $post == null || $post == '') {
            //阻止微信接口反复回调接口
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            echo $str;
            exit('Notify 非法回调');
        }

        libxml_disable_entity_loader(true); //禁止引用外部xml实体

        $xml = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);//XML转数组

        $post_data = (array)$xml;

        //订单号
        $ordernumber = isset($post_data['out_trade_no']) && !empty($post_data['out_trade_no']) ? $post_data['out_trade_no'] : 0;

        //查询订单信息
        $order_info = Db::name('StoreServiceOrder')->where('out_trade_no='.$ordernumber)->find();

        if(count($order_info) > 0){

            //平台支付key
            $wxpay_key = Config::get('KEY');

            //接收到的签名
            $post_sign = $post_data['sign'];
            unset($post_data['sign']);

            //重新生成签名
            $newSign = MakeSign($post_data,$wxpay_key);

            //签名统一，则更新数据库
            if($post_sign == $newSign){
                $data = array();
                $orderInfo = Db::name('StoreServiceOrder')->where(['order_sn'=>$ordernumber])->field('pay_status')->find();
                if($order_info['pay_status'] == 1){
                    $data['pay_status'] = 2;
                    $data['pay_time'] = $paytime;
                    $res = Db::name('StoreServiceOrder')->where('order_sn='.$ordernumber)->save($data);
                    if($res != false && $res != 0){
                        echo "success";
                    }
                }elseif($order_info['pay_status'] == 2){
                    echo "success";
                }else{
                    echo "filed";
                }
                //更新order数据库
                //Do what you want...
            }else{
                echo "filed";
            }

        }else{
            echo "filed";
        }
    }
}