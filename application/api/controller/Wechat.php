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
// use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Text;
use think\Session;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Support\File;
class Wechat extends ApiBase
{	//变量定义
	public $app;
	public $qrcode;
	public $staff;

	/**
	 * 初始化
	 * @return void
	 */
	public function __construct(){
		// parent::__construct();
		
		$this->app = new Application(config('wechat_config'));
	}
	/**
	 * @Author   liutianpeng
	 * @DateTime 2017-12-21
	 * @param    string              $qr_key [参数二维码所带参数]
	 * @param    integer             $expire [过期时间]
	 * @return   [type]              [description]
	 */
	public function ckechFollow(){
		$result = [];
		$errcode = 0;
		$message = '';
		if($this->user_info && $this->user_info['openid']){
			$user_info = $this->app->user->get($this->user_info['openid'])->all();
			$result['subscribe'] = $user_info && $user_info['subscribe'] ? 1 : 0;
		}else{
			$errcode = 1001;
		}
		return $this->sendResult($result,$errcode,$message);
	}

	//客服消息发送文本。
	public function sendStaffMsgText($msg = '', $openid = ''){
		$result = false;
		if($msg && $openid){
			$text = new Text(['content'=>$msg]);
			$result = $this->app->staff->message($text)->to($openid)->send();
		}
		return $result;
	}

	public function oauth(){
    	$code = input('code', '');
    	$app = $this->app;
    	$user = Session::get('wechat_user');
    	if ($code && !$user) {
    		$oauth = $app->oauth;
			// 获取 OAuth 授权结果用户信息
			$user = $oauth->user();
			$user = $user->toArray();
			Session::set('wechat_user', $user);
            $targetUrl = Session::get('target_url') ? Session::get('target_url') : '/' ;
            header('location:'. $targetUrl);
    	} elseif (!$user) {
            $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
            Session::set('target_url', $url);
	    	$response = $app->oauth->scopes(['snsapi_userinfo'])->redirect($url);
	        // dump($response);
	        $response->send();
        }
        return $user;
    }

    public function getJssdk(){
    	$url = input('url', '');
    	$js = $this->app->js;
    	$apis = ['checkJsApi','onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone','hideMenuItems','showMenuItems','hideAllNonBaseMenuItem','showAllNonBaseMenuItem','translateVoice','startRecord','stopRecord','onVoiceRecordEnd','playVoice','onVoicePlayEnd','pauseVoice','stopVoice','uploadVoice','downloadVoice','chooseImage','previewImage','uploadImage','downloadImage','getNetworkType','openLocation','getLocation','hideOptionMenu','showOptionMenu','closeWindow','scanQRCode','chooseWXPay','openProductSpecificView','addCard','chooseCard','openCard'];
    	if($url) $js->setUrl($url);
    	$jssdk = $js->config($apis, false, false, false);
    	return $this->sendResult($jssdk);
    }

    public function saveMedia(){
    	$mediaId = input('mediaId', '_JBlVmGvAgFRAU2TxFb9JhM1LQDu2urJdL2V_qd8p1iyhrkEdxCvzEw-wabo6a86');
    	$result = [];
    	if($mediaId){
    		$attach = model('Attachment');
    		$result = $attach->where(['media_id'=>$mediaId])->find();
    		if(!$result){
	    		$temporary = $this->app->material_temporary;
	    		$stream = $temporary->getStream($mediaId);
	    		$filename = time().rand(10000, 99999);
	        	$ext = File::getStreamExt($stream);
	        	$filename .= $ext;
	        	$dir = 'public'.DS.'uploads'.DS.'wehcat'.DS.date('Ymd').DS;
	    		$path = $url = $dir.$filename;
	    		if(!is_dir(ROOT_PATH.$dir)) mkdir(ROOT_PATH.$dir, 0755, true);
	    		$rootpath = ROOT_PATH.$path;
	        	file_put_contents($rootpath, $stream);
	    		$result = [
	    			'ext'  => $ext,
	    			'name' => $filename,
	    			'path' => DS.$path,
	    			'url'  => DS.$url,
	    			'rootpath' => ROOT_PATH,
	    			'full_path' => $rootpath,
	    			'domain' => request()->domain(),
	    			'media_id' => $mediaId,
	    			'create_time' => time(),
	    		];
	    		$attach->insert($result);
	    		$result['aid'] = $attach->getLastInsId();
    		}

    	}
    	return $this->sendResult($result);
    }
}