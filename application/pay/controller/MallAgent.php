<?php 
namespace app\pay\controller;
use app\common\controller\ApiBase;
/**
 * 推荐用户购买后、计算相应的
 */
class MallAgent extends ApiBase
{
    public $out_trade_no = '';
    public $referer = 0;
    public $order_info = null;
    public $orderRefCountAmount = 0; //当前分给用户的佣金总和
    //计算当前产品所给的总佣金
    public function getOrderRefAmount(){

        // $this->orderRefCountAmount = $this->order_info['order_amount'] * 0.05;
        $goodsList = model('MallOrderGoods')->getOrderGoods($this->order_info['order_id']);
        // dump($goodsList);
        if($goodsList){
            $db_sku_policy = db('mall_pro_sku_policy');
            foreach ($goodsList as $key => $value) {
                $map = [
                    'sku_id' => $value['sku_id'],
                    'customer_id' => $this->order_info['referer'],
                    'status' => 1,
                ];
                $policy = $db_sku_policy->where($map)->find(); //某个产品对某个人设置过专属的分成信息
                if(!$policy){
                    //默认分成信息
                    $map['customer_id'] = 0;
                    $policy = $db_sku_policy->where($map)->find();

                }
                if($policy){
                    if($policy['ptype'] == 1) $this->orderRefCountAmount += $policy['pvalue'];
                    if($policy['ptype'] == 0) $this->orderRefCountAmount += $value['pro_total_price'] * $policy['pscale'];
                }
            }
            //设置最高佣金阈值、超过百分之30，该订单不返佣金。
            if(($this->order_info['order_amount'] * 0.3) < $this->orderRefCountAmount) $this->orderRefCountAmount = 0;
            // dump($this->orderRefCountAmount);
            // dump($this->order_info['order_amount'] * 0.3);
        }

    }
    //检查当前订单有没有计算过佣金,避免同一订单重复发放。
    public function checkOrderCompute(){
        $result = false;
        $count = db('mall_agent_salary_record')->where(['out_trade_no'=>$this->out_trade_no])->count();
        if($count) $result = true;
        return $result;
    }

    //计算来源佣金
    public function computeRefAmount($out_trade_no = ''){
        if($out_trade_no){
            $this->out_trade_no = $out_trade_no;
            $this->order_info = model('MallOrder')->getOrder($out_trade_no);
            //当订单信息包含店铺ID时、不计算为个人推广订单。
            
            if($this->order_info && $this->order_info['referer'] && !$this->order_info['shop_id'] && !$this->checkOrderCompute()){
                $this->getOrderRefAmount();
            }
            // dump($this->orderRefCountAmount);
            //佣金总额大于0时、计算佣金。
            if($this->orderRefCountAmount){
                $referList = model('MallAgent')->getAgentList($this->order_info['referer']);
                // $this->orderRefCountAmount = 10;
                $residueAmount = $this->orderRefCountAmount;
                // dump($referList);
                for ($i=0; $i < count($referList); $i++) { 
                    if(count($referList) == $i){
                        $referList[$i]['amount'] = $residueAmount < $this->orderRefCountAmount * $referList[$i]['scale'] ? $residueAmount : $this->orderRefCountAmount;
                    }else{
                        $referList[$i]['amount'] = $this->orderRefCountAmount * $referList[$i]['scale'];
                        $residueAmount -= $referList[$i]['amount'];
                    }
                    $insert_arr = [
                        'customer_id' => $referList[$i]['customer_id'],
                        'amount' => $referList[$i]['amount'],
                        'create_time' => time(),
                        'scale' => $referList[$i]['scale'],
                        'from_customer_id' => $this->order_info['referer'],
                        'out_trade_no' => $out_trade_no,
                        'order_id' => $this->order_info['order_id']
                    ];
                    // dump($insert_arr);
                    db('mall_agent_salary_record')->insert($insert_arr);
                    controller('notice/AgentNotice')->notice($this->order_info, $referList[$i]['amount'], $referList[$i]['customer_id'], $referList[$i]['lavel'], $referList[$i]['scale']);
                }
                //佣金自动发放。
                controller('LuckyMoney')->send_agent_salary_lucky_money($this->order_info['order_id']);
            }
        }
    }

    public function test(){
        $this->computeRefAmount('C180123231341988805');
    }

}