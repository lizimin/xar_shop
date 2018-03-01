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
// | @Description 文件描述： API接口

namespace app\api\controller;

use think\Controller;
use think\Cache;
use app\common\controller\ApiBase;

class Service extends ApiBase
{
	//变量定义
	private $model_info, $model_user;

	/**
	 * 初始化
	 *
	 * @return void
	 */
    protected function _initialize(){
        parent::_initialize();
        //$this->model_info = model('common/ShopInfo');
        //$this->model_user = model('common/ShopUser');
    }

	/**
	 *  获取店铺服务
	 *
	 * @param integer $shop_id
	 * @return void
	 */
    public function getshopService($shop_id=0)
    {
		//初始化
		$return = $result  =  $service_group = [];
		$message = 'success';
		$errcode = 0;

		//获取参数
		$shop_id = input('shop_id',$shop_id,'intval');
		
		if($shop_id){
			$service_group = db('shop_order_service_group')->where(['shop_id'=>$shop_id,'pid'=>0, 'is_status'=>1])->field('sg_id,pid,sg_name,shop_id')->select();

	        foreach ($service_group as $key => $value) {

	        	$service_list = $service_info = array();

				$service_list = db('shop_order_service_relation')->where(['group_id'=>$value['sg_id'],'is_status'=>1])->column('service_id');
				$service_info = db('shop_order_service')->where(['service_id'=>array('in',$service_list), 'is_status'=>1])->field('service_id,service_name,service_price,service_type,service_multi,shop_id,shop_group_id')->select();
				$service_group[$key]['service'] = $service_info;
			}

			$return['service_group'] = $service_group;
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}
        
        return $this->sendResult($return,$errcode,$message);
    }


}