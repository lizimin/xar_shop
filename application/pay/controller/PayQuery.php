<?php 
namespace app\pay\controller;
use Payment\Client\Query;
use Payment\Config;
use app\common\controller\ApiBase;
use Payment\Common\PayException;
/**
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class TestNotify
 * anthor helei
 */
class Payquery extends ApiBase
{
	public function query(){
		$result = array();
		$query_type = input('query_type', '');
		$order_no = input('order_no', '');

		$msg = '';
		$status = 1;
		if($order_no && $query_type){
			date_default_timezone_set('Asia/Shanghai');
			$data = [
			    'out_trade_no' => $order_no,
			];
			$result = array();
			$payConfig = array();
			if(strstr($query_type, 'ali')){
				$payConfig = config('ali_pay_config');
			}
			if(strstr($query_type, 'wx')){
				$payConfig = config('wechat_pay_config');
			}
			try {
			    $result = Query::run($query_type, $payConfig, $data);
			} catch (\Exception $e) {
			    $msg = $e->errorMessage();
			}
		}else{
			$status = 10002;
		}
		return $this->sendResult($result, $errCode, $msg);
	}
}