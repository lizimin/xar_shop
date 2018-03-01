<?php
namespace app\cgj\controller;
use think\Controller;
use think\Cache;
use app\common\controller\BaseApi;
class Shopinfo extends BaseApi
{
	public function shopList(){
		$result = array();
		$result = db('shop_info')->where(['is_status'=>1])->select();
		return $this->sendResult($result);
	}
}