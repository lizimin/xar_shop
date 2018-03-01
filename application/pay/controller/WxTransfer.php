<?php
namespace app\pay\controller;
use EasyWeChat\Foundation\Application;
use app\common\controller\ApiBase;
class WxTransfer extends ApiBase
{
  private $merchant_pay = null;
	public function __construct(){
    $app = new Application(config('wechat_config'));
    $this->merchant_pay = $app->merchant_pay;
  }
  //
  private function toBalance($openid = '', $amount = 0, $desc = '', $partner_trade_no = ''){
    $result = false;
    if( $openid && $amount && $desc){
      $result = $this->merchant_pay->send([
        'partner_trade_no' => $partner_trade_no ? $partner_trade_no : time().mt_rand(1000, 9999), // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
        'openid' => $openid,
        'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
        'amount' => $amount, // 企业付款金额，单位为分
        'desc' => $desc, // 企业付款操作说明信息。必填
        'spbill_create_ip' => request()->ip()
      ]);
    }
    return $result;
  }
  //佣金使用企业付款方式付款。
  public function send_agent_salary($order_id = 0){
    if($order_id){
      $db_salary_record = db('mall_agent_salary_record');

      $salary = $db_salary_record->where(['order_id' => $order_id, 'status' => 1, 'is_send' => 0])->select();
      foreach ($salary as $key => $value) {
        # code...
        $db_salary_record->startTrans();
        //先改发放佣金的状态、修改失败的话回滚当前更新
        $partner_trade_no = time().mt_rand(1000, 9999);
        $db_salary_record->where(['id'=>$value['id']])->update(['partner_trade_no'=>$partner_trade_no, 'is_send' => 1]);
        $openid = model('CustomerAuth')->where(['customer_id'=>$value['customer_id'], 'auth_idf' => 'xarwx'])->value('openid');
        $send_res = $this->toBalance($openid, $value['amount'] * 100, '小矮人商城订单佣金发放', $partner_trade_no);
        if(!$send_res || $send_res->result_code != 'SUCCESS'){
          $db_salary_record->rollBack();
        }
        $db_salary_record->commit();
      }

    }
  }
}