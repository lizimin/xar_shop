<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 库存入库表
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 18:15
namespace app\common\model;

use app\common\model\Model;

class StockIn extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_erp_stock_in';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'st_inid';

    // 保存自动完成列表
//    protected $auto = array('article_add_time','article_update_time');
    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');
}