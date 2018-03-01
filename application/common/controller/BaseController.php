<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com 
//+----------------------------------------------------------------------
// | @Author: lishaoenbh@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: lishaoen
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-12-11 13:35:54 
// +----------------------------------------------------------------------
// | @Description 文件描述：通用控制器基类

namespace app\common\controller;

use think\Controller;
use think\Config;
use think\App;
use think\Request;
use think\Response;
use think\Url;
use think\Exception;
use think\exception\HttpResponseException;

class BaseController extends Controller
{
    
	// 请求参数
    protected $param;
    
    /**
     * 基类初始化
     */
    protected function _initialize()
    {
        // 初始化请求信息
        $this->initRequestInfo();
        // 初始化全局静态资源
        $this->initCommonStatic();
        // 初始化响应类型
        $this->initResponseType();
    }
    
    
    /**
     * 初始化请求信息
     */
    final private function initRequestInfo()
    {
        
        defined('IS_POST')          or define('IS_POST',         $this->request->isPost());
        defined('IS_GET')           or define('IS_GET',          $this->request->isGet());
        defined('IS_AJAX')          or define('IS_AJAX',         $this->request->isAjax());
        defined('IS_PJAX')          or define('IS_PJAX',         $this->request->isPjax());
        defined('MODULE_NAME')      or define('MODULE_NAME',     $this->request->module());
        defined('CONTROLLER_NAME')  or define('CONTROLLER_NAME', $this->request->controller());
        defined('ACTION_NAME')      or define('ACTION_NAME',     $this->request->action());
        defined('URL')              or define('URL',             strtolower($this->request->controller() . SYS_DS_PROS . $this->request->action()));
        defined('URL_MODULE')       or define('URL_MODULE',      strtolower($this->request->module()) . SYS_DS_PROS . URL);
        defined('URL_TRUE')         or define('URL_TRUE',        $this->request->url(true));
        defined('DOMAIN')           or define('DOMAIN',          $this->request->domain());
        
        $this->param = $this->request->param();
    }
    
    /**
     * 初始化响应类型
     */
    final private function initResponseType()
    {
        IS_AJAX && !IS_PJAX  ? config('default_ajax_return', 'json') : config('default_ajax_return', 'html');
    }
    
    /**
     * 初始化全局静态资源
     */
    final protected function initCommonStatic()
    {
        //$this->assign('xxx', config('xxx'));
    }


    
}
