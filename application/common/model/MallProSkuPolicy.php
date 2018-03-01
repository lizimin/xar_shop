<?php
namespace app\common\model;

use app\common\model\Model;

class MallProSkuPolicy extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_sku_policy';
    protected $pk = 'id';

    public function getUserPolicy($customer_id = 0, $sku_info = []){
    	$share_amount = 0;
    	$policy = null;
    	if($sku_info && $sku_info['sku_id'] && $sku_info['mall_price']){
    		$policy = $this->where(['sku_id' => $sku_info['sku_id'], 'customer_id' => 0])->find();
    		if($customer_id){
    			$policy_tmp = $this->where(['sku_id' => $sku_info['sku_id'], 'customer_id' => $customer_id])->find();
    			if($policy_tmp) $policy = $policy_tmp;
    		}

    		if($policy){
    			if($policy['ptype'] == 1) $share_amount = $policy['pvalue'];
    			if($policy['ptype'] == 0) $share_amount = round($sku_info['mall_price'] * $policy['pscale'], 2);
    		}
    		if($share_amount > $sku_info['mall_price'] * 0.30) $share_amount = 0;

    	}
    	return $share_amount;
    }

}