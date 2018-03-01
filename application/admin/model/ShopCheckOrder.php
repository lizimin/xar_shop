<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | @Copyright (c)
//+----------------------------------------------------------------------
// | @Author: lishaoenbh@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: caorui
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-12-02 10:52:50
// +----------------------------------------------------------------------
// | @Description 文件描述： 订单检查点记录表模型

namespace app\admin\model;

use app\admin\model\Model;

class ShopCheckOrder extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_check_order';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'chkk_orderid';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}