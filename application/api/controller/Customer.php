<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com 
//+----------------------------------------------------------------------
// | @Author: lishaoenbh@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: 柳天鹏
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 10:57:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： API接口

namespace app\api\controller;

use app\common\controller\ApiBase;
use Util\HttpCurl;
use think\Cache;
class Customer extends ApiBase
{
	public function getCustomer(){
		$result = [];
		$errcode = 0;
		$message = '';
		$auth_idf = input('auth_idf', 'xarwx');
		if($this->customer_id){
			$result = model('Customer')->getCustomer($this->customer_id, $auth_idf);
		}else{
			$errcode = 10001;
		}
		return $this->sendResult($result,$errcode,$message);
	}
	public function addCustomer($userinfo, $auth_type = '', $auth_idf = ''){
		$openid = $userinfo['openid'];
		$unionid = $userinfo['unionid'];
		$modelCustomerAuth = model('CustomerAuth');
		$customerInfo = array();
		if($openid || $unionid){
			$customerInfo = $modelCustomerAuth->getCustomerInfo($unionid, $openid, $auth_idf);
			if(!$customerInfo){
				$modelCustomer = model('Customer');
				$save_arr = [
					'cname' => isset($userinfo['nickname']) ? $userinfo['nickname'] : '',
					'cavatar' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl']: '',
					'csex' => isset($userinfo['sex']) ? $userinfo['sex'] : '',
					'cprovince' => isset($userinfo['province']) ? $userinfo['province'] : '',
					'ccountry' => isset($userinfo['country']) ? $userinfo['country'] : '',
					'ccity' => isset($userinfo['city']) ? $userinfo['city'] : '',
					'unionid' => isset($userinfo['unionid']) ? $userinfo['unionid'] : '',
				];
				$customer_id = $modelCustomer->addCustomer($save_arr);
				if($customer_id){
					$auth_arr = [
						'customer_id' => $customer_id,
						'auth_type' => $auth_type,
						'auth_idf' => $auth_idf,
						'openid' => isset($userinfo['openid']) ? $userinfo['openid'] : '',
						'unionid' => isset($userinfo['unionid']) ? $userinfo['unionid'] : '',
						'create_time' => time(),
						'update_time' => time()
					];
					$customerInfo = $auth_arr;
					$customerInfo['customer_id'] = $customer_id;
					$customerInfo['customer_info'] = $save_arr;
					$modelCustomerAuth->insert($auth_arr);
				}
			}
		}
		return $customerInfo;

	}

	public function syncLzUser(){
		$openid = input('openid', '');
		
		$result = array();
		$userinfo = HttpCurl::get('http://xxapi.yntulin.com/lizibookapi.php?m=Lizibook&c=UsersWx&a=getXarUserInfo&openid='.$openid);

		$userinfo = json_decode($userinfo, true);
		$userinfo = $userinfo['data']['data_arr'];

		$auth_type = 1;
		$auth_idf = 'weixin';
		$customerInfo = $this->addCustomer($userinfo, $auth_type, $auth_idf);
		$result['expire_time'] = time() + 3600 * 24;
		$token = md5($openid);
		Cache::set($token, $customerInfo, 3600 * 24);

		$result['access_token'] = $token;
		return $this->sendResult($result);
	}

	public function wxLogin(){
		$result = [];
		$state = input('state', '/');
		$user_info = controller('Wechat')->oauth();
		$auth_type = 1;
		$auth_idf = 'xarwx';
		$customerInfo = $this->addCustomer($user_info['original'], $auth_type, $auth_idf);
		$openid = $user_info['id'];
		$token = md5($openid);
		Cache::set($token, $customerInfo, 3600 * 24);
		// dump($token);
		// dump(Cache::get($token));
		$url = 'http://mall.gotomore.cn/index.html?#/home/'.$token.'/'.urlencode($state);
		// dump($url);die;
		// echo $url;
		echo "<script>location.href='".$url."'</script>";
		// return redirect($url);
		// $this->redirect($url);
	}

	public function wxMgrLogin(){
		$result = [];
		$state = input('state', '/');
		$user_info = controller('Wechat')->oauth();
		$auth_type = 1;
		$auth_idf = 'xarwx';
		$customerInfo = $this->addCustomer($user_info['original'], $auth_type, $auth_idf);
		$openid = $user_info['id'];
		$token = md5($openid);
		Cache::set($token, $customerInfo, 3600 * 24);
		// dump($token);
		// dump(Cache::get($token));
		$url = 'http://ma.gotomore.cn/index.html?#/home/'.$token.'/'.urlencode($state);
		// dump($url);die;
		// echo $url;
		echo "<script>location.href='".$url."'</script>";
		// return redirect($url);
		// $this->redirect($url);
	}
	public function mjcdToken(){
		$result = [];
		$state = input('state', '/');
		$user_info = controller('Wechat')->oauth();
		$auth_type = 1;
		$auth_idf = 'xarwx';
		$customerInfo = $this->addCustomer($user_info['original'], $auth_type, $auth_idf);

		// dump($user_info);
		$unionid = $user_info['original']['unionid'];

		$token = md5($unionid);
		// dump($unionid);
		// dump($token);
		$shop_user_info = model('common/ShopUser')->get_shop_user_info('', $unionid,'shop_user_id,uname,urealname,nickname,headimgurl,usex,utel,uaddress,unionid,group_id,shop_id',true);
		// dump($shop_user_info);
		Cache::set($token, $shop_user_info, 3600 * 24);
		// dump($token);
		// dump(Cache::get($token));
		$url = 'http://mjcd.gotomore.cn/?#/home/'.$token.'/'.urlencode($state);
		// dump($url);die;
		// echo $url;
		echo "<script>location.href='".$url."'</script>";
		// return redirect($url);
		// $this->redirect($url);
	}

	public function test(){
		dump($this->shop_user_id);
		dump($this->shop_user_info);
	}
}