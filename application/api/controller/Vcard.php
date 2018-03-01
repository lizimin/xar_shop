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

class Vcard extends ApiBase
{
	//每次生成10000张卡入库
	public function createVcardCode($gen_user_id = 0){
	    $num = 100;
		$arr = $save_arr = array();
	   	for($i = 1; $i <= $num; $i++)
	   	{
	   		$vcode = $this->createOneVcode();
	     	$arr[] = $vcode;
	     	$save_arr[] = [
	     		'vcard_no' => $vcode,
	     		'create_time' => time(),
	     		'update_time' => time(),
	     		'gen_user_id' => $gen_user_id   //卡密生成渠道
	     	];
	   	}
	   	db('mall_pro_vcard')->insertAll($save_arr);
	}

	//获取赠送洗车卡的详情。
	public function getGiveVcardInfo(){
		$card_code = input('card_code');
		$result = [];
		$errcode = 0;
		$message = '';
		if($this->customer_id){
			if($card_code){
                $field = 'id,customer_id,to_customer_id,create_time,vc_id,card_code,expire_time,status';
				$result = model('VcardGive')->getGiveCard($card_code, $field);
				$result['is_me'] = $this->customer_id == $result['to_customer_id'] ? 1 : 0;
				if(!$result){
					$errcode = 12004; //赠送的洗车卡不存在
				}
			}else{
				$errcode = 1001; //参数错误
			}
		}else{
			$errcode = 10001;
		}
		return $this->sendResult($result,$errcode,$message);
	}
	//     Vcard/getMyGive
	//获取别人赠送、我赠送别人的洗车卡列表
	public function getMyGive()
	{
		$result = [];
		$errcode = 0;
		$message = '';
		$min_id = 0;
		$max_id = 0;
		$type = input('type', 0, 'intval'); //0为获取别人赠送我的洗车卡。 1位获取我赠送出去的洗车卡
		$status = input('status', -1, 'intval'); // 默认查看所有
		$page = input('page', 0, 'intval'); // 默认查看所有
		$list_no = input('list_no', '');   //该赠送卡的list_no
		if($this->customer_id){
			$m_VcardGive = model('VcardGive');
			$m_Vcard = model('Vcard');
			$map = [];
			if($type){
				$map['customer_id'] = $this->customer_id;
			}else{
				$map['to_customer_id'] = $this->customer_id;
			}
			if($status != -1) $map['status'] = $status;
			if($list_no) $map['list_no'] = $list_no;
			$list = $m_VcardGive
					->where($map)
					->limit(10)
					->order('create_time desc')
					->page($page,$this->pagesize)
					->select();
			$total = $m_VcardGive->where($where)->count();
			$result['page_data'] = [];
			$result['page_data']['total']        = $total;
			$result['page_data']['current_page'] = $page;
			//总页数
			$result['page_data']['last_page']    = (int) ceil($total / $this->pagesize);
			foreach ($list as $key => $value) {
				if($value->Customer){
					$value->Customer->getData();
				}else{
					$value->Customer = [];
				}
				$sku_data = $m_Vcard->Sku($value->vc_id);
				$list[$key]['sku'] = $sku_data;
				$list[$key]['is_use'] = $sku_data['is_use'];
			}
			$result['list'] = $list;
		}else{
			$errcode = 10001;
		}
		return $this->sendResult($result,$errcode,$message);
	}

	//获取我的某个订单的使用列表，及使用情况
	public function getMyVcard()
	{
		$result = [];
		$errcode = 0;
		$message = '';
		$out_trade_no = input('out_trade_no', '');
		if($this->customer_id){
			if($out_trade_no){
				$result = model('Vcard')->where(['out_trade_no'=>$out_trade_no, 'customer_id'=>$this->customer_id])->select();
				foreach ($result as $key => $value) {
					if($value['is_status'] == 2 || $value['is_status'] == 3) $value->giveVcard->getData();
					if($value['is_status'] == 1 && $value['is_use'] == 1) $value->destoryVcard->getData();
				}
			}else{
				$errcode = 1001;
			}
		}else{
			$errcode = 10001;
		}
		return $this->sendResult($result,$errcode,$message);
	}
	//领取洗车卡
	public function getGiveVcard(){
		$card_code = input('card_code');
		$result = [];
		$errcode = 0;
		$message = '';
		if($this->customer_id){
			list($result, $errcode) = $this->_getGiveVcard($this->customer_id, $card_code);
		}else{
			$errcode = 10001;
		}
		return $this->sendResult($result,$errcode,$message);
	}

