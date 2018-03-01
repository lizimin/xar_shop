<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Last Modified by: 柳天鹏
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 10:57:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： API接口

namespace app\api\controller;

use app\common\controller\ApiBase;

class Comment extends ApiBase
{
    public function addCommentNew(){
        $customer_id = $this->customer_id;
        $img_list = input('img_list', '');
        $comment = input('comment', '');
        $order_id = input('order_id', 0);
        $extra_id = input('extra_id', 0);
        $pro_id = input('pro_id', 51);
        $extra_type = input('extra_type', 'vcard');
        $save_arr = array(
            'shop_id' => $shop_id,
            'shop_user' => $shop_user,
            'pro_id' => $pro_id,
            'comment' => $comment,
            'customer_id' => $customer_id,
            'order_id' => $order_id,
            'extra_id' => $extra_id,
            'extra_type' => $extra_type,
            'img_list' => $img_list,
            'add_time' => time()
        );
        db('mall_comment')->insert($save_arr);
        return $this->sendResult($result, $errcode);
    }

    public function addComment(){
        $customer_id = $this->customer_id;
        $out_trade_no = input('out_trade_no', '');
        $extra_id = input('extra_id', '');
        $extra_type = input('extra_type', '');
        $img_list = input('img_list', '');
        $comment = input('comment', '');
        $result = $map = array();
        $errcode = 0;
        $customer_id = 6;
        if($out_trade_no && $extra_id && $extra_type){
            $order_info = model('MallOrder')->getOrder($out_trade_no);
            $order_id = $order_info['order_id'];
            if($order_id){
                if($customer_id == $order_info['customer_id']){
                    $map = array();
                    $map['order_id'] = $order_id;
                    $extra_data = [];
                    $shop_id = 0;
                    $pro_id = 0;
                    $shop_user = 0;
                    switch ($extra_type) {
                        case 'vcard':
                            $map['customer_id'] = $customer_id;
                            $map['extra_id'] = $extra_id;
                            $map['extra_type'] = $extra_type;
                            $extra_data = model('VcardDestory')->getDataByPk($extra_id);
                            $shop_id = $extra_data['shop_id'];
                            $shop_user = $extra_data['staff_id'];
                            break;
                        default:
                            # code...
                            break;
                    }
                    $is_comment = model('Comment')->where($map)->find();
                    if(!$is_comment){
                        $save_arr = array(
                            'shop_id' => $shop_id,
                            'shop_user' => $shop_user,
                            'pro_id' => $pro_id,
                            'comment' => $comment,
                            'customer_id' => $customer_id,
                            'order_id' => $order_id,
                            'extra_id' => $extra_id,
                            'extra_type' => $extra_type,
                            'img_list' => $img_list,
                            'add_time' => time()
                        );
                        db('mall_comment')->insert($save_arr);
                    }else{
                        $errcode = 14003; //不是该用户的订单
                    }
                }else{
                    $errcode = 14001; //不是该用户的订单
                }
            }else{
                $errcode = 14002; //订单不存在
            }
        }else{
            $errcode = 101;
        }
        return $this->sendResult($result, $errcode);
    }

	public function getComment(){
        $customer_id = $this->customer_id;
        $out_trade_no = input('out_trade_no', '');
        $extra_id = input('extra_id', '');
        $extra_type = input('extra_type', '');
		$result = $map = array();
        $errcode = 0;
        $customer_id = 6;
		if($out_trade_no && $extra_id && $extra_type){

            $order_info = model('MallOrder')->getOrder($out_trade_no);
            if($order_info['order_id']){
                if($customer_id == $order_info['customer_id']){
                    $map = array();
                    $map['order_id'] = $order_info['order_id'];
                    switch ($extra_type) {
                        case 'vcard':
                            $map['customer_id'] = $customer_id;
                            $map['extra_id'] = $extra_id;
                            $map['extra_type'] = $extra_type;
                            break;
                        default:
                            # code...
                            break;
                    }
                    $result = model('Comment')->where($map)->find();
                    if($result['img_list']){
                        $result['img_list'] = model('Attachment')->where(['aid'=>['in', $result['img_list']]])->field('aid, name, url, domain')->select();
                    }elseif($result){
                        $result['img_list'] = [];
                    }
                }else{
                    $errcode = 14001; //不是该用户的订单
                }
            }else{
                $errcode = 14002; //订单不存在
            }
		}else{
            $errcode = 101;
        }
		return $this->sendResult($result);
	}



	public function getCommentList(){
    	$result = array();
    	$shop_id = input('shop_id', 0);
    	$customer_id = input('customer_id', 0);
    	$pro_id = input('pro_id', 0);
    	$limit = input('limit', 10);
    	$min_id = input('min_id', 0);
    	$max_id = input('max_id', 0);
    	$map = $result = [];
    	$errcode = 0;
    	if($shop_id) $map['shop_id'] = $shop_id;
    	if($pro_id) $map['pro_id'] = $pro_id;
    	if($customer_id) $map['customer_id'] = $customer_id;
    	
    	if($min_id) $map['id'] = ['<', $min_id];
    	if($max_id) $map['id'] = ['>', $max_id];
        $map['status'] = 1;
    	$result['list'] = db('mall_comment')->where($map)->order('add_time desc')->limit($limit)->select();
    	foreach ($result['list'] as $key => $value) {
    		$result['list'][$key]['img_list'] = $value['img_list'] ? model('Attachment')->where(['aid'=>['in', $value['img_list']]])->field('aid, name, url, domain')->select() : [];
    		$result['list'][$key]['customer'] = db('shop_customer')->where(['customer_id'=>$value['customer_id']])->field('customer_id, cname, cavatar')->find();
            $result['list'][$key]['add_time'] = date('Y-m-d', $result['list'][$key]['add_time']);
    	}
        if(!$min_id && !$max_id){
            $average = db('mall_comment')->where($map)->field("sum(`range`) o_range, count(*) num")->find();
            if($average['o_range'] && $average['num']){
                $result['o_range'] = $average['o_range'];
                $result['num'] = $average['num'];
                $result['avg'] = (round($result['o_range'] / ($average['num'] * 5), 4) * 100) . '%' ;
            }
        }
    	return $this->sendResult($result, $errcode);
    }
}