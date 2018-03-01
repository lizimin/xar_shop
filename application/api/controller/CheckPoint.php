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

use app\common\controller\ApiBase;

class CheckPoint extends ApiBase
{
	//定义参数
	protected $model_shop_check_point;

	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();

		$this->model_shop_check_point      = model('common/ShopCheckPoint');
	}

	
	/**
	 * 获取检查点	 * 
	 *
	 * @param integer $chk_type
	 * @return void
	 */
	public function getCheckPoint($chk_type=0){
		//初始化
		$return  =  [];
		$message = 'success';
		$errcode = 0;

		//获取参数
		$chk_type = input('chk_type', $chk_type ,'intval');
		//if($chk_type){
			//查询条件
			$map = [
				'chk_type' => $chk_type,
				'is_status' => 1,
			];
			
			$return_data = db('ShopCheckPoint')->where($map)->field('chk_id,chk_type,chk_name,chk_dep,chk_remarks,shop_id')->select();	
			if($return_data){
				foreach ($return_data as $key =>&$value) {
					$value['detailOption'] = [
						0 => [
							'type_id'   => 1,
							'type_name' =>'刮痕',
							'status'    => 0
						],
						1 => [
							'type_id'   => 2,
							'type_name' =>'擦伤',
							'status'    => 0
						],
						2 => [
							'type_id'   => 3,
							'type_name' =>'其他',
							'status'    => 0
						],
					];
				}

				$return = $return_data;
			//}
		}
		
		return $this->sendResult($return,$errcode,$message);
	}
}