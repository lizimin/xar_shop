<?php
// +----------------------------------------------------------------------
// | [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 柳天鹏
// +----------------------------------------------------------------------
// [ 订单表 ]
//2017/11/30 16:04
namespace app\common\model;

use app\common\model\Model;

class MallOrder extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_order';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'order_id';

    public function getOrder($out_trade_no = '', $field = '*', $show_pay_data = true){
    	$result = $map = array();
    	if($out_trade_no){
    		$map['out_trade_no'] = $out_trade_no;
    		$result = $this->where($map)->field($field)->find();
    		if($result && $show_pay_data){
    			$map['order_id'] = $result['order_id'];
    			if(strstr($result['pay_type'], 'wx')){
    				$result['pay_notice'] = db('mall_pay_wxnotice')->where($map)->find();
    			}
    			if(strstr($result['pay_type'], 'ali')){
    				$result['pay_notice'] = db('mall_pay_alinotice')->where($map)->find();
				}
    			$result['goods'] = db('mall_order_goods')->where($map)->select();
    		}
    	}
    	return $result;
    }
}