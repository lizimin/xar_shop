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
// | @Last Modified time: 2017-11-30 10:57:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： 店铺车辆信息接口

namespace app\api\controller;

use EasyWeChat\Foundation\Application;
use think\Cache;
use app\common\controller\ApiBase;

class ShopCar extends ApiBase
{
	//定义参数
	protected $model_shop_car;

	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();

		$this->model_shop_car      = model('common/ShopCar');
	}
	
	/**
	 * 默认方法
	 *
	 * @return void
	 */
	public function index(){
		$result = array();
		
		return $this->sendResult($result);
	}
	
}