	public function _getGiveVcard($customer_id = 0, $card_code = ''){
		$errcode = 0;
		$result = [];
		$m_VcardGive = model('VcardGive');
		$giveCard = $m_VcardGive->getGiveCard($card_code);
		// dump($giveCard);
		if($giveCard){
			if($giveCard['status'] == 0){
				if($giveCard['expire_time'] > time()){
					if($giveCard['customer_id'] != $customer_id){
						$m_VcardRelation = model('VcardRelation');
						$m_Vcard = model('Vcard');
						$m_VcardLog = model('VcardLog');
						$m_VcardGive->startTrans();
						$m_VcardRelation->startTrans();
						$m_Vcard->startTrans();
						$m_VcardLog->startTrans();
						//开启事务
						$res1 = $m_VcardGive->where(['id'=>$giveCard['id']])->update(['to_customer_id'=>$customer_id, 'update_time'=>time(), 'status'=>1]);  //修改赠送记录表

						$res2 = $m_VcardRelation->where(['vc_id'=>$giveCard['vc_id']])->update(['to_customer_id'=>$customer_id, 'update_time'=>time()]); //修改赠关联表
						$res3 = $m_Vcard->where(['vc_id'=>$giveCard['vc_id']])->update(['is_status'=>3]); //修改卡记录表
						$res4 = $m_VcardLog->add_log(3, $giveCard['vc_id'], $giveCard['Vcard']['vcard_no'], $giveCard['Vcard']['customer_id'], $customer_id); //增加日志
						if($res1 === false || $res2 === false || $res3 === false || $res4 ===false){
							$m_VcardGive->rollBack();
							$m_VcardRelation->rollBack();
							$m_VcardLog->rollBack();
							$m_Vcard->rollBack();
							$errcode = 12007; //领取失败
						}
						$m_VcardGive->commit();
						$m_VcardRelation->commit();
						$m_VcardLog->commit();
						$m_Vcard->commit();
					}else{
						$errcode = 12006; //自己不能领取自己的洗车卡
					}
				}else{
					$errcode = 12005; //超过24小时  已自动退回
				}
			}else{
				$errcode = 12007; //洗车卡已被领取
			}
		}else{
			$errcode = 12004; //赠送的洗车卡不存在
		}
		return [$result, $errcode];
	}


	public function createOneVcode($length = 16){
		$str = '';

		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = date('YmdHis');
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $str .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
		return $str;
	}

