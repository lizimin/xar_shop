<?php
namespace app\pay\controller;
use EasyWeChat\Foundation\Application;
use think\Cache;
use app\common\controller\ApiBase;
use Payment\Common\PayException;
use Payment\Client\Refund;
use Payment\Client\Charge;
use think\Exception;
class Payment extends ApiBase
{	
	public $aliPayList    = ['ali_wap', 'ali_app', 'ali_web'];
	public $wechatPayList = ['wx_pub'];
	public $db_mall_pro_sku = null;
	public $db_mall_order_goods = null;
    protected function _initialize(){
        parent::_initialize();
        $this->db_mall_pro_sku = db('mall_pro_sku');
		$this->db_mall_order_goods = db('mall_order_goods');
    }

	//通过sku_id查询当前订单的订单总价。
	public function createAmount($pro_arr = array(), $out_trade_no = ''){
		$amount = 0;
		
		foreach ($pro_arr as $key => $value) {
			if($value['sku_id'] && $value['pro_quantity']){
				$mall_price = $this->db_mall_pro_sku->where(['sku_id' => $value['sku_id']])->value('mall_price');
				$pro_total_price = $mall_price * $value['pro_quantity'];
				$amount += $pro_total_price;
				$save_arr = [
					'out_trade_no'   => $out_trade_no,
					'pro_id' 	     => $value['pro_id'],
					'pro_name'       => $value['pro_name'],
					'sku_id'         => $value['sku_id'],
					'pro_total_price'=> $pro_total_price,
					'pro_price'      => $mall_price,
					'pro_quantity'   => $value['pro_quantity'],
					'create_time'    => time(),
					'update_time'    => time(),
				];
				$this->db_mall_order_goods->insert($save_arr);
			}
		}
		return $amount;
	}
	//检测当前产品sku是否限制新用户才能购买。
	public function checkNeedNewUser($proinfo = array()){
		$result = false;
		foreach ($proinfo as $key => $value) {
			$sku_is_new_user = $this->db_mall_pro_sku->where(['sku_id' => $value['sku_id']])->value('is_new_user');
			if($sku_is_new_user){
				$buy_num = model('MallOrder')->alias('a')->join('cys_mall_order_goods b', 'a.order_id = b.order_id')->where(['a.customer_id' => $this->customer_id, 'b.sku_id' => $value['sku_id']])->count();
				if($buy_num) $result = true; 
			}
		}
		return $result;
	}

	//检测当前产品sku是否限制新用户才能购买。
	public function checkMaxCount($proinfo = array()){
		$result = false;
		foreach ($proinfo as $key => $value) {
			$buy_max_count = $this->db_mall_pro_sku->where(['sku_id' => $value['sku_id']])->value('buy_max_count');
			if($buy_max_count){
				$buy_num = model('MallOrder')->alias('a')->join('cys_mall_order_goods b', 'a.order_id = b.order_id')->where(['a.customer_id' => $this->customer_id, 'b.sku_id' => $value['sku_id']])->value('sum(b.pro_quantity)');
				if($buy_num + $value['pro_quantity'] > $buy_max_count) $result = true; 
			}
		}
		return $result;
	}


