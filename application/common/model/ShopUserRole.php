<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 店铺角色表
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 18:55
namespace app\common\model;

use app\common\model\Model;

class ShopUserRole extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_user_role';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'role_id';

    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');
}