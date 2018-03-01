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
//分销购买成功通知
class AgentNotice extends BaseNotice
{
	public $data = [];
	public $template_id = 'MfRIOLaSm3p8SLlFqpmgj8bSh1VdKtZo0Uw60a-OgRo';
	public $defaultColor = '#000';
	public function createData($orderInfo = array(), $amount = 0, $lavel = 0, $scale = 0){
		if($orderInfo){
			$pro_name = '';
			foreach ($orderInfo['goods'] as $key => $value) {
				$pro_name.= $value['pro_name'].'、';
			}
			$this->data = [
	    		'first' => ['您分销的商品有买家支付成功、佣金已记录到您的账户上。'.$lavel.'-'.$scale],
	    		'keyword1' => [$pro_name],
	    		'keyword2' => [$amount],
	    		'keyword3' => ['支付成功'],
	    		'remark' => ['
感谢您的支持。'],
	    	];
		}

	}
	//根据订单号进行通知。
	public function notice($orderInfo = array(), $amount = 0, $customer_id = 0, $lavel = 0, $scale = 0){
		if($orderInfo && $amount && $customer_id){
			$openid = model('CustomerAuth')->where(['customer_id'=>$customer_id, 'auth_idf'=> 'xarwx'])->value('openid');
			if($openid){
				$this->createData($orderInfo, $amount, $lavel, $scale);
				$res = $this->WechatNotice($openid, $this->template_id, $this->url, $this->data);
				dump($res);
			}

		}
	}
	public function test(){
		$order_no = 'C180111204145830399';
		$this->notice($order_no);
	}
}