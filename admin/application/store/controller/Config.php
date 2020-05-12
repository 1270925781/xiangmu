<?php
namespace app\store\controller;

use app\store\service\ExtendService;
use library\Controller;

/**
 * 展厅参数配置
 * Class Config
 * @package app\store\controller
 */
class Config extends Controller
{

    /**
     * 展厅参数配置
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->applyCsrfToken();
        $this->title = '展厅参数配置';
        if ($this->request->isGet()) {
            $this->query = ExtendService::querySmsBalance();
            $this->fetch();
        } else {
            foreach ($this->request->post() as $k => $v) sysconf($k, $v);
            $this->success('展厅参数配置保存成功！');
        }
    }

    /**
     * 展厅短信配置
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sms()
    {
        $this->applyCsrfToken();
        $this->title = '展厅短信配置';
        if ($this->request->isGet()) {
            $this->query = ExtendService::querySmsBalance();
            $this->fetch();
        } else {
            foreach ($this->request->post() as $k => $v) sysconf($k, $v);
            $this->success('展厅短信配置保存成功！');
        }
    }

}