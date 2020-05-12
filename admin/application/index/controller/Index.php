<?php



namespace app\index\controller;

use library\Controller;

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Index extends Controller
{
    /**
     * 入口跳转链接
     */
    public function index()
    {
        $this->redirect('@admin/login');
    }

}
