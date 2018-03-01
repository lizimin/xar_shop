<?php
// +----------------------------------------------------------------------
// | 彩龙平台 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 车辆检查点表 ]
//2018/1/31 14:15
namespace app\admin\model;

use app\admin\model\Model;

class ShopCheckPoint extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_check_point';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'chk_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function getInfoById($chk_id){
        if(!$chk_id){
            return array();
        }
        $check_point = $this->find($chk_id);
        $check_point = $check_point ? $check_point->toArray() : array();
        return $check_point;
    }
}