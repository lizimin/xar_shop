<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 服务类型
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 17:40
namespace app\common\model;

use app\common\model\Model;

class ShopOrderServiceGroup extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_order_service_group';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'sg_id';

    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');
}