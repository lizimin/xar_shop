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
// | @Description 文件描述： 订单支付日志模型

namespace app\common\model;

use app\common\model\Model;

class ShopOrderPayLog extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_order_pay_log';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'pay_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //自动处理
    protected $auto = [];
    //插入时状态处理
    protected $insert = [];

    /**
     * 获取订单支付日志列表
     *
     * @param array $where
     * @param integer $pagesize
     * @param string $order
     * @param string $field
     * @return void
     */
    public function get_shop_order_pay_log_list($where=[],$limit='',$order='pay_id desc',$field='*'){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->limit($limit)->order($order)->field($field)->select();
            if($data){
                $data = collection($data)->toArray();
                $return = $data;
            }
        }
        
        return $return;
    }
    
    /**
     * 获取支付日志信息
     *
     * @param array $where
     * @param string $field
     * @return void
     */
    public function get_shop_order_pay_log_info($where=array(),$field='*'){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->field($field)->find();
            $return     = $data->getData();
        }

        return $return;
    }
    
    /**
    * 添加订单支付信息
    *
    * @param array $post_data
    * @return void
    */
    public function shop_order_pay_log_save($post_data=[],$where=[]){
        //初始化
        $return = $map = array();

        if(!empty($post_data)){
            if($where){
                $result_data = $this->allowField(true)->save($post_data,$where);
            }else{
                $result_data = $this->allowField(true)->save($post_data);
            }
            if($result_data){
                $return = $this->pay_id;
            }
        }
        return $return;
    }

}