<?php
namespace app\mapi\controller;
use think\Db;
use think\facade\Env;
use think\facade\Config;
require_once Env::get('ROOT_PATH')."vendor/wxpayApp/WxPay.Api.php";
require_once Env::get('ROOT_PATH')."vendor/alipayApp/aop/AopClient.php";
require_once Env::get('ROOT_PATH')."vendor/alipayApp/aop/request/AlipayTradeAppPayRequest.php";

class Order extends Base
{
    /**添加服务订单
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addOrder(){
        $data = input();
        $order_sn = 'FW'.date('YmdHis',time()).rand(1000, 9999);
        $Services = Db::name('StoreServices')->where(['id'=>$data['id']])->field('title,price,dates')->find();
        $orderId = Db::name('StoreServicesOrder')->insertGetId([
            'order_sn'=>$order_sn,
            'uid'=>$this->uid,
            'service_id'=>$data['id'],
            'service_name'=>$Services['title'],
            'dates'=>$Services['dates'],
            'price'=>$Services['price']*100,
            'pay_price'=>$Services['price']*100
        ]);
        return $this->showMsg(1,'添加成功',$orderId);
    }

    /**
     * 修改服务订单信息
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function updateOrder(){
        $data = input();
        Db::name('StoreServicesOrder')->where(['order_sn'=>$data['order_no']])->update([
            'pay_status'=>2,
            'pay_time'=>time()
        ]);
        $orderInfo = Db::name('StoreServicesOrder')->where(['order_sn'=>$data['order_no']])->find();
        $userService = Db('StoreUsersService')->where(['uid'=>$orderInfo['uid'],'service_id'=>$orderInfo['service_id']])->find();

        if(empty($userService)){
            $endTime = time()+$orderInfo['dates']*3600*24;
            Db('StoreUsersService')->insert([
                'uid'=>$orderInfo['uid'],
                'service_id'=>$orderInfo['service_id'],
                'start_time'=>date('Y-m-d H:i:s',time()),
                'end_time'=>date('Y-m-d H:i:s',$endTime)
            ]);
        }else{
            if(time()>=strtotime($userService['end_time'])){  //服务已过期
                $endTime = date('Y-m-d H:i:s',strtotime("+".$orderInfo['dates']."day"));
            }else{      //服务未过期
                $endTime = date('Y-m-d H:i:s',strtotime($userService['end_time'])+$orderInfo['dates']*3600*24);
            }
            Db('StoreUsersService')->where(['uid'=>$orderInfo['uid'],'service_id'=>$orderInfo['service_id']])->update([
                'end_time'=>$endTime,
                'start_time'=>date('Y-m-d H:i:s',time())
            ]);
        }
        return $this->showMsg(1,'操作成功');
    }

    /**
     * 微信支付
     * @return \think\Response
     * @throws \WxPayException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function wxpay(){

        $data = input();

        $orderInfo = Db::name('StoreServicesOrder')->where(['id'=>$data['id']])->find();

        // 获取支付金额
        $price = $orderInfo['pay_price'];
        //获取订单编号
        $out_trade_no = $orderInfo['order_sn'];

        Db::name('StoreServicesOrder')->where(['id'=>$data['id']])->update([
            'type'=>$data['type']
        ]);

        // 商品名称
        $subject = '地毯汇商户服务';
        // 订单号，示例代码使用时间值加随机数作为唯一的订单ID号
        $unifiedOrder = new \WxPayUnifiedOrder();
        $unifiedOrder->SetBody($subject);//商品或支付单简要描述
        $unifiedOrder->SetOut_trade_no($out_trade_no);
        $unifiedOrder->SetTotal_fee($price);
        $unifiedOrder->SetTrade_type("APP");
        $wxpayApi = new \WxPayApi();
        $result = $wxpayApi->unifiedOrder($unifiedOrder);
        $list['order_info'] = $result;
        $list['out_trade_no'] = $out_trade_no;

        return $this->showMsg(1,'',$list);
    }

    /**
     * 支付宝支付
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function alipay(){

        $data = input();

        $orderInfo = Db::name('StoreServicesOrder')->where(['id'=>$data['id']])->find();

        // 获取支付金额
        $price = $orderInfo['pay_price']/100;
        //获取订单编号
        $out_trade_no = $orderInfo['order_sn'];

        Db::name('StoreServicesOrder')->where(['id'=>$data['id']])->update([
            'type'=>$data['type']
        ]);

        $aop = new \AopClient();
        $aop->gatewayUrl = Config::get('gatewayUrl');
        $aop->appId = Config::get('appId');
        $aop->rsaPrivateKey =   Config::get('rsaPrivateKey');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = Config::get('alipayrsaPublicKey');
        $request = new \AlipayTradeAppPayRequest();
        // 异步通知地址
        $notify_url = urlencode(Config::get('web_url').'/yunshang/index.php/mapi/NotifyApi/alipay');
        $subject = '地毯汇商户服务';
        $body = '地毯汇商户服务';
        $bizcontent = "{\"body\":\"".$body."\","
            . "\"subject\": \"".$subject."\","
            . "\"out_trade_no\": \"".$out_trade_no."\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"".$price."\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
        $response = $aop->sdkExecute($request);
        $list['order_info'] = $response;
        $list['out_trade_no'] = $out_trade_no;

        return $this->showMsg(1,'',$list);
    }
    
    //我的订单列表
    public function orderList(){
        $data = input();
        $page = $data['page'];
        $pageLimit = 7;
        $orderList = Db::name('StoreServicesOrder')->where('is_delete = 2 and uid = '.$this->uid)->order('create_at desc')->page($page,$pageLimit)->select();
        if($orderList){
            foreach ($orderList as $key=>$value){
                if($value['pay_status'] == 1){
                    $orderList[$key]['pay_status_name'] = "未支付";
                }else if($value['pay_status'] == 2){
                    $orderList[$key]['pay_status_name'] = "已支付";
                }else if($value['pay_status'] == 3){
                    $orderList[$key]['pay_status_name'] = "支付失败";
                }
                $orderList[$key]['pay_price'] = $value['pay_price']/100;
            }
        }
        $list['order_list'] = $orderList;
        $list['page_limit'] = $pageLimit;
        return $this->showMsg(1,'',$list);
    }
    //删除订单信息
    public function delOrder(){
        $data = input();
        $id = $data['id'];
        $list = Db::name('StoreServicesOrder')->where('id = '.$id)->update(['is_delete' => 1]);
        if($list){
             return $this->showMsg(1,'删除成功');
        }
        return $this->showMsg(-1,'删除失败');
    }
}