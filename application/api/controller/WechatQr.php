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
use EasyWeChat\Foundation\Application;
class WechatQr extends ApiBase
{	//变量定义
	public $app;
	public $qrcode;

	/**
	 * 初始化
	 * @return void
	 */
	public function __construct(){
		parent::__construct();

		$this->app = new Application(config('wechat_config'));
		$this->qrcode = $this->app->qrcode;
	}
	/**
	 * @Author   liutianpeng
	 * @DateTime 2017-12-21
	 * @param    string              $qr_key [参数二维码所带参数]
	 * @param    integer             $expire [过期时间]
	 * @return   [type]              [description]
	 */
	public function createQr($qr_key = '1', $expire = 3600, $type = 1){
		$qr_key = $qr_key . '|x|' . time();
		if($type == 1) $res = $this->qrcode->temporary($qr_key, $expire);
		if($type == 2) $res = $this->qrcode->forever($qr_key);
		$res = $res->all();
		$res['qrurl'] = $this->qrcode->url($res['ticket']);

		$save_arr = [
			'type' => $type,
			'expire' => time() + $expire,
			'qr_key' => $qr_key,
			'create_time' => time(),
			'url' => $res['qrurl'],
			'ticket' => $res['ticket'],
			'customer_id' => $this->customer_id
		];
		db('scence_qrcode')->insert($save_arr);
		return $res;
	}
	public function test(){
		$code = input('code', 'product|x|51|x|23');
		$res = $this->createQr($code, 7200);
		dump($res);
	}	
	public function productQr(){
		$shop_user = $this->getShopUser();
		if($this->customer_id && $shop_user){
			$pro_id = input('pro_id', 51, 'intval');
			$ac_id = input('ac_id', 0, 'intval');
			$referer = input('referer', $this->customer_id, 'intval');

			$result = $this->createQr('product|x|'.$pro_id.'|x|'.$referer.'|x|'.$shop_user['shop_user_id'].'|x|'.$ac_id, 30 * 24 * 60 * 60);
		}
		return $this->sendResult($result);
		// echo '<image src="'.$res['qrurl'].'"></image>';
		// dump($res);
	}	
	public function youhuiQr($customer_id=23){
		$this->customer_id = $customer_id;
		$result = $this->createQr('youhui|x|0124|x|'.$customer_id, 30 * 24 * 60 * 60);
		// dump($result);
		return $result;
		// echo '<image src="'.$res['qrurl'].'"></image>';
		// dump($res);
	}

	public function getYouhuiQr(){
		$result = $this->youhuiQr($this->customer_id);
		return $this->sendResult($result);
	}
}