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
// | @Last Modified time: 2017-12-01 10:28:38 
// +----------------------------------------------------------------------
// | @Description 文件描述：车牌识别模型

namespace app\common\model;

use app\common\model\Model;

class PlateOcr extends Model{
    //设置数据表（不含前缀)
    protected $name = 'plate_ocr';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

	//自动写入时间戳
	protected $autoWriteTimestamp = true;
	 
	// 定义时间戳字段名
	protected $createTime = 'create_time';
	protected $updateTime = 'update_time';
}