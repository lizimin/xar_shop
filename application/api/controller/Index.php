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
// | @Description 文件描述： API接口默认

namespace app\api\controller;

//入口限定
if (!defined("IN_API")) { exit("Access Denied");}

use app\common\controller\ApiBase;
use app\api\org\Token;

class Index extends ApiBase
{
	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();
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

	public function getAccessToken(){
		//初始化
		$result = $data = [];
		$message = 'success';
		$errcode = 0;

		try {
			$token = new Token();
			$access_token = $token->tokenEncode($tokenStr=[]);
			
			$data = $token->getTokenData($access_token);
			$result['access_token'] = $access_token;
			$result['data'] = $data['data'];

        } catch (\Exception $e) {
			$message = $e->getMessage();
		}
		
		return $this->sendResult($result);
	}


	

}