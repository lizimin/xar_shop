<?php 
namespace app\pay\controller;
use Payment\Client\Notify;
use Payment\Config;
use app\common\controller\ApiBase;
use Payment\Common\PayException;
/**
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class TestNotify
 * anthor helei
 */
class Paynotice extends ApiBase
{
	public function notice(){
		date_default_timezone_set('Asia/Shanghai');
		$callback = new HandelNotice();
		$type = Config::WX_CHARGE;
		$config = config('wechat_pay_config');
		try {
		    $ret = Notify::run($type, $config, $callback);// 处理回调，内部进行了签名检查
		    if($ret) echo 'SUCCESS';
		    // file_put_contents('pay.text', json_encode($ret));
		} catch (PayException $e) {
		    echo $e->errorMessage();
		    exit;
		}
	}
	public function alinotice(){
		date_default_timezone_set('Asia/Shanghai');
		$callback = new HandelNotice();
		$type = Config::ALI_CHARGE;
		$config = config('ali_pay_config');
		try {
		    $ret = Notify::run($type, $config, $callback);// 处理回调，内部进行了签名检查
		    if($ret) echo 'SUCCESS';
		    // file_put_contents('pay.text', json_encode($ret));
		} catch (PayException $e) {
		    echo $e->errorMessage();
		    exit;
		}
	}
}