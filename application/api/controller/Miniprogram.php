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

use think\Cache;
use app\common\controller\ApiBase;
use EasyWeChat\Foundation\Application;
use EasyWeChat\MiniProgram\Encryption\Encryptor;
class miniProgram extends ApiBase
{
	//变量定义
	public $app;
	public $miniProgram;

	/**
	 * 初始化
	 * @return void
	 */
	public function __construct(){
		parent::__construct();

		$this->app = new Application(config('wechat_config'));
		$this->miniProgram = $this->app->mini_program;
	}
	

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
    public function index($code='')
    {
		//获取code
		$code = input('code', $code ,'string');
		//通过code获取小程序
		$res_data = $this->miniProgram->sns->getSessionKey($code);

		$res = $res_data->all();
		$str = md5($res['openid'].$res['session_key']);
		//存储至用户数据库之后的用户信息。
		$user_info = array();
        $shop_user = model('ShopUser')->get_shop_user_info($res['openid'], $res['unionid'], '*', true);
        Cache::set($str, array_merge((array)$res,$shop_user), 7200);
		$result = array(
            'reqkey'=>$str,
            'expires_in'=>time()+7200,
            'user' => $shop_user['user'],
            'user_shop' => $shop_user['user_shop'],
        );
		
        return $this->sendResult($result);
    }
    public function miniProgram(){
    	return $this->miniProgram;
    }


    /**
     * 小程序的用户数据转化为公众平台用户数据。
     * @Author   liutianpeng
     * @DateTime 2017-12-20
     * @param    array       $xcx_data [小程序用户数据]
     * @return   [type]                [返回公众平台用户数据]
     */
    public function mini_data_to_pub_data($xcx_data = []){
    	$result = array();
    	$result['nickname'] = isset($xcx_data['nickName']) ? $xcx_data['nickName']: '';
    	$result['headimgurl'] = isset($xcx_data['avatarUrl']) ? $xcx_data['avatarUrl']: '';
    	$result['sex'] = isset($xcx_data['gender']) ? $xcx_data['gender']: '';
    	$result['province'] = isset($xcx_data['province']) ? $xcx_data['province']: '';
    	$result['country'] = isset($xcx_data['country']) ? $xcx_data['country']: '';
    	$result['city'] = isset($xcx_data['city']) ? $xcx_data['city']: '';
    	$result['unionid'] = isset($xcx_data['unionId']) ? $xcx_data['unionId']: '';
    	$result['openid'] = isset($xcx_data['openId']) ? $xcx_data['openId']: '';
    	return $result;
    }

    public function getPhone(){

    	$result = array();
    	$errcode = 0;
    	if($this->xcx_client_info){

    		$sessionKey = $this->xcx_client_info['session_key'];
    		$iv = input('iv', '');
    		$encrypted = input('encrypted', '');
    		if($iv && $encrypted){
    			$result = $this->app->mini_program->encryptor->decryptData($sessionKey, $iv, $encrypted);

    		}else{
    			$errcode = 13002; //小程序登录态过期
    		}
    	}else{
    		$errcode = 13001; //小程序登录态过期
    	}
    	return $this->sendResult($result, $errcode);
    }

    public function userInfo(){
        $reqkey = input('reqkey', '' ,'string');
        $userInfo = Cache::get($reqkey);
        dump($userInfo);
    }

    public function test(){
        $res = model('ShopUser')->get_shop_user_info(['unionid'=>'oV6-vt-BbRN_YVEeXgsitR9FmtQo'], '*', true);
        dump($res);
    }

    public function file_upload(){
        $model_attachment = model('common/Attachment');
        $res = $model_attachment->upload();
        return $this->sendResult($res);
    }

    public function Qrcode(){
        $path = input('path', '');
        $result = [];
        $errcode = 0;
        if($path){
            $res = $this->miniProgram->qrcode->getAppCode($path);
            $result['img_data'] = 'data:image/png;base64,'.base64_encode($res);
        }else{
            $errcode = 13005;
        }
        // echo $res;
        return $this->sendResult($result, $errcode);
    }
}