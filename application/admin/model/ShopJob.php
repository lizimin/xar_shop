<?php
// +----------------------------------------------------------------------
// | 彩龙平台 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 内部派工单表 ]
//2018/1/31 14:15
namespace app\admin\model;

use app\admin\model\Model;

class ShopJob extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_job';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'job_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    private $job_type_arr = array(
        0 => '服务',
        1=> '材料'
    );

    /**
     * 列表页展示方法
     */
    public function getPageList(){
        $return_arr = $map = array();
        $post_data = request()->param();
        if(isset($post_data['kw']) && trim($post_data['kw']) != ''){
            $map['job_sn|service_name|car_plateno'] = array('like', '%'.$post_data['kw'].'%');
        }
        if(isset($post_data['job_type'])  && trim($post_data['job_type']) != ''){
            $map['job_type'] =  $post_data['job_type'];
        }
        if($post_data['start_time'] && $post_data['end_time']){
            $map['create_time'] = array('between', array(strtotime($post_data['start_time']), (strtotime($post_data['end_time']) + 86400)));
        }
        $map['shop_id'] = $this->shop_id;
        $field = '*';
        $count = $this->where($map)->count();
        $list = $this->where($map)->order('create_time DESC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
        $list = $list ? collection($list)->toArray() : array();
        foreach($list as &$v){
            if($v['job_type'] == 0){
                $v['server_count'] = '';
            }
            $v['job_type'] = $this->job_type_arr[$v['job_type']];
            //查询店铺
            $v['shop'] = model('common/ShopInfo')->getShopInfo($v['shop_id']);
            $v['shop_name'] = $v['shop']['shop_name'];
        }
        $return_arr['code'] = 0;
        $return_arr['msg'] = '';
        $return_arr['count'] = $count;
        $return_arr['data'] = $list;
        return $return_arr;
    }
}