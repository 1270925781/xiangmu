<?php
namespace library\getui;
//import('getui.IGt',EXTEND_PATH,'.Push.php');
class GeTui {
    
    private $HOST = 'http://sdk.open.api.igexin.com/apiex.htm';

    //展厅端信息
    private $APPKEY = 'ji1qXIGUMZ7Mzr47didW9A';
    private $APPID = '8eLVj8vMFq8gTzJs8dRNf8';
    private $MASTERSECRET = 'hm7vfDL6hx9TffBkHlcmx8';
    private $APPSECRET = 'ALwxBPy3i0A0DlehshRNn1';
    //用户端信息
    private $APPKEY1 = 'DLDOijbKfw8O5mw6pSofZ1';
    private $APPID1 = 'izyI1i7H4E7We8kTC9b8p9';
    private $MASTERSECRET1 = 'fDoe9WjItx81ndqC8yHpR5';
    private $APPSECRET1 = 'fEFwExEecfAX0ZAgWR5nS2';
    
    public function __construct()
    {
        $this->__loader();
    }
     private function __loader()
    {
        require_once(dirname(__FILE__) . '/' . 'IGt.Push.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/IGt.AppMessage.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/IGt.TagMessage.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/IGt.APNPayload.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/template/IGt.BaseTemplate.php');
        require_once(dirname(__FILE__) . '/' . 'IGt.Batch.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/utils/AppConditions.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/template/notify/IGt.Notify.php');
        require_once(dirname(__FILE__) . '/' . 'igetui/IGt.MultiMedia.php');
        require_once(dirname(__FILE__) . '/' . 'payload/VOIPPayload.php');
    }
     
    //群推接口案例
    function pushMessageToApp($title='',$content='',$type='',$val='',$push_obj=''){
//        import('getui.IGt', '', '.Push.php');
        if($push_obj == 1){
            //展厅端消息推送
            $igt = new \IGeTui($this->HOST,$this->APPKEY,$this->MASTERSECRET);
        }else{
            //用户端消息推送
            $igt = new \IGeTui($this->HOST,$this->APPKEY1,$this->MASTERSECRET1);
        }
        //定义透传模板，设置透传内容，和收到消息是否立即启动启用
        $template = $this->IGtNotificationTemplateDemo($title,$content,$type,$val,$push_obj);
        // 定义"AppMessage"类型消息对象，设置消息内容模板、发送的目标App列表、是否支持离线发送、以及离线消息有效期(单位毫秒)
        
//        Loader::import('getui\igetui\IGT.AppMessage', EXTEND_PATH);
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);

        if($push_obj == 1){
            $appIdList=array($this->APPID);
        }else{
            $appIdList=array($this->APPID1);
        }
        $phoneTypeList=array('ANDROID','IOS');
       // $cdt = new \AppConditions();
        //$cdt->addCondition3(AppConditions::PHONE_TYPE, $phoneTypeList);
        $message->set_appIdList($appIdList);
        //$message->set_phoneTypeList($phoneTypeList);
       // $message->set_conditions($cdt);
        $rep = $igt->pushMessageToApp($message);
        return $rep;
    }

    //消息推送末班
    function IGtNotificationTemplateDemo($title,$content,$type,$val,$push_obj){
        $template =  new \IGtNotificationTemplate();
        if($push_obj == 1){
            $template->set_appId($this->APPID);                   //应用appid
            $template->set_appkey($this->APPKEY);                 //应用appkey
        }else{
            $template->set_appId($this->APPID1);                   //应用appid
            $template->set_appkey($this->APPKEY1);
        }
        $template->set_transmissionType(1);            //透传消息类型
        $template->set_transmissionContent($val);//透传内容
        //$template->set_transmissionContent("{title:2,content:2}");//透传内容
        $template->set_title($title);      //通知栏标题
        $template->set_text($content);     //通知栏内容

        $template->set_logo("");                       //通知栏logo
        $template->set_logoURL("");                    //通知栏logo链接
        $template->set_isRing(true);                   //是否响铃
        $template->set_isVibrate(true);                //是否震动
        $template->set_isClearable(true);              //通知栏是否可清除

        return $template;
    }
}
