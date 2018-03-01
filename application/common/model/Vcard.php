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
class Vcard extends Model
{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'vc_id';
    public $customer_id = 0;
    //所属店铺信息
    public function product(){
        return $this->hasOne('MallProInfo', 'pro_id', 'pro_id')->field('*');
    }
    public function giveVcard(){
        return $this->hasOne('VcardGive', 'vc_id', 'vc_id')->field('*');
    }

    public function destoryVcard(){
        return $this->hasOne('VcardDestory', 'vc_id', 'vc_id')->field('*');
    }

    public function getVcard($vc_id = 0){
    	$result = array();
    	if($vc_id){
    		$result = $this->where(['vc_id'=>$vc_id])->find();
    		$result['proinfo'] = $result->product->getData();
    	}
    	return $result;
    }
    public function Sku($vc_id = 0)
    {
        $result = array();
        if($vc_id){
            $result = model('Vcard')->alias('a')->join('cys_mall_pro_sku b', 'a.sku_id = b.sku_id')->join('cys_mall_pro_info c', 'a.pro_id = c.pro_id')->where(['a.vc_id'=>$vc_id])->field('a.pro_id, a.sku_id,b.sku_code,b.sku_name,b.extra_data,b.fun_str,b.detail_des,b.short_des,b.sku_use_rule,b.card_use_rule,a.is_status vcard_status, a.is_use, c.pro_name, c.pro_id')->find();
            $tmp = json_decode($result['extra_data'], true);
            $result['extra_data'] = $tmp ? $tmp : [];
        }
        return $result;
    }
}