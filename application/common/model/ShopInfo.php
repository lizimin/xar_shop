<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 店铺信息
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 17:40
namespace app\common\model;

use app\common\model\Model;

class ShopInfo extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_info';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'shop_id';

    public function getShopInfo($id, $field='*'){
        if(!$id){
            return array();
        }
        $map = array();
        $map['shop_id'] = $id;
        $info = $this->where($map)->field($field)->find();
        if(!$info){
            return array();
        }
        return $info->toArray();
    }
    public function getNearOneShop($lat = 0, $lng = 0){
        $result = db('shop_info')->field("*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($lat*PI()/180-shop_lat*PI()/180)/2),2)+COS($lat*PI()/180)*COS(shop_lat*PI()/180)*POW(SIN(($lng*PI()/180-shop_lng*PI()/180)/2),2)))*1000) AS juli")->where(['is_status'=>1])->order('juli asc')->find();
        $result['shop_num'] = db('shop_info')->count();
        return $result;
    }
}