后台写日志：yunshang\application\admin\sys.php
_syslog('系统管理', '用户登录系统成功');

----------------------------------------------

配置文件获取：
sysconf('storage_minsize')

----------------------------------------------

后台上传api路基路径：
yunshang\application\admin\controller\api\Plugs.php

----------------------------------------------

短信接口：
yunshang\thinkphp\helper.php===》转移到 yunshang\application\common.php下
echo alisendsms('15991750091', 'SMS_186430066',json_encode(array('code'=>'123456')));
短信模板信息
$Template=array(
	'SMS_186430066'=>array('SMS_186430066','身份验证验证码','验证码${code}，您正在进行身份验证，打死不要告诉别人哦！'),
	'SMS_186430065'=>array('SMS_186430065','登录确认验证码','验证码${code}，您正在登录，若非本人操作，请勿泄露。'),
	'SMS_186430064'=>array('SMS_186430064','登录异常验证码','验证码${code}，您正尝试异地登录，若非本人操作，请勿泄露。'),
	'SMS_186430063'=>array('SMS_186430063','用户注册验证码','验证码${code}，您正在注册成为新用户，感谢您的支持！'),
	'SMS_186430062'=>array('SMS_186430062','修改密码验证码','验证码${code}，您正在尝试修改登录密码，请妥善保管账户信息。'),
	'SMS_186430061'=>array('SMS_186430061','信息变更验证码','验证码${code}，您正在尝试变更重要信息，请妥善保管账户信息。'),
);

----------------------------------------------

use ip2region;
$ip2region = new \Ip2Region();
$ip = '61.140.232.170';
echo PHP_EOL;
echo "查询IP：{$ip}" . PHP_EOL;
$info = $ip2region->btreeSearch($ip);
var_export($info);
exit();

----------------------------------------------

获取配置信息
config('app.thinkadmin_ver');

环境变量的获取
use think\facade\Env;
Env::get('database.username','root');


\vendor\zoujingli\think-library\src\common.php下方法可用