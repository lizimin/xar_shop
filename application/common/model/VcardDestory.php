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
class VcardDestory extends Model
{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard_destory';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';
    //所属卡信息
    public function Vcard($field = '*'){
        return $this->hasOne('Vcard', 'vc_id', 'vc_id')->field($field);
    }
    public function Customer($field = "*"){
        // dump($field);
        return $this->hasOne('Customer', 'customer_id', 'customer_id')->field('cname,cavatar,customer_id');
    }
    public function getImgList(){
        $result = [];
        if($this->img_list){
            $result = model('Attachment')->where(['aid'=>['in', $this->img_list]])->field('path, domain, aid')->select();
        }
        return $result;
    }
}