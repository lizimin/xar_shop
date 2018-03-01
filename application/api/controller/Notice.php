<?php
// +----------------------------------------------------------------------
// | 彩龙平台 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 接口部分通知入口 ]
//2018/1/3 11:19
namespace app\api\controller;

use app\common\controller\ApiBase;

class Notice extends ApiBase{

    protected function _initialize(){
        parent::_initialize();
    }

    /**
     * @param $order_sn
     * 通知客户当前车辆服务进度
     */
    public function noticeCustomerProgress($order_sn=''){
        $return = $result = array();
        $message = 'success';
        $errcode = 0;
        $order_sn = input('order_sn',$order_sn,'string');
        $order_sn = trim($order_sn);
        if($order_sn){
            $return = controller('notice/CustomerProgress')->notice($order_sn);
            if($return['code'] != 0){
                $message = $return['message'];
                $errcode = $return['code'];
            }
        }else{
            $errcode = 1;
            $message = '请求操作不合法';
        }

        return $this->sendResult($result,$errcode,$message);
    }

    /**
     * @param $order_sn
     * 通知客户提车
     */
    public function noticeCarDone($order_sn='',$confirm_notice=0){
        $return = $result = array();
        $message = 'success';
        $errcode = 0;
        $order_sn = input('order_sn',$order_sn,'string');
        $order_sn = trim($order_sn);
        $confirm_notice = input('confirm_notice',$confirm_notice);
        if($order_sn){
            $return = controller('notice/CarOrderDone')->notice($order_sn, $confirm_notice);
            if($return['code'] != 0){
                $message = $return['message'];
                $errcode = $return['code'];
            }
        }else{
            $errcode = 1;
            $message = '请求操作不合法';
        }

        return $this->sendResult($result,$errcode,$message);
    }
}