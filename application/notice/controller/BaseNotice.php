<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Author: 1296969641@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: 柳天鹏
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 10:57:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： API接口

namespace app\notice\controller;

use EasyWeChat\Foundation\Application;
use think\Controller;

class BaseNotice extends Controller
{
	//变量定义
	public $app;
	public $notice;
    public $defaultColor = '#000';
    public $url = 'http://mall.gotomore.cn/index.html#/';
	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();
		$this->app = new Application(config('wechat_config'));
		$this->notice = $this->app->notice;
	}
	/**
	 * @Author   liutianpeng
	 * @DateTime 2017-12-09
	 * @param    string      $touser      [接受者]
	 * @param    string      $template_id [模板消息ID]
	 * @param    string      $url         [跳转连接]
	 * @param    array       $data        [发送数据]
	 * @return   [type]                   [description]
	 */
    public function WechatNotice($touser = '', $template_id = '', $url = '', $data = [])
    {
    	$res = false;
        if($touser && $template_id && $url && $data){
            $this->notice->defaultColor($this->defaultColor);
    		$res = $this->notice->send([
    			'touser' => $touser,
    			'template_id' => $template_id,
    			'url' => $url,
    			'data' => $data,
    		]);
    	}
		return $res;
    }

    public function test(){
    	$data = [
    		'first' => ['我们已经收到您的订单'],
    		'keyword1' => ['0.01', '#eeeeee'],
    		'keyword2' => ['C12hhhHHAHHAH', '#888888'],
    		'remark' => ['感谢您的支持。', '#eeeeee'],
    	];
    	$res = array();

    	$accessToken = $this->app->access_token->getToken();
    	try {
    		$res = $this->sendMsg('oux15wmbP9OGLXxbQ7qgff1zm7Fg', 'BVEbjVgIXUFFLMyvQDoruB5JgFcy13-iq9YThwXSYWs', 'http://xcxcgj.gotomore.cn/index.html#/', $data);
    	} catch (\HttpException $e) {
    		dump($e);
    	}
    	
    	dump($res);
    }
}