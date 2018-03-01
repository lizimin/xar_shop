<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 角色权限表
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 18:15
namespace app\common\model;

use app\common\model\Model;

class ShopUserRoleAccess extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_user_role_access';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'access_id';
}