	public function userBuyVcode($pro_id = 0, $sku_id = 0, $out_trade_no = '', $list_num = 1, $pay_id = 0, $customer_id = 0, $num = 0){

		if($pro_id && $sku_id && $out_trade_no && $pay_id && $customer_id && $num){
			$db_mall_pro_vcard = db('mall_pro_vcard');
			$db_mall_pro_vcard_log = db('mall_pro_vcard_log');
			$db_mall_pro_vcard_rel = db('mall_pro_vcard_relation');
			$db_mall_pro_sku = db('mall_pro_sku');
			$exp_time = 0;
			$sku_info = $db_mall_pro_sku->where(['sku_id'=>$sku_id])->find();
			if($sku_info['exp_type'] == 1) $exp_time = $sku_info['exp_time'] ? $sku_info['exp_time'] : time() + 24 * 60 * 60 * 365;
			if($sku_info['exp_type'] == 2) $exp_time = time() + $sku_info['exp_time'];
			$save_arr = [
				'pro_id' 	   => $pro_id,
				'sku_id' 	   => $sku_id,
				'out_trade_no' => $out_trade_no,
				'list_no' 	   => $out_trade_no.'-'.$list_num,
				'pay_id'       => $pay_id,
				'customer_id'  => $customer_id,
				'begin_time'   => time(),
				'exp_time'     => $exp_time,
				'is_buy'       => 1
			];
			$save_all = [];
			for ($i=0; $i < $num; $i++) {
				unset($save_arr['vc_id']);
				$save_arr['vcard_no'] = $this->createOneVcode();
				$save_arr['create_time'] = time();
				$save_arr['update_time'] = time();
				$save_arr['gen_user_id'] = 0;  //购买时生成
				$db_mall_pro_vcard->insert($save_arr);
				$vc_id = $db_mall_pro_vcard->getLastInsID();
				$save_arr['vc_id'] = $vc_id;
				$save_all[] = $save_arr;

				//关联记录生成
				$rel_arr['vc_id'] = $vc_id;
				$rel_arr['founder_user_id'] = $rel_arr['customer_id'] = $rel_arr['to_customer_id'] = $customer_id;
				$rel_arr['create_time'] = time();
				$db_mall_pro_vcard_rel->insert($rel_arr);

			}
			//记录用户分配日志。
			if($save_all){
				$vc_list = [];
				foreach ($save_all as $key => $value) {
					$vc_list[] = [
						'vc_id' => $value['vc_id'],
						'vcard_no' => $value['vcard_no'],
						'vc_action_type' => 0, //购买时系统分配
						'customer_id' => $customer_id,
						'to_customer_id' => 0,
						'is_status' => 1,
						'create_time' => time(),
					];
				}
				$db_mall_pro_vcard_log->insertAll($vc_list);
			}
		}
	}
	//活动给 
	public function createYhq(){
		$customer_id = 589;
		$pro_id = 52;
		$list_num = 1;
		$pay_id = -1;
		$num = 1;
		$sku_id = 171;
		$out_trade_no = 'systemyhj0124'.$customer_id.'sku'.$sku_id;
		$this->userBuyVcode($pro_id, $sku_id, $out_trade_no, $list_num, $pay_id, $customer_id, $num);
		$sku_id = 172;
		$out_trade_no = 'systemyhj0124'.$customer_id.'sku'.$sku_id;
		$this->userBuyVcode($pro_id, $sku_id, $out_trade_no, $list_num, $pay_id, $customer_id, $num);
		$sku_id = 173;
		$out_trade_no = 'systemyhj0124'.$customer_id.'sku'.$sku_id;
		$this->userBuyVcode($pro_id, $sku_id, $out_trade_no, $list_num, $pay_id, $customer_id, $num);
	}

	public function giveXarYhq($to_customer_id = 23){
		$result = [];
		if(!$this->checkSendYhq($to_customer_id)){
			$this->createYhq();//创建三张优惠券、然后发放给当前这个人。
			$sku_id = 171;
			$customer_id = 589;
			$list_no = 'systemyhj0124'.$customer_id.'sku'.$sku_id.'-1';
			$res = controller('api/AllQrcode')->_giveVcard($customer_id, $list_no);
			$result[] = $this->_getGiveVcard($to_customer_id, $res['card_code']);
			$sku_id = 172;
			$list_no = 'systemyhj0124'.$customer_id.'sku'.$sku_id.'-1';
			$res = controller('api/AllQrcode')->_giveVcard($customer_id, $list_no);
			$result[] = $this->_getGiveVcard($to_customer_id, $res['card_code']);
			$sku_id = 173;
			$list_no = 'systemyhj0124'.$customer_id.'sku'.$sku_id.'-1';
			$res = controller('api/AllQrcode')->_giveVcard($customer_id, $list_no);
			$result[] = $this->_getGiveVcard($to_customer_id, $res['card_code']);
		}
		return $result;
	}

	public function checkSendYhq($to_customer_id = 0){
		$result = false;
		if($to_customer_id){
			$map = [];
			$map['customer_id'] = 589;
			$map['to_customer_id'] = $to_customer_id;
			$map['list_no'] = ['like', 'systemyhj0124589sku%'];
			$res = db('mall_pro_vcard_give')->where($map)->find();
			if($res) $result = true;
		}
		return $result;
	}

	public function test(){
		$res = $this->giveXarYhq(338);
		dump($res);
	}


}



