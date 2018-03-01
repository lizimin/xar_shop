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

class MallAgent extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_agent';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    public function getAgentList($customer_id = 0){
        $referList = [];
        $scaleCount = 0;
        $tmp_customer_id = $customer_id;
        while ($customer_id) {
            $referer_user = $this->where(['customer_id'=>$customer_id])->find();
            $customer_id = $referer_user['p_customer_id'];
            if($referer_user) $referList[] = $referer_user->toArray();
        }
        for ($i = count($referList) - 1; $i > 0; $i--) { 
            $count_scale = $referList[$i]['scale'];
            $tmp = round($referList[$i-1]['scale'] * $count_scale, 2);
            $referList[$i-1]['scale'] = $tmp;
            $referList[$i]['scale'] = $count_scale - $tmp;
        }
        if(!$referList) {
            $referList[] = [
                'customer_id' => $tmp_customer_id,
                'scale' => 1,
            ];
        }
        foreach ($referList as $key => $value) {
            $scaleCount += $value['scale'];
        }
        // $referList[count($referList) - 1]['scale'] = 1 - $scaleCount;
        if($scaleCount > 1) $referList = [];
        return $referList;
    }
}