<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 洗车卡赠送模型
// +---------------------------------------------------------+
// | Author: 柳天鹏
// +———————————————————+
//2017/11/17 10:10

namespace app\api\controller;

use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;
class GiveVcard extends ApiBase
{
	protected function _initialize(){
        parent::_initialize();
        $this->m_VcardGive = model('VcardGive');
    }
    //将超过24小时的洗车卡未被用户领取的，自动退回。
	public function resetExpireGiveVcard(){
		$map = [];
		$map['status'] = 0;
		$map['expire_time'] = ['<', time()];
		$expire_vcard = $this->m_VcardGive->where($map)->select();
		dump($expire_vcard);
		foreach ($expire_vcard as $key => $value) {
            $this->_reserGiveVcard($value, $value['customer_id'], 2, 4);
		}
    }
    //传入customer_id和GiveVcard 。重置洗车卡
    //giveCard_status 3 为撤销退回， 2 为超时退回
    //log_status 5 撤销退回记录  4、 超时退回记录
    private function _reserGiveVcard($giveCard = [], $customer_id = 0, $giveCard_status = 3, $log_status = 5){
        $errcode = 12009;
        if($giveCard && $customer_id){
            $m_Vcard = model('Vcard');
            $m_VcardLog = model('VcardLog');
            $this->m_VcardGive->startTrans();
            $m_Vcard->startTrans();
            $m_VcardLog->startTrans();
            //开启事务
            $res1 = $this->m_VcardGive->where(['id'=>$giveCard['id']])->update(['to_customer_id'=>0, 'update_time'=>time(), 'status'=>$giveCard_status]);  //修改赠送记录表
            $res3 = $m_Vcard->where(['vc_id'=>$giveCard['vc_id']])->update(['is_status'=>1]); //修改卡记录表
            $res4 = $m_VcardLog->add_log($log_status, $giveCard['vc_id'], $giveCard['vcard_no'], $giveCard['customer_id'], $customer_id); //增加日志
            $errcode = 0;
            if($res1 === false || $res3 === false || $res4 ===false){
                $this->m_VcardGive->rollBack();
                $m_VcardLog->rollBack();
                $m_Vcard->rollBack();
                $errcode = 12009; //操作
            }
            $this->m_VcardGive->commit();
            $m_VcardLog->commit();
            $m_Vcard->commit();
        }
        return $errcode;
    }

    //撤销洗车卡
	public function resetGiveVcard(){
		$card_code = input('card_code');
		$result = [];
		$errcode = 0;
        $message = '';
		if($this->customer_id){
			
			$giveCard = $this->m_VcardGive->getGiveCard($card_code);
			// dump($giveCard);
			if($giveCard){
				if($giveCard['status'] == 0){
					if($giveCard['expire_time'] > time()){
						if($giveCard['customer_id'] == $this->customer_id){
                            //撤销退回
							$errcode = $this->_reserGiveVcard($giveCard, $this->customer_id, 3, 5);
						}else{
							$errcode = 12008; //没有权限操作该卡
						}
					}else{
						$errcode = 12005; //超过24小时  已自动退回
					}
				}elseif($giveCard['status'] == 1){
					$errcode = 12007; //洗车卡已被领取
				}else{
					$errcode = 12010; //已退回
				}
			}else{
				$errcode = 12004; //赠送的洗车卡不存在
			}
		}else{
			$errcode = 10001;
		}
		return $this->sendResult($result,$errcode);
	}
}