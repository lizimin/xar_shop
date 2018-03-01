<?php
namespace app\api\controller;
use think\Controller;
use think\Cache;
use app\common\controller\ApiBase;

class Shopinfo extends ApiBase
{
	public function shopList(){
		$result = array();
		$result = db('shop_info')->where(['is_status'=>1])->select();
		return $this->sendResult($result);
	}
	//附近5公里内的店铺
	public function getNearShop(){
		$lat = input('lat', 0);
		$lng = input('lng', 0);
		$result = [];
		$type = input('type', 0);
		if($lat && $lng){
			if($type){
				$result = model('shopInfo')->getNearOneShop($lat, $lng);
			}else{
				$result = db('shop_info')->field("*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($lat*PI()/180-shop_lat*PI()/180)/2),2)+COS($lat*PI()/180)*COS(shop_lat*PI()/180)*POW(SIN(($lng*PI()/180-shop_lng*PI()/180)/2),2)))*1000) AS juli")->where(['is_status'=>1])->order('juli asc')->select();
			}

		}
		return $this->sendResult($result);
	}

	public function getShopInfo(){
		$shop_id = input('shop_id', 0);
		$result = [];
		if($shop_id){
			$result = db('shop_info')->where(['shop_id'=>$shop_id])->find();
		}
		return $this->sendResult($result);
	}
}