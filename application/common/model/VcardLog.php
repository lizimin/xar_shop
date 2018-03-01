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
class VcardLog extends Model
{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard_log';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'vlog_id';

    public function add_log($vc_action_type = 0, $vc_id = 0, $vcard_no = '', $customer_id = 0, $to_customer_id = 0){
        $result = false;
        if($vc_action_type && $vc_id && $vcard_no && $customer_id){
            $save_arr = [
                'vc_action_type' => $vc_action_type,
                'vc_id' => $vc_id,
                'vcard_no' => $vcard_no,
                'customer_id' => $customer_id,
                'to_customer_id' => $to_customer_id,
                'create_time' => time()
            ];
            $result = $this->insert($save_arr);
        }
        return $result;
    }
}