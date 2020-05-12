<?php
require_once 'aop/AopClient.php';
require_once 'aop/request/AlipayTradeAppPayRequest.php';

$aop = new AopClient;
$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
$aop->appId = "2018120762494566";
$aop->rsaPrivateKey = 'MIIEowIBAAKCAQEAm2MfmZXFOnpGwiNHFPbKC+2t6yQDpm92y/qOElap1qWMwz7fkpzXLRmmwGWI7yYTX4nIlntubwnIVEHZ4ceWHsTmPYLivulJNpSA98rTDuHnKmWHEXyWZcHeqNuHVPjc2GUAgX2mIBwMeBbqUYpB4kBkZMVWsP1mAixH4NxMODhtu+idO/g6vnKf9/0C4gOKz2ZqBodgZyBJRVE6Azdt+2OJFU0c1Jw4CbUkxs/8evl7RkzgDEcipn9KZViQc7rPDGSsv6o5F1sN9DZ/Xrw02oYPRkxXIi1EWkZoRGARJ644Ek3eoTXe0gxrXuj1Dz7bhPVE/S+Nx8qlIUsqOM7j+wIDAQABAoIBAGuitR/5bB5+1wbh0vpFnV237XJlhxXCPmM7kzBG0ez6zk/s/IedlzwJecXqT3mBQYg7dDQxGiVWfGtJFjlcvLNhpy9Su+iMxodFRTTgTWUQvMVUgMRvLesc6TTEpLEKlkhbZodMV0gExeplzThgchTcj+5x+AQv83pr2/p5/14tOvpAS/bysV81CHAL8WRetobkk3OX9zatwoUIeh5+gkOhq3SNcFJRkC6ki7M52JAOxNs/3RXDNoxsRKLctaWByy0dJawYQmPy8cftOw1UjQie+pfKQ9fBOg0c5hLKzNnYhCxxMMMyXWSHX5E+gZRiBh++vHc3a2Zq1bfoWDlTS1ECgYEA2mIfAAC0H/CTQc+dKreqoBING9UNkCyQDtaTA41pg0myaJZWreGHtXnFFOGzpmnRCrsSloQCRnBg8kzKrn/EQglfZp9LIgHKNVYhC20UMWd8TMOBevUXcovCvGyCvjhydXk+0CKk1RBWh+2Kqgzf+58J+de/jYh1CKzC/3zzpYkCgYEAticc2jBw2XbL2k2Loq6ba36kG09zfjWhGXTBHbRbFSxGoCuiOB2sVdqfwNAeZUsR7y6RIjw5OCGk03OLoTblrijAEORAwpj0sy4UQ17zYOZ0TfOCIlgD/s4bzYthsYh2tLnQhHUWITdSW2rtksBCc1LM5y0EZDUr3pY7JZZO4GMCgYBSJij9KkaX4TqzvfKkWBTiRAgUWS/R9UF3o3YIFxEC5x0qxKr0m+sd9CGT+ldHGXUecULLxfrJHosJeqOfwsZEBRyTEQcFUuEK8Uxa7Px0nTYf9kdlxn68gyCNGtCP19IOqL5dMzsIPy4d0digoVk8YRYymtse1Z8Y1UxBKXLJ6QKBgQCX+tBm+E3gE++IiHT+WoNQ0ExqgQMTBfoRfEn419e7NcMDtwRVn+R/ibcLMFp8F4OyNf9gOjFftSqKRvj0nRJMGrIOJfQmqWpZ6hN8FsgoTIAz3f3xW9CRlDS8bhQoBX1N00+4hNzfAAKWRgRNEl8fS7GtHPFyDq84u85D0UKkkwKBgEK+gbEiPAuqNLKfDnq/ZB5kK+NeEa3Iy5Ao6RjitjRYsQLjbNcOtoeWt2xsL5m4RHshDxy/rd0hgKWxYcQPc09VbU+Ez3xRwUXWAbGC6H7G9eH7yqKQTGlzzxQeADYEICp8mo4TNCx7/XU0VqhLZ6q8j/BcJ05ZX0j9CCpZpXI5';
$aop->format = "json";
$aop->charset = "UTF-8";
$aop->signType = "RSA2";
$aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqvPaskmrJLTnkwLrtphD/5sgouclYlW36cEDkX7g4nDA+psB4ZZxJ9lGWqN0l5PlObZlOJNLj4tuvrNQF04goT3lDPKMBn4z3COZdeZsddAAK/RghAjghT/5DIQqRUFVCuLaGyKH3XPX6Tz8a7QA2V9Y2iGF3uMVc+4Y7PdMZVvg8Vx0ZymWfSuXwtx6gCbx485fHygK99PaGMjhzOKsqpLYLCvqRznyMVUQR43iQ0C1peKFRNJslcw5zshCjJmPvopu9USLdK7/wcEelRbVSVG+FRI8CLizNQcQ1vLfdIQ6r9L/lHncFm/E47M/9BSt86wnlNJHpe9aPfzL6ailRQIDAQAB';
//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
$request = new AlipayTradeAppPayRequest();

// 异步通知地址
$notify_url = urlencode('http://www.baidu.com');
// 订单标题
$subject = '莱恩物流';
// 订单详情
$body = '莱恩物流'; 

//SDK已经封装掉了公共参数，这里只需要传入业务参数
$bizcontent = "{\"body\":\"".$body."\","
                . "\"subject\": \"".$subject."\","
                . "\"out_trade_no\": \"".$out_trade_no."\","
                . "\"timeout_express\": \"30m\","
                . "\"total_amount\": \"".$total."\","
                . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                . "}";
$request->setNotifyUrl($notify_url);
$request->setBizContent($bizcontent);
//这里和普通的接口调用不同，使用的是sdkExecute
$response = $aop->sdkExecute($request);

// 注意：这里不需要使用htmlspecialchars进行转义，直接返回即可
//$response;
?>