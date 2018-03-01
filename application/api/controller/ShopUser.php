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
// | @Description 文件描述： 店铺接待人接口

namespace app\api\controller;

use think\Cache;
use app\common\controller\ApiBase;

class ShopUser extends ApiBase
{
	//定义参数
	protected $model_shop_user;
	protected $model_shop_user_auth;

	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();
		
		//模型
        $this->model_shop_user     = model('common/ShopUser');
	}
	
	/**
	 * 默认方法
	 *
	 * @return void
	 */
	public function index(){
		$result = array();
		
		return $this->sendResult($result);
	}

	
	/**
	 * 获取用户信息
	 *
	 * @param integer $openid
	 * @return void
	 */
	public function userInfo(){
		//初始化
		$return = $return_data = $data = $map = array();
		$message = 'success';
		$errcode = 0;

        $data = $this->get_userinfo_token($this->token);
		
		if($data){
            $return = $data;
		}else{
			$errcode = 1;
			$message = 'token不存在';
		}

		return $this->sendResult($return,$errcode,$message);
	}

	public function addShopUser(){
		$utel = input('utel', '');
		$urealname = input('urealname', '');
		$exp = input('exp', '');
		$skill = input('skill', '');
		$ucardno = input('ucardno', '');
		$shop_id = input('shop_id', 0);
		$iv = input('iv', '');
		$encryptedData = input('encryptedData', '');
		$result = [];
		$errcode = 0;
		$message = '';
		if($utel && $urealname && $shop_id){
			$db_shop_user = db('shop_user');
			$sessionKey = $this->xcx_client_info['session_key'];
			$miniProgram_c = controller('Miniprogram');
			$miniProgram = $miniProgram_c->miniProgram();
			$xcx_user_info = $miniProgram->encryptor->decryptData($sessionKey, $iv, $encryptedData);
			$wx_userinfo = $miniProgram_c->mini_data_to_pub_data($xcx_user_info);
			$shop_user_tmp = $db_shop_user->where(['unionid'=>$wx_userinfo['unionid']])->find();
			if(!$shop_user_tmp){
				$save_arr = [
					'nickname'   => $wx_userinfo['nickname'],
					'headimgurl' => $wx_userinfo['headimgurl'],
					'province'   => $wx_userinfo['province'],
					'country'    => $wx_userinfo['country'],
					'city'    	 => $wx_userinfo['city'],
					'unionid'    => $wx_userinfo['unionid'],
					'exp'        => $exp,
					'skill'      => $skill,
					'usex'       => $sex,
					'ucardno'    => $ucardno,
					'utel'       => $utel,
					'urealname'  => $urealname,
					'shop_id'    => $shop_id,
					'add_time'   => time(),
					'create_time'=> time(),
				];
				$db_shop_user->insert($save_arr);
				$shop_user_id = $db_shop_user->getLastInsID();
				$save_auth = [
					'shop_user_id' => $shop_user_id,
					'openid' => $wx_userinfo['openid'],
					'unionid' => $wx_userinfo['unionid'],
					'create_time' => time(),
					'auth_idf' => 'miniprogram',
					'auth_type' => 1,
				];
				db('shop_user_auth')->insert($save_auth);
			}else{
				$errcode = 13006;
			}
		}else{
			$errcode = 13003;   //参数不正确，员工加入失败
		}

		return $this->sendResult($result,$errcode,$message);
	}
	public function apiAddShopUser(){
		$utel = input('utel', '');
		$urealname = input('urealname', '');
		$ucardno = input('ucardno', '');
		$shop_id = input('shop_id', 0);
		$result = [];
		$errcode = 0;
		$message = '';
		if($utel && $urealname && $shop_id){
			$db_shop_user = db('shop_user');

			$shop_user_tmp = $db_shop_user->where(['unionid'=>$this->user_info['unionid']])->find();
			if(!$shop_user_tmp){

				$save_arr = [
					'nickname'   => $this->user_info['customer_info']['cname'],
					'headimgurl' => $this->user_info['customer_info']['cavatar'],
					'province'   => $this->user_info['customer_info']['cprovince'],
					'country'    => $this->user_info['customer_info']['ccountry'],
					'city'    	 => $this->user_info['customer_info']['ccity'],
					'unionid'    => $this->user_info['customer_info']['unionid'],
					'usex'       => $this->user_info['customer_info']['csex'],
					'ucardno'    => $ucardno,
					'utel'       => $utel,
					'urealname'  => $urealname,
					'shop_id'    => $shop_id,
					'add_time'   => time(),
					'create_time'=> time(),
				];
				$db_shop_user->insert($save_arr);
			}else{
				$errcode = 13006;  //已经加入
			}
		}else{
			$errcode = 13003;   //参数不正确，员工加入失败
		}

		return $this->sendResult($result,$errcode,$message);
	}

	public function test(){
		$map = ['unionid'=>'oV6-vt-BbRN_YVEeXgsitR9FmtQo'];
		$res = $this->model_shop_user->get_shop_user_info($map,$field=true,$extend=true);
		dump($res);
	}

	public function getShopUserList(){
		$result = [];
		$errcode = 0;
		$shop_id = input('shop_id', 0, 'intval');
		$map = ['shop_id' => $shop_id];
		$m_ShopUser = model('ShopUser');
		$result['list'] = $m_ShopUser->getListByMap($map, '*', 'shop_user_id desc', $this->page, $this->pagesize);
		$total = $m_ShopUser->where($map)->count();
		$result['last_page'] = $this->getPageData($total);
		return $this->sendResult($result,$errcode);
	}

}