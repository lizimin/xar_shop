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
// | @Description 文件描述： 店铺接待人接口

namespace app\api\controller;

use think\Cache;
use app\common\controller\ApiBase;

class ShopUserGroup extends ApiBase
{

	//定义参数
	protected $model_shop_user_group;

	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();

		$this->model_shop_user_group = model('common/ShopUserGroup');
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

	
	/**
	 * 获取用户信息
	 *
	 * @param integer $openid
	 * @return void
	 */
	public function getShopUserGroup($shop_id=''){
		//初始化
		$return = $return_data = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		//获取参数
		$shop_id = input('shop_id',$shop_id,'string');
		
		if($shop_id){
			$map['shop_id'] = array('eq',$shop_id);
			$map['status']  = array('gt',0);
			
			//调用模型方法
			$data = $this->model_shop_user_group->get_shop_user_group_list($map);
			
			if($data){
				$return = $data;
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}

}