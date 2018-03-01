<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 用户表
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/17 10:10
namespace app\common\model;
use app\common\model\Model;
class VcardRelation extends Model
{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard_relation';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'rel_id';

    public function get_vcard_by_rel_id($rel_id = 0){
        $result = array();
        if($rel_id){
            $result = $this->where(['rel_id'=>$rel_id])->find();
            if($result){
                $result = $result->toArray();
                $result['vcard_info'] = db('mall_pro_vcard')->where(['vc_id'=>$result['vc_id']])->find();
                $result['vcard_info']['exp_time_str'] = $result['vcard_info']['exp_time'] ? date('Y-m-d: H:i:s', $result['vcard_info']['exp_time']) : '无限制';
                if($result['vcard_info']){
                    $result['sku'] = model('MallProSku')->getDataByPk($result['vcard_info']['sku_id']);
                    if($result['sku']['can_use_shop']){
                        $result['sku']['can_use_shop_list'] = db('shop_info')->where(['shop_id' => ['in', $result['sku']['can_use_shop']]])->field('shop_id, shop_name')->select();
                    }
                }
                if($result['vcard_info']) $result['order'] = model('MallOrder')->getOrder($result['vcard_info']['out_trade_no']);
                if($result['vcard_info']) $result['pro_info'] = db('mall_pro_info')->where(['pro_id'=>$result['vcard_info']['pro_id']])->find();

                // $result['log'] = db('mall_pro_vcard_log')->where(['vc_id'=>$result['vc_id']])->order('vlog_id desc')->select();
                $result['customer'] = model('Customer')->getCustomer($result['customer_id'], 'xarwx', 'cname,crealname,cavatar');
                $result['to_customer'] = model('Customer')->getCustomer($result['to_customer_id'], 'xarwx', 'cname,crealname,cavatar');
            }
        }
        return $result;
    }
}