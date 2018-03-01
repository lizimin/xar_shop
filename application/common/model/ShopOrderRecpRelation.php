<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 接车单用户关系表
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 17:40
namespace app\common\model;

use app\common\model\Model;

class ShopOrderRecpRelation extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_order_recp_relation';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';
}