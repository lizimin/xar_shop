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
// | @Last Modified time: 2017-12-07 11:23:34 
// +----------------------------------------------------------------------
// | @Description 文件描述：配置模型

namespace app\common\model;

use app\common\model\Model;

class Comment extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_comment';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 定义时间戳字段名
    protected $createTime = 'add_time';

    /**
     * @param $extra_id
     * 获取洗车核销评论，目前只考虑评论一次
     */
    public function get_washcar_comment(&$log, $field='*'){
        if(!$log){
            //这里特殊处理一下，如果参数错误，返回空数据
            return array();
        }
        $map = array();
        $map['extra_type'] = 'vcard';
        $map['extra_id'] = $log['vlog_id'];
        $map['customer_id'] = $log['customer_id'];
        $comment = $this->where($map)->field($field)->order('add_time')->find();
        if(!$comment){
            return array();
        }
        return $comment->toArray();
    }
}

