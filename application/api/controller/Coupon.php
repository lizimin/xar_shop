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
// | @Description 文件描述： 优惠券

namespace app\api\controller;

use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;

class Coupon extends ApiBase{

    protected function _initialize(){
        parent::_initialize();
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取用户优惠券
     */
    public function getCouponByUser(){
        $return_arr = array();
        $page_data = array();
        $page_data['total'] = 1;
        $page_data['current_page'] = 1;
        $page_data['last_page'] = 1;
        $return_arr['page_data'] = $page_data;
        $return_arr['list'] = array(

        );
        return $this->sendResult($return_arr);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取推荐优惠券列表
     */
    public function getRecommendCoupon(){
        $return_arr = array();
        $page_data = array();
        $page_data['total'] = 1;
        $page_data['current_page'] = 1;
        $page_data['last_page'] = 1;
        $return_arr['page_data'] = $page_data;
        $return_arr['list'] = array(
            array(
                'photo' => '',
                'conpon_name' => '',
            ),
        );
        return $this->sendResult($return_arr);
    }
}