<?php
namespace app\pay\controller;
use EasyWeChat\Foundation\Application;
use app\common\controller\ApiBase;
class LuckyMoney extends ApiBase
{
	private $lucky_money = null;
	private $mch_billno = '';
	public function __construct(){
		$app = new Application(config('wechat_config'));
		$this->luckyMoney = $app->lucky_money;
	}

	private function create_mch_billno(){
		$this->mch_billno = 'APIWXHB'.time().mt_rand(1000, 9999);
	}

	private function sendLuckyMoney($openid = '', $amount = 0, $total_num = 1, $send_name = '小矮人汽车', $act_name = '小矮人惠全城活动', $wishing = '恭喜发财、大吉大利', $remark = '洗车修车、就到小矮人'){
		$result = false;
		if($openid && $amount && $total_num){
			if($this->mch_billno == '') $this->create_mch_billno();
			$luckyMoneyData = [
			    'mch_billno'       => $this->mch_billno,
			    'send_name'        => $send_name,
			    're_openid'        => $openid,
			    'total_num'        => $total_num,  //固定为1，可不传
			    'total_amount'     => $amount,  //单位为分，不小于100
			    'wishing'          => $wishing,
			    'act_name'         => $act_name,
			    'remark'           => $remark,
			];
			$result = $this->luckyMoney->sendNormal($luckyMoneyData);
			if($res->result_code == 'SUCCESS'){
				$luckyMoneyData['hb_type'] = 'Normal';
				$luckyMoneyData['send_type'] = 'API';
				$luckyMoneyData['result_code'] = $res->result_code;
				$luckyMoneyData['create_time'] = time();
				$luckyMoneyData['send_time'] = date('Y-m-d H:i:s', time());
				db('wechat_lucky_money')->insert($luckyMoneyData);
			}
		}
		return $result;
	}
	//活动扫码发红包 1元红包
	public function activityLyckyMoney($openid = '', $ac_id = 0){
		$result = false;
		if($openid && $ac_id){
			$db_activity_userinfo = db('activity_userinfo');
			$lucky = $db_activity_userinfo->where(['ac_id' => $ac_id, 'send_lucky_money'=>0])->find();
			$is_send_lucky_money = $db_activity_userinfo->where(['openid'=>$openid])->find(); //当前这个人有没有领取过红包
			if($lucky && !$is_send_lucky_money){
				$this->create_mch_billno();
				$db_activity_userinfo->startTrans();
				$db_activity_userinfo->where(['ac_id'=>$ac_id])->update(['send_lucky_money'=>1, 'openid' => $openid, 'mch_billno' => $this->mch_billno]);
				$result = $this->sendLuckyMoney($openid, 100);
				if(!$result || $result->result_code != 'SUCCESS'){
          $db_activity_userinfo->rollBack();
        }
        $db_activity_userinfo->commit();
			}
		}
		return $result;
	}

  //佣金使用微信红包的方式发放。
  public function send_agent_salary_lucky_money($order_id = 0){
    if($order_id){
      $db_salary_record = db('mall_agent_salary_record');

      $salary = $db_salary_record->where(['order_id' => $order_id, 'status' => 1, 'is_send' => 0])->select();
      foreach ($salary as $key => $value) {
        # code...
        $db_salary_record->startTrans();
        //先改发放佣金的状态、修改失败的话回滚当前更新
        $partner_trade_no = time().mt_rand(1000, 9999);
        $db_salary_record->where(['id'=>$value['id']])->update(['partner_trade_no'=>$partner_trade_no, 'is_send' => 1, 'send_type'=>1]);
        $openid = model('CustomerAuth')->where(['customer_id'=>$value['customer_id'], 'auth_idf' => 'xarwx'])->value('openid');
        $send_res = $this->sendLuckyMoney($openid, $value['amount'] * 100, '小矮人商城订单佣金发放', 1, '小矮人汽车', '邀请好友迎大奖活动', '恭喜发财、大吉大利');
        // dump($send_res);
        if(!$send_res || $send_res->result_code != 'SUCCESS'){
          $db_salary_record->rollBack();
        }
        $db_salary_record->commit();
      }

    }
  }

	public function test(){
		// $this->activityLyckyMoney('oHLTVvp38dkIlSutQbltuztQg4Bs', 14);
		$this->send_agent_salary_lucky_money(488);
	}

	//微信支付查询现金红包领取情况
	public function getLuckyMoneyData(){
		$mch_billno = input('mch_billno', 'xy123456', 'string');
		if($mch_billno){
			$res = $this->luckyMoney->query($mch_billno);
			dump($res);
		}
	}
}