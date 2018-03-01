<?php 
namespace app\pay\controller;
use Payment\Notify\PayNotifyInterface;
use Payment\Config;
/**
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class TestNotify
 * anthor helei
 */
class HandelNotice implements PayNotifyInterface
{
    public function notifyProcess(array $data)
    {
        $channel = $data['channel'];
        file_put_contents('pay.txt', json_encode($data), FILE_APPEND);
        $out_trade_no = $data['out_trade_no'];
        $orderInfo = model('MallOrder')->getOrder($out_trade_no);
        if($orderInfo && !$orderInfo['is_notice']){
            $data['order_id'] = $orderInfo['order_id'];
            $data['customer_id'] = $orderInfo['customer_id'];
            if ($channel === Config::ALI_CHARGE) {// 支付宝支付
                db('mall_pay_alinotice')->insert($data);
            } elseif ($channel === Config::WX_CHARGE) {// 微信支付
                db('mall_pay_wxnotice')->insert($data);
            } elseif ($channel === Config::CMB_CHARGE) {// 招商支付

            } elseif ($channel === Config::CMB_BIND) {// 招商签约

            } else {
                // 其它类型的通知
            }
            //更新通知状态。。。
            $update_arr = [
                'update_time' => time(),
                'is_notice'   => 1,
                'pay_status'  => 1
            ];
            db('mall_order')->where(['order_id'=>$orderInfo['order_id']])->update($update_arr);
            $this->goods_option($orderInfo);
            controller('notice/OrderNotice')->notice($out_trade_no);
            controller('pay/MallAgent')->computeRefAmount($out_trade_no);
        }
        // 执行业务逻辑，成功后返回true
        return true;
    }

    public function goods_option($orderInfo){
        if($orderInfo){
            $db_sku = db('mall_pro_sku');
            
            foreach ($orderInfo['goods'] as $key => $value) {
				$sku_data = $db_sku->where(['sku_id'=>$value['sku_id']])->find();
				// dump($sku_data);
                if($sku_data){
                    switch ($sku_data['fun_str']) {
                        case 'vcard':
							// $extra_data = json_decode($sku_data['extra_data'], true);
							// dump($extra_data);
                            for ($list_num =1; $list_num <= $value['pro_quantity']; $list_num++) { 
                                    // dump($list_num);
                                controller('api/Vcard')->userBuyVcode($sku_data['pro_id'], $value['sku_id'], $value['out_trade_no'], $list_num, $orderInfo['order_id'], $orderInfo['customer_id'], $sku_data['frequency']);
                                if($value['sku_id'] == 127){
                                    controller('api/Vcard')->userBuyVcode($sku_data['pro_id'], 160, $value['out_trade_no'], 160, $orderInfo['order_id'], $orderInfo['customer_id'], 1);
                                }
                            }


                            break;
                    }
                }
                
            }
        }
    }
} 
?>