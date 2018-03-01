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

use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;

class VcardDestory extends ApiBase
{
	public function getShopUserDestory(){
		// dump($this->xcx_client_info);
		$client_info = $this->xcx_client_info;
		$user = isset($client_info['user']) ? $client_info['user'] : [];
		$shop_user_id = isset($user['shop_user_id']) ? $user['shop_user_id'] : 0;
		$errcode = 0;
		$page = input('page', 0, 'intval');
		$result = array();
		if($shop_user_id){
			$m_VcardDestory = model('VcardDestory');
			$m_Vcard = model('Vcard');
			$map = ['staff_id' => $shop_user_id];
			$list = $m_VcardDestory->where($map)->page($page, $this->pagesize)->order('id desc')->select();
			$result['page_data'] = $this->getPageData($m_VcardDestory->where($map)->count(), $page);
			foreach ($list as $key => $value) {
				if($value->Vcard) $value->Vcard->getData();
				if($value->Customer) $value->Customer->getData();
				$list[$key]['img_list'] = $value->getImgList();
				$value->add_time = date('Y-m-d H:i:s', $value->add_time);
				$list[$key]['sku'] = $m_Vcard->Sku($value->vc_id);
			}
			$result['list'] = $list;
		}else{
			$errcode = 13004;
		}
		return $this->sendResult($result,$errcode);
	}
}