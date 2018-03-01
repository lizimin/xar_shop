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
// | @Description 文件描述： 接待表模型

namespace app\admin\model;

use app\admin\model\Model;

class ShopOrderRecp extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_order_recp';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'carorder_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //自动处理
    protected $auto = ['shop_group_id','order_deadline'];
    //插入时状态处理
    protected $insert = ['order_sn','job_sn','order_status' => 1];

    //所属用户组
    public function shopcheckorder(){
        return $this->hasMany('ShopCheckOrder', 'order_sn')->field('*');
    }

    private $pay_type_arr = array(
        0 => '现金',
        1 => '微信',
        2 => '支付宝',
    );

    private $pay_status_arr = array(
        0 => '未付款'
    );

    private $order_status_arr = array(
        -1 => '取消',
        1 => '接车',
        10 => '接待接车确认',
        20 => '客户确认',
        30 => '未派单',
        31 => '已派单',
        32 => '工单完成待确认',
        40 => '接待已确认工单完成',
        50 => '已提车',
        100 => '已完成',
    );

    private $order_debit = array(
        0 => '不需要',
        1 => '需要'
    );

    private $is_or_not = array(
        0 => '否',
        1 => '是'
    );

    /**
     * 列表页展示方法
     */
    public function getPageList(){
        $return_arr = $map = array();
        $post_data = request()->param();
        if(isset($post_data['kw']) && trim($post_data['kw']) != ''){
            $map['order_psn|order_sn|job_sn|car_plateno|car_vin|carorder_remarks|car_outside_remarks|car_inside_remarks|customer_name|customer_tel|order_insurance'] = array('like', '%'.$post_data['kw'].'%');
        }
        if(isset($post_data['pay_type'])  && trim($post_data['pay_type']) != ''){
            $map['pay_type'] =  $post_data['pay_type'];
        }
        if(isset($post_data['pay_status'])  && trim($post_data['pay_status']) != ''){
            $map['pay_status'] =  $post_data['pay_status'];
        }
        if(isset($post_data['order_status'])  && trim($post_data['order_status']) != ''){
            $map['order_status'] =  $post_data['order_status'];
        }
        if($post_data['start_time'] && $post_data['end_time']){
            $map['create_time'] = array('between', array(strtotime($post_data['start_time']), (strtotime($post_data['end_time']) + 86400)));
        }
        if($post_data['deadline_start_time'] && $post_data['deadline_end_time']){
            $map['order_deadline'] = array('between', array(strtotime($post_data['deadline_start_time']), (strtotime($post_data['deadline_end_time']) + 86400)));
        }
        $map['shop_id'] = $this->shop_id;
        $field = '*';
        $count = $this->where($map)->count();
        $list = $this->where($map)->order('create_time DESC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
        $list = $list ? collection($list)->toArray() : array();
        foreach($list as &$v){
//                $group['is_status'] = get_common_status($group['is_status']);
            $v['pay_type'] = $this->pay_type_arr[$v['pay_type']];
            $v['pay_status'] = ($v['pay_status'] == 0) ? $this->pay_status_arr[0] : '已付款';
            $v['order_status'] = $this->order_status_arr[$v['order_status']];
            $v['order_deadline'] = ($v['order_deadline'] == 0) ? '' : date('Y-m-d : H:i:s', $v['order_deadline']);
            $v['order_debit'] = $this->is_or_not[$v['order_debit']];
            $v['order_identity_customer'] = $this->is_or_not[$v['order_identity_customer']];
            $v['order_identity_car'] = $this->is_or_not[$v['order_identity_car']];
        }
        $return_arr['code'] = 0;
        $return_arr['msg'] = '';
        $return_arr['count'] = $count;
        $return_arr['data'] = $list;
        return $return_arr;
    }

    /**
     * @param $key 可以是id、job_sn、order_sn
     * 返回查看详情数据
     */
    public function showRecpDetail($key){
        //内、外检查点记录表
        $model_check_point = model('admin/ShopCheckPoint');
        $model_order_check = model('admin/ShopCheckOrder');
        //给客户看的派工单表
        $model_order_job = model('admin/ShopOrderJob');
        //给内部看的派工单
        $model_job = model('admin/ShopJob');
        //支付信息表
        $model_pay_log = model('admin/ShopOrderPayLog');

        $map = array();
        $map['carorder_id|order_sn|job_sn'] = $key;
        $recp = $this->where($map)->find();
        if(!$recp){
            return false;
        }
        $recp = $recp->toArray();
        //修改基础信息
        $recp['pay_type'] = $this->pay_type_arr[$recp['pay_type']];
        $recp['pay_status'] = ($recp['pay_status'] == 0) ? $this->pay_status_arr[0] : '已付款';
        $recp['order_status'] = $this->order_status_arr[$recp['order_status']];
        $recp['order_deadline'] = ($recp['order_deadline'] == 0) ? '' : date('Y-m-d : H:i:s', $recp['order_deadline']);
        $recp['order_debit'] = $this->is_or_not[$recp['order_debit']];
        $recp['order_identity_customer'] = $this->is_or_not[$recp['order_identity_customer']];
        $recp['order_identity_car'] = $this->is_or_not[$recp['order_identity_car']];
        //查询店铺
        $recp['shop'] = model('common/ShopInfo')->getShopInfo($recp['shop_id']);

        //查询内外检查点
        $cko_map = array();
        $cko_map['order_sn'] = $recp['order_sn'];
        $check_order = $model_order_check->where($cko_map)->select();
        $check_order = $check_order ? collection($check_order)->toArray() : array();
        //处理内外检查点
        $out_check = $inside_check = array();
        foreach($check_order as $v){
            $v['check_point'] = $model_check_point->getInfoById($v['chk_id']);
            $v['chkorder_point'] = json_decode($v['chkorder_point'], true);
            $v['chkorder_img'] = explode(',', $v['chkorder_img']);
            if($v['chk_type'] == 0){
                $inside_check[] = $v;
            }else if($v['chk_type'] == 1){
                $out_check[] = $v;
            }
        }

        //查询给客户看的派工单
        $oj_map = array();
        $oj_map['carorder_id'] = $recp['carorder_id'];
        $order_job = $model_order_job->where($oj_map)->select();
        $order_job = $order_job ? collection($order_job)->toArray() : array();
        //处理服务和材料
        $order_job_service = $order_job_material = array();
        foreach($order_job as $v){
            if($v['job_type'] == 0){
                $order_job_service[] = $v;
            }else if($v['job_type'] == 1){
                $order_job_material[] = $v;
            }
        }

        //查询内部派工单
        $j_map = array();
        $j_map['carorder_id'] = $recp['carorder_id'];
        $job = $model_job->where($j_map)->select();
        $job = $job ? collection($job)->toArray() : array();
        //处理服务和材料
        $job_service = $job_material = array();
        foreach($job as $v){
            if($v['job_type'] == 0){
                $job_service[] = $v;
            }else if($v['job_type'] == 1){
                $job_material[] = $v;
            }
        }

        //查询付款信息
        $pay_map = array();
        $pay_map['carorder_id'] = $recp['carorder_id'];
        $pay_log = $model_pay_log->where($pay_map)->select();

        $return_arr = array();
        $return_arr['recp'] = $recp;

        $return_arr['outside_check_order'] = $out_check;
        $return_arr['inside_check_order'] = $inside_check;

        $return_arr['order_job_service'] = $order_job_service;
        $return_arr['order_job_material'] = $order_job_material;

        $return_arr['job_service'] = $job_service;
        $return_arr['job_material'] = $job_material;
        $return_arr['pay_log'] = $pay_log;
        return $return_arr;
    }
}