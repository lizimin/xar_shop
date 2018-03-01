<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 客户信息
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 17:40
namespace app\common\model;

use app\common\model\Model;

class ShopCustomer extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_customer';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'customer_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    
}