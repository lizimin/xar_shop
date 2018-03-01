<?php
// +----------------------------------------------------------------------
// | [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 提车通知 ]
//2018/1/3 11:15
namespace app\notice\controller;

use app\api\controller\ShopOrderRecp;

class CarOrderDone extends BaseNotice{
    public $data = [];
    public $template_id = 'EFaeO3puwC5PIgULmKRYWHG8kr6IX6q48MXqSGs7rng';
    public $url = 'http://mall.gotomore.cn/index.html#/order_detail/';
    public $defaultColor = '#000';
    public function createData($orderInfo = array()){
        if($orderInfo){
            $this->data = [
                'first' => ['尊敬的'.$orderInfo['realname'].'您好！您的车辆目前服务已完成，请您尽快提车。'],
                'keyword1' => [$orderInfo['car_plateno']],
                'keyword2' => ['暂无'],
                'keyword3' => [date('Y年m月d日', time()).'后都可提车'],
                'keyword4' => ['接待'.$orderInfo['recp_user_info'].'已确认您的车辆服务情况'],
                'keyword5' => [$orderInfo['shop_info']],
                'remark' => ['小矮人集团感谢您的支持。'],
            ];
        }

    }
    //根据订单号进行通知。
    public function notice($order_sn = '', $confirm_notice=false){
        $orderInfo = array();
        //查询单子信息
        $map['order_sn'] = array('eq',$order_sn);
        $oder_recp_data =  model('common/ShopOrderRecp')->get_shop_order_recp_info($map,$field='carorder_id,order_sn,job_sn,order_status,customer_id,shop_id,shop_user_id,car_plateno');

        if($oder_recp_data){
            //如果当前单子没有完成，则返回错误，这里返回一个提示
            if(!$confirm_notice){
                if($oder_recp_data['order_status'] < ShopOrderRecp::$order_status['repaired_recpter_confirm']){
                    return array('code'=>10, 'message'=>'该单未完成，确定要通知吗');
                }
            }

            //车辆信息
            $orderInfo['car_plateno'] = $oder_recp_data['car_plateno'];

            //查询用户openid
            $customer_info = model('common/CustomerAuth')->getDataByCustomerId($oder_recp_data['customer_id'], 'xarwx');
            $orderInfo['realname'] = $customer_info['cname'];
            $orderInfo['openid'] = $customer_info['auth']['openid'];

            //查询店面信息
            $sho_info = model('common/ShopInfo')->getShopInfo($oder_recp_data['shop_id'],'shop_name,shop_tel');
            $orderInfo['shop_info'] = $sho_info['shop_name'].'（'.$sho_info['shop_tel'].'）';

            //查询接待信息
            $recp_user_info = model('common/ShopUser')->getUserInfoById($oder_recp_data['shop_user_id'], 'urealname,utel');
            $orderInfo['recp_user_info'] = $recp_user_info['urealname'].'（'.$recp_user_info['utel'].'）';

            //发送通知
            if($orderInfo['openid']){
                $openid = $orderInfo['openid'];
                $this->createData($orderInfo);
                //拼接url
                $this->url .= $oder_recp_data['order_sn'];
                $this->WechatNotice($openid, $this->template_id, $this->url, $this->data);
            }
        }else{
            return array('code'=>1, 'message'=>'所属接待订单信息不存在');
        }
    }
}