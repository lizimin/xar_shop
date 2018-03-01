<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com 
//+----------------------------------------------------------------------
// | @Author: 1296969641@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: 柳天鹏
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 10:57:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： API接口

namespace app\notice\controller;

class OrderNotice extends BaseNotice
{
	public $data = [];
	public $template_id = 'OaFROlMrecdZVQLF93J_FD5byIiNNy5TLHOZ2Tsbcng';
	public $defaultColor = '#000';
	public function createData($orderInfo = array()){
		if($orderInfo){
			$this->data = [
	    		'first' => ['亲爱的，我们已经收到您的订单
'],
	    		'keyword1' => [$orderInfo['order_amount']],
	    		'keyword2' => [$orderInfo['out_trade_no']],
	    		'remark' => ['
感谢您的支持。'],
	    	];
		}

	}
	//根据订单号进行通知。
	public function notice($order_no = ''){
		$orderInfo   = model('MallOrder')->getOrder($order_no);
		
		if($orderInfo){
			$openid = $orderInfo['pay_notice']['openid'];
			$this->createData($orderInfo);
			$this->WechatNotice($openid, $this->template_id, $this->url, $this->data);
		}
	}
	public function test(){
		$order_no = 'C180111204145830399';
		$this->notice($order_no);
	}
}