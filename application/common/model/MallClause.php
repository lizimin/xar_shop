<?php
// +----------------------------------------------------------------------
// | [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 服务条款 ]
//2017/12/19 16:10
namespace app\common\model;
use app\common\model\Model;
class MallClause extends Model{
    protected $name = 'mall_clause';
    protected $insert = array('create_time');
    protected $update = array('update_time');

    public function getClauseByIds($ids, $field='*'){
        if(!$ids){
            return array();
        }
        if(!is_array($ids)){
            $ids = explode(',', $ids);
        }
        if(empty($ids)){
            return array();
        }
        $map = array();
        $map['is_status'] = 1;
        $map['cla_id'] = array('in', $ids);
        $list = $this->where($map)->field($field)->order('cla_id ASC')->select();
        $list = $list ? collection($list)->toArray() : $list;
        return $list;
    }
}