	//其与控制器拉起支付
	//
	public function getPayData($pay = []){
		$result = $data = [];
		$errCode = 0;
		$msg = '';
		if($pay && isset($pay['body']) && isset($pay['subject']) && isset($pay['orderNo']) && isset($pay['pay_type']) && isset($pay['amount']) && isset($pay['order_type']) && $this->openid){
			$return_param = isset($pay['return_param']) ? $pay['return_param'] : '';
			$return_param_arr = isset($pay['return_param']) ? json_decode($pay['return_param'], true) : [];
			//推荐人uid
			$referer = isset($return_param_arr['referer']) ? $return_param_arr['referer'] : 0;
			$shop_id = isset($return_param_arr['shop_id']) ? $return_param_arr['shop_id'] : 0;
			$pay_type = $pay['pay_type'];
			$amount = $pay['amount'];
			$order_type = $pay['order_type'];
			$openid = $this->openid;
			$customer_id = $this->customer_id;
			$product_id = isset($pay['product_id']) ? $pay['product_id'] : 0;
			$orderNo = $pay['orderNo'];
			$payData = [
				'body' => $pay['body'],
				'subject' => $pay['subject'],
				'amount' => $amount,
				'return_param' => $return_param,
				'client_ip' => '127.0.0.1',// 客户地址
				'order_no' => $orderNo,
				'product_id' => $product_id,
				'timeout_express' => time() + 600
			];
			switch ($pay_type) {
				case 'wx_pub':
					if($openid){
						$payData['openid'] = $openid;
					}else{
						$errCode = 12003;
					}
					break;
			}
			if(in_array($pay_type, $this->wechatPayList)){
				$payConfig = config('wechat_pay_config');
			}
			if(in_array($pay_type, $this->aliPayList)){
				$payConfig = config('ali_pay_config');
			}
			try {
				$data = Charge::run($pay_type, $payConfig, $payData);
				$pay_data = array(
			    	'out_trade_no' => $orderNo,
			    	'customer_id' => $customer_id,
			    	'order_amount' => $amount,
			    	'create_time' => time(),
			    	'update_time' => time(),
			    	'pay_data' => json_encode($data),
						'pay_type' => $pay_type,
						'shop_id' => $shop_id,
			    	'order_type' => $order_type,
			    	'referer' => $referer
			    );
			    $db_mall_order = db('mall_order');
			    $db_mall_order->insert($pay_data);
				$data['order_id'] = $db_mall_order->getLastInsID();
				
			} catch (PayException $e) {
			    $msg =  $e->errorMessage();
			    $errCode = 15001;
			}
		}else{
			$errCode = 15001;
		}	
		$result = [
			'data' => $data,
			'error' => $errCode,
			'message' => $msg
		];
		return $result;
	}

	//商城支付
	public function payCharge(){
		$errCode       = 0;
		$body          = input('body', '', 'trim');
		$subject 	   = input('subject', '', 'trim');
		$customer_id   = $this->customer_id;
		$openid        = $this->openid;
		$return_param  = input('return_param', '', 'trim');
		$pay_type      = input('pay_type', '', 'trim');
		$product_id    = input('product_id', 0);
		$proinfo       = input('proinfo', '');
		$msg           = '';

		$result = $payConfig = array();
		if($body && $subject && $pay_type && $proinfo){
			$orderNo = 'C'.date('ymdHis').mt_rand(100000, 999999);
			$proinfo_arr = json_decode($proinfo, true);
			$amount = $this->createAmount($proinfo_arr, $orderNo);

			if(!$this->checkNeedNewUser($proinfo_arr)){
				if(!$this->checkMaxCount($proinfo_arr)){
					if(in_array($pay_type, $this->wechatPayList) || in_array($pay_type, $this->aliPayList)){
						if(!$errCode){
							$pay = [
								'body' => $body,
								'subject' => $subject,
								'return_param' => $return_param,
								'orderNo' => $orderNo,
								'pay_type' => $pay_type,
								'openid' => $openid,
								'product_id' => $product_id,
								'order_type' => 1, //商城订单
								'amount' => $amount
							];
						    $resultData = $this->getPayData($pay);
						    $result = $resultData['data'];
						    $errCode = $resultData['error'];
						    $msg = $resultData['message'];
						    if($result){
						    	db('mall_order_goods')->where(['out_trade_no'=>$orderNo])->update(['order_id'=>$result['order_id']]);
						    }
						}
					}else{
						$errCode = 12000;
					}
				}else{
					$errCode = 15003;
				}
			}else{
				$errCode = 15002;
			}
		}else{
			$errCode = 1001;
		}
		return $this->sendResult($result, $errCode, $msg);
	}
}