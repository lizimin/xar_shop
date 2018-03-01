<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com 
//+----------------------------------------------------------------------
// | @Author: 1296969641@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: 柳天鹏
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 10:57:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： API接口

namespace app\notice\controller;

class Vcard extends BaseNotice
{
	public $data = [];
	public $template_id = 'dw9vijNGyTRKkdJ1Bsgv4ovZRdxHIgQtrZ33kW7LOmA';
	public $defaultColor = '#000';
	public function createData($customer = array(), $vcard = [], $shopInfo = [], $shopUser = []){
		if($customer){
			$this->data = [
	    		'first' => ['亲、您的【'.$vcard['proinfo']['pro_name'].'-'.$vcard['vc_id'].'】已消费成功。若有任何问题请与我们联系
'],
	    		'keyword1' => [$customer['customer_id']],
	    		'keyword2' => [$customer['cname']],
	    		'keyword3' => [date('Y-m-d H:i:s').'
消费门店：'.$shopInfo['shop_name'].'('.$shopUser['urealname'].')'],
	    		'remark' => ['
请对我们的服务进行评价
评价完成有机会获得现金红包哦。'],
	    	];
		}

	}
	//根据订单号进行通知。
	public function notice($vc_id = '', $customer = 0, $shop_id = 0, $staff_id = 0, $extra_id = 0){
		if($vc_id && $customer && $shop_id && $staff_id){
			$customer = model('CustomerAuth')->getDataByCustomerId($customer);
			$m_vcard = model('Vcard');
			$vcard = $m_vcard->getVcard($vc_id);
			$shopInfo = model('ShopInfo')->getDataByPk($shop_id);
			$shopUser = model('ShopUser')->getDataByPk($staff_id, 'urealname');
			$this->createData($customer, $vcard, $shopInfo, $shopUser);
			$pro_id = $vcard['pro_id'];
			$order_info = model('MallOrder')->getOrder($vcard['out_trade_no'], 'order_id', false);
			$this->url = $this->url.'comment/'.$vcard['out_trade_no'].'/vcard/'.$extra_id;
			$this->WechatNotice($customer['auth']['openid'], $this->template_id, $this->url, $this->data);
		}
	}
	public function test(){
		$this->notice(1, 6, 1, 13, 5);
	}
}