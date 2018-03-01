<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com 
//+----------------------------------------------------------------------
// | @Author: lishaoenbh@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: lishaoen
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-12-02 10:52:50 
// +----------------------------------------------------------------------
// | @Description 文件描述： 订单接待检查项

namespace app\common\model;

use app\common\model\Model;

class ShopCheckPoint extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_check_point';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'chk_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
}