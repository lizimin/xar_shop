<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 洗车卡赠送模型
// +---------------------------------------------------------+
// | Author: 柳天鹏
// +———————————————————+
//2017/11/17 10:10
namespace app\common\model;
use app\common\model\Model;
class VcardGive extends Model
{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard_give';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';
    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');

    //所属卡信息
    public function Vcard($field = '*'){
        return $this->hasOne('Vcard', 'vc_id', 'vc_id')->field($field);
        // return model('Vcard')->getVcard($this->vc_id);
    }
    public function Customer($field = "*"){
        // dump($field);
        return $this->hasOne('Customer', 'customer_id', 'customer_id')->field('customer_id, cname, cavatar');
    }
    public function toCustomer(){
        return $this->hasOne('Customer', 'customer_id', 'to_customer_id')->field('customer_id, cname, cavatar');
    }


    public function getGiveCard($card_code = '', $field='*'){
        $result = [];
        $card_field = 'vc_id,sku_id,exp_time';
        if($card_code){
            $result = $this->where(['card_code'=>$card_code])->field($field)->find();
            // $result = $result->toArray();
            $model_card = model('common/MallProVcard');
            if($result){
                $result['given_customer'] = model('common/Customer')->getCustomerBaseInfo($result['customer_id'], 'cname,cavatar');
                $result['card_info'] = $model_card->queryCard(array('vc_id'=>$result['vc_id']), $card_field);
                $use_info = array(
                    'use_info' => array(
                        'count' => 1,
                        'used' => 0,
                        'cancel' => 0,
                        'can_use' => 1
                    )
                );
                $result['card_info'] = array_merge($result['card_info'], $use_info);
                $result['Vcard'] = $result->Vcard->getData();
            }
        }
        return $result;
    }
}