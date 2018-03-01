<?php
namespace app\cgj\controller;
use app\common\controller\BaseApi;
class Checkpoint extends BaseApi
{
	public function getCheckPoint(){
		$chk_type = input('chk_type', 0);
		$result = array();
		$map = [
			'chk_type' => $chk_type,
			'is_status' => 1,
		];
		$result = db('shop_check_point')->where($map)->select();
		return $this->sendResult($result);
	}
}