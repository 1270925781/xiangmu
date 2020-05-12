<?php
namespace app\mapi\controller;

use library\Controller;
use library\File;
/**
 * 上传管理
 * Class Plugs
 * @package app\mapi\Controller
 */
class Plugs extends Base
{

    /**
     * 文件状态检查
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function upstate()
    {
        $ext = strtolower(pathinfo(request()->post('filename', ''), PATHINFO_EXTENSION));
        $name = File::name(request()->post('md5'), $ext, '', 'strtolower');
        // 检查文件是否已上传
        $this->safe = $this->getUploadSafe();
        if (is_string($siteUrl = File::url($name))) {
            return $this->showMsg(-1,'检测到该文件已经存在，无需再次上传！',[
                'site_url' => $this->safe ? $name : $siteUrl,
            ]);
        }
        // 文件驱动
        $file = File::instance($this->getUploadType());
        // 生成上传授权参数
        $param = [
            'file_url' => $name, 'uptype' => $this->uptype, 'token' => md5($name . session_id()),
            'site_url' => $file->base($name), 'server' => $file->upload(), 'safe' => $this->safe,
        ];
        if (strtolower($this->uptype) === 'qiniu') {
            $auth = new \Qiniu\Auth(sysconf('storage_qiniu_access_key'), sysconf('storage_qiniu_secret_key'));
            $param['token'] = $auth->uploadToken(sysconf('storage_qiniu_bucket'), $name, 3600, [
                'returnBody' => json_encode(['code' => 1, 'data' => ['site_url' => $file->base($name)]], JSON_UNESCAPED_UNICODE),
            ]);
        } elseif (strtolower($this->uptype) === 'oss') {
            $param['OSSAccessKeyId'] = sysconf('storage_oss_keyid');
            $param['policy'] = base64_encode(json_encode(['conditions' => [['content-length-range', 0, 1048576000]], 'expiration' => gmdate("Y-m-d\TH:i:s\Z", time() + 3600)]));
            $param['signature'] = base64_encode(hash_hmac('sha1', $param['policy'], sysconf('storage_oss_secret'), true));
        }
        return $this->showMsg(-1,'未检测到文件，需要上传完整的文件！', $param);
    }

    /**
     * 文件上传
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function upfile()
    {
        $this->safe = $this->getUploadSafe();
        $this->uptype = $this->getUploadType();
        $this->mode = request()->get('mode', 'one');
        $this->field = request()->get('field', 'file');
        $this->types = request()->get('type', 'jpg,png');
        $this->mimes = File::mine($this->types);
        $this->fetch();
    }

    /**
     * WebUpload 文件上传
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function upload()
    {
        // 文件接收
        if (!($file = $this->getUploadFile()) || empty($file)) {
            return $this->showMsg(-1,'文件上传异常，文件可能过大或未上传！');
        }
		//大小限制--最大
		if (!$file->checkSize(sysconf('storage_maxsize'))) {
            return $this->showMsg(-1,'文件上传异常，文件不能过大！('.sysconf('storage_maxsize_title').')',['uploaded' => false, 'error' => ['message' => '文件上传异常，文件不能过大！('.sysconf('storage_maxsize_title').')']]);
        }
		//大小限制--最小
		if (!$file->checkSizeMin(sysconf('storage_minsize'))) {
            return $this->showMsg(-1,'文件上传异常，文件不能过小！('.sysconf('storage_minsize_title').')',['uploaded' => false, 'error' => ['message' => '文件上传异常，文件不能过小！('.sysconf('storage_minsize_title').')']]);
        }
		//类型限制
        if (!$file->checkExt(strtolower(sysconf('storage_local_exts')))) {
            return $this->showMsg(-1,'文件上传类型受限，请在后台配置！');
        }
		//php文件限制
        if ($file->checkExt('php')) {
            return $this->showMsg(-1,'可执行文件禁止上传到本地服务器！');
        }

		$this->safe = $this->getUploadSafe();
        $this->uptype = $this->getUploadType();
        $this->ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);
        $name = File::name($file->getPathname(), $this->ext, '', 'md5_file');
        $info = File::instance($this->uptype)->save($name, file_get_contents($file->getRealPath()), $this->safe);
        if (is_array($info) && isset($info['url'])) {
           return $this->showMsg(1,'文件上传成功！',['uploaded' => true, 'filename' => $name, 'url' => $this->safe ? $name : $info['url']]);
        }
		/*
        // 唯一名称
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $name = File::name($this->request->post('md5'), $ext, '', 'strtolower');
        // Token 验证
        if ($this->request->post('token') !== md5($name . session_id())) {
            return $this->showMsg(-1,'文件上传验证失败，请刷新页面重新上传！');
        }
        $this->safe = $this->getUploadSafe();
        $pathinfo = pathinfo(File::instance('local')->path($name, $this->safe));
        if ($file->move($pathinfo['dirname'], $pathinfo['basename'], true)) {
            if (is_array($info = File::instance('local')->info($name, $this->safe)) && isset($info['url'])) {
                return $this->showMsg(1,'文件上传成功！', ['site_url' => $this->safe ? $name : $info['url']]);
            }
        }
		*/
        return $this->showMsg(-1,'文件上传失败，请稍候再试！');
    }

    /**
     * Plupload 插件上传文件
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function plupload()
    {
        if (!($file = $this->getUploadFile()) || empty($file)) {
            return $this->showMsg(-1,'文件上传异常，文件可能过大或未上传！',['uploaded' => false, 'error' => ['message' => '文件上传异常，文件可能过大或未上传！']]);
        }
		//大小限制--最大
		if (!$file->checkSize(sysconf('storage_maxsize'))) {
            return $this->showMsg(-1,'文件上传异常，文件不能过大！('.sysconf('storage_maxsize_title').')',['uploaded' => false, 'error' => ['message' => '文件上传异常2，文件不能过大！('.sysconf('storage_maxsize_title').')']]);
        }
		//大小限制--最小
		if (!$file->checkSizeMin(sysconf('storage_minsize'))) {
            return $this->showMsg(-1,'文件上传异常，文件不能过小！('.sysconf('storage_minsize_title').')',['uploaded' => false, 'error' => ['message' => '文件上传异常，文件不能过小！('.sysconf('storage_minsize_title').')']]);
        }
		//类型限制
        if (!$file->checkExt(strtolower(sysconf('storage_local_exts')))) {
            return $this->showMsg(-1,'文件上传类型受限，请在后台配置！',['uploaded' => false, 'error' => ['message' => '文件上传类型受限，请在后台配置！']]);
        }
		//php文件限制
        if ($file->checkExt('php')) {
            return $this->showMsg(-1,'可执行文件禁止上传到本地服务器！',['uploaded' => false, 'error' => ['message' => '可执行文件禁止上传到本地服务器！']]);
        }
        $this->safe = $this->getUploadSafe();
        $this->uptype = $this->getUploadType();
        $this->ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);
        $name = File::name($file->getPathname(), $this->ext, '', 'md5_file');
        $info = File::instance($this->uptype)->save($name, file_get_contents($file->getRealPath()), $this->safe);
        if (is_array($info) && isset($info['url'])) {
        	if($this->uptype=="qiniu"){
                $info['url']="http:".$info['url'];
            }
           return $this->showMsg(1,'文件上传成功！',['uploaded' => true, 'filename' => $name, 'url' => $this->safe ? $name : $info['url']]);
        }
        return $this->showMsg(-1,'文件处理失败，请稍候再试！',['uploaded' => false, 'error' => ['message' => '文件处理失败，请稍候再试！']]);
    }

    /**
     * 获取文件上传方式
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function getUploadType()
    {
        $this->uptype = input('uptype');
        if (!in_array($this->uptype, ['local', 'oss', 'qiniu'])) {
            $this->uptype = sysconf('storage_type');
        }
        return $this->uptype;
    }

    /**
     * 获取上传安全模式
     * @return boolean
     */
    private function getUploadSafe()
    {
        return $this->safe = boolval(input('safe'));
    }

    /**
     * 获取本地文件对象
     * @return \think\File
     */
    private function getUploadFile()
    {
        try {
			$file = request()->file('file');
            return $file;
        } catch (\Exception $e) {
            return $this->showMsg(-1,lang($e->getMessage()));
        }
    }
}