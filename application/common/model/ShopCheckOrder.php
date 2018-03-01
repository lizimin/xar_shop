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
// | @Description 文件描述： 订单接待检查项目模型

namespace app\common\model;

use app\common\model\Model;

class ShopCheckOrder extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_check_order';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'chkk_orderid';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //插入时状态处理
    protected $insert = ['status' => 1];
    
    /**
    * 添加接车单信息
    *
    * @param array $post_data
    * @return void
    */
    public function shop_check_order_saveall($post_data=[],$order_recp_data,$where=[]){
        //初始化
        $return = array();

        if(!empty($post_data) && $order_recp_data){
            
            $shop_check_point_order = array();

            foreach ($post_data as $key => &$value) {
                //基础数据
                $value['order_sn']      = $order_recp_data['order_sn'];
                $value['car_id']        = $order_recp_data['car_id'];
                $value['car_plateno']   = $order_recp_data['car_plateno'];
                $value['shop_id']       = $order_recp_data['shop_id'];
                $value['shop_user_id']  = $order_recp_data['shop_user_id'];

                $value['chkorder_point'] = $value['chk_name'];
                $value['chkorder_img'] = $value['imgSrc'];
                $value['chkorder_remarks'] = $value['chk_remarks'];
                if(is_array($value['detailOption']) && !empty($value['detailOption'])){
                    $value['chkorder_point'] = json_encode($value['detailOption']);
                }else{
                    $value['chkorder_point'] = json_encode(array());
                }

                //条件查询获取数据库中的值
                if($value['chk_id'] &&($value['car_id'] || $value['car_plateno'])){
                    if($value['chkk_orderid']){
                        $map['chkk_orderid']   = $value['chkk_orderid'];
                    }else{
                        $map['chk_id']   = $value['chk_id'];
                        $map['order_sn'] = $value['order_sn'];
                        $map['car_plateno']   = $value['car_plateno'];
                    }
                    $data = $this->where($map)->find();
                    if($data){
                        $data = $data->getData();
                        $value = array_merge($data,$value);
                    }
                }else{
                    continue;
                }

                $shop_check_point_order[] = $value;
            }
            
            $result_data = $this->allowField(true)->saveAll($shop_check_point_order);
            if($result_data){
                //遍历数据对象数组
                $car_checkid_arr = $car_checkid_data = array();
                foreach ($result_data as &$list){
                    $car_checkid_arr[]  = $list->getData('chkk_orderid');
                    $car_checkid_data[] = $list->getData();
                }

                $return = array(
                    'checkids' => $car_checkid_arr,
                    'data'     => $car_checkid_data,
                );
            }
        }
        return $return;
    }

}