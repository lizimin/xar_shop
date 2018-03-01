<?php
// +----------------------------------------------------------------------
// |  [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 通知客户进度 ]
//2018/1/3 11:10
namespace app\notice\controller;

use app\api\controller\ShopOrderRecp;

class CustomerProgress extends BaseNotice{
    public $data = [];
    public $template_id = 'qLDPg8aP1XcdEztzNqfOL8_jI8CqQ_QHcUU90v6AUGM';
    public $url = 'http://mall.gotomore.cn/index.html#/order_detail/';
    public $defaultColor = '#000';
    public $job_status = array(
        '1' => '等待工人接单',
        '20' => '进行中',
        '30' => '进行中',
        '40' => '进行中',
        '41' => '进行中',
        '42' => '已完成',
    );
    public function createData($progressInfo = array()){
        if($progressInfo){
            $this->data = [
                'first' => ['尊敬的'.$progressInfo['realname'].'您好！您的车辆目前服务进度如下：'.$progressInfo['progress']],
                'keyword1' => [$progressInfo['car_plateno']],
                'keyword2' => [$progressInfo['shop_info']],
                'keyword3' => [$progressInfo['recp_user_info']],
                'remark' => ['小矮人集团感谢您的支持。'],
            ];
        }
    }
    //根据订单号进行通知。
    public function notice($order_sn = ''){
        $progressInfo = array();
        //查询单子信息
        $map['order_sn'] = array('eq',$order_sn);
        $oder_recp_data =  model('common/ShopOrderRecp')->get_shop_order_recp_info($map,$field='carorder_id,order_sn,job_sn,car_plateno,order_status,customer_id,shop_id,shop_user_id');

        if($oder_recp_data){
            $job_sn = $oder_recp_data['job_sn'];
            $result['recp_info'] = $oder_recp_data;
            //获取sg_id组信息
            if($job_sn){
                //派工组的工作信息
                $job_list = array();
                $model_shop_job = model('common/ShopJob');
                $services   = $model_shop_job->where($where=['job_sn'=>$job_sn])->field('sg_id,service_name,status')->select();
                if($services){
                    $services = collection($services)->toArray();
                    foreach($services as $key=>&$val){
                        $job_list[] = $val['service_name'].'（'.$this->job_status[$val['status']].'）';
                    }
                }
                //组合信息
                $progressInfo['progress'] = implode('、', $job_list);
            }

            //车辆信息
            $progressInfo['car_plateno'] = $oder_recp_data['car_plateno'];

            //如果当前单子没有派工，则返回错误
            if(!$progressInfo['progress'] || $oder_recp_data['order_status'] < ShopOrderRecp::$order_status['need_work']){
                return array('code'=>1, 'message'=>'该单未派工，不可发送通知');
            }

            //查询用户openid
            $customer_info = model('common/CustomerAuth')->getDataByCustomerId($oder_recp_data['customer_id'], 'xarwx');
            $progressInfo['realname'] = $customer_info['cname'];
            $progressInfo['openid'] = $customer_info['auth']['openid'];

            //查询店面信息
            $sho_info = model('common/ShopInfo')->getShopInfo($oder_recp_data['shop_id'],'shop_name,shop_tel');
            $progressInfo['shop_info'] = $sho_info['shop_name'].'（'.$sho_info['shop_tel'].'）';

            //查询接待信息
            $recp_user_info = model('common/ShopUser')->getUserInfoById($oder_recp_data['shop_user_id'], 'urealname,utel');
            $progressInfo['recp_user_info'] = $recp_user_info['urealname'].'（'.$recp_user_info['utel'].'）';

            //发送通知
            if($progressInfo['openid']){
                $openid = $progressInfo['openid'];
                $this->createData($progressInfo);
                //拼接url
                $this->url .= $oder_recp_data['order_sn'];
                $this->WechatNotice($openid, $this->template_id, $this->url, $this->data);
            }
        }else{
            return array('code'=>1, 'message'=>'所属接待订单信息不存在');
        }

    }
}