<?php
require_once "WxPay.Api.php";
require_once "WxPay.Data.php";


// 获取支付金额
$total =  100;

// 商品名称
$subject = '莱恩物流';
$unifiedOrder = new WxPayUnifiedOrder();
$unifiedOrder->SetBody($subject);//商品或支付单简要描述
$unifiedOrder->SetOut_trade_no($out_trade_no);
$unifiedOrder->SetTotal_fee($total);
$unifiedOrder->SetTrade_type("APP");
try{
    $result = WxPayApi::unifiedOrder($unifiedOrder);
}catch(Exception $e){
    file_put_contents('wxpay.txt', $e->getMessage());
}

$response = json_encode($result);
?>