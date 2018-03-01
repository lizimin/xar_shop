<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 16:26
namespace app\common\model;

use app\common\model\Model;

class ShopMenu extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_menu';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'shopmenu_id';

    // 保存自动完成列表
//    protected $auto = array('start_time','end_time');
    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
//    protected $update = array('update_time');
}