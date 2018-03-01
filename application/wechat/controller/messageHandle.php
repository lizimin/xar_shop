<?php
namespace app\wechat\controller;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Factory;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Image;
use think\Log;
class MessageHandle
{
	private $message = null;
	private $customer = null;
	private $customer_id = 0;
	private $openid = '';
	private $unionid = '';
	private $cname = '';
	private $cavatar = '';
	private $cprovince = '';
	private $ccountry = '';
	private $ccity = '';
	private $csex = 0;
	private $shop_id = 0;

	public $result = null;
	public $app = null;

	public $MsgType = '';
	public $Event = '';

	public $Content = ''; //  当前提问的文本内容、当 消息类型为文本时、则为 文本消息类容、为语音时、则为、翻译后的文本内容

	public $MediaId = '';  //图片、语音、视频等的media的id

	public function __construct($message = [], $customer = []){
		$this->message = $message;
		$this->customer = $customer;
		$this->customer_id = $customer['customer_id'];
		$this->openid = $customer['openid'];
		$this->unionid = $customer['unionid'];
		$this->cname = isset($customer['customer_info']['cname']) ? $customer['customer_info']['cname'] : '';
		$this->cavatar = isset($customer['customer_info']['cavatar']) ? $customer['customer_info']['cavatar'] : '';
		$this->csex = isset($customer['customer_info']['csex']) ? $customer['customer_info']['csex'] : '';
		$this->cprovince = isset($customer['customer_info']['cprovince']) ? $customer['customer_info']['cprovince'] : '';
		$this->ccountry = isset($customer['customer_info']['ccountry']) ? $customer['customer_info']['ccountry'] : '';
		$this->ccity = isset($customer['customer_info']['ccity']) ? $customer['customer_info']['ccity'] : '';
		$this->MsgType = $message->MsgType;
		$this->MediaId = $message->MediaId;
		$this->Event = $message->Event;
		$this->app = new Application(config('wechat_config'));
		switch ($this->MsgType) {
			case 'text':
				$this->Content = $message->Content;
				break;
			case 'voice':
				$this->Content = $message->Recognition;
				break;
			
			default:
				# code...
				break;
		}

	}

	public function responseMessage(){
		Log::write($this->Content, 'wechat');
		if($this->Content == '小矮人666'){
			$this->result = 'ID:'.$this->customer_id."
昵称:".$this->cname;
		}
		if($this->Content == '优惠券' || $this->Content == '优惠卷'){
			$qr_data = controller('api/WechatQr')->youhuiQr($this->customer_id);
			$image_path = download_img($qr_data['qrurl']);
			$res = $this->app->material_temporary->uploadImage($image_path);
			$this->result = new Image(['media_id' => $res->media_id]);
			// dump($res->media_id);

		}
		return $this->result;
	}


}