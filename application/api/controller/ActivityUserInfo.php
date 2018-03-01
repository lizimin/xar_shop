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
use think\Db;
use app\common\controller\ApiBase;

class ActivityUserInfo extends ApiBase{

	public function addActivityUser(){
		$shop_user = $this->getShopUser();
		$error = 0;
		$result = [];
		$shop_user = ['shop_user_id' => 13];
		if($shop_user){
			$jcd_no = input('jcd_no', '');
			$amount = input('amount', 0);
			$xcd_no = input('xcd_no', '');
			$plate = input('plate', 0);
			if(($jcd_no || $xcd_no) && $amount && $plate){
				$db_activity_userinfo = db('activity_userinfo');
				$save_arr = [
					'jcd_no' => $jcd_no,
					'amount' => $amount,
					'xcd_no' => $xcd_no,
					'plate' => $plate,
					'shop_user_id' => $shop_user['shop_user_id'],
					'create_time' => time(),
				];
				$db_activity_userinfo->insert($save_arr);
				$result = ['ac_id'=>$db_activity_userinfo->getLastInsId()];
			}else{
				$error = 1002;
			}
		}else{
			$error = 13004;
		}
		return $this->sendResult($result, $error);
	}
}