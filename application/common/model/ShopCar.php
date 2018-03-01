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
// | @Last Modified time: 2017-12-06 16:49:03 
// +----------------------------------------------------------------------
// | @Description 文件描述： 车辆信息模型

namespace app\common\model;

use app\common\model\Model;

class ShopCar extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_car';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'car_id';

    //自动写入时间戳
	protected $autoWriteTimestamp = true;
    
    //自动过滤掉不存在的字段
    protected $field = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //自动处理
    protected $auto   = ['shop_group_id','car_brand_str','car_model_str','car_regdate','car_issuedate'];
    //插入时状态处理
    protected $insert = ['status' => 1];


    //所属用户组
    public function carbrand(){
        return $this->belongsTo('CarBrand', 'car_brand','brand_id')->field('brand_id,brand_name,brand_logo');
    }

    //获取店铺对应的集团id
    protected function setShopGroupIdAttr($value,$data)
    {
        $shop_group_id = get_model_data($model='admin_shop_group_relation',$id=$data['shop_id'],$field='shop_group_id',$field_where='shop_id');

        return $shop_group_id;
    }

    //获取车辆品牌id对面名称
    protected function setCarBrandStrAttr($value,$data)
    {
        $car_brand_str = get_model_data('CarBrand', $data['car_brand'], 'brand_name', 'brand_id');
 
        return $car_brand_str;
    }

    //获取车辆品牌id对面名称
    protected function setCarModelStrAttr($value,$data)
    {
        $car_model_str = get_model_data('CarInfo', $data['car_model'], 'car_name', 'car_id');
 
        return $car_model_str;
    }

    //时间处理
    protected function setCarRegdateAttr($value)
    {
        return is_int($value) ? $value : strtotime($value);
    }

    //时间处理
    protected function setCarIssuedateAttr($value)
    {
        return is_int($value) ? $value : strtotime($value);
    }

    /**
    * 添加车辆信息
    *
    * @param array $post_data
    * @return void
    */
    public function shop_car_save($post_data=[],$where=[]){
        //初始化
        $return = array();

        if(!empty($post_data) && ($post_data['car_plateno'] || $post_data['car_vin'])){
            if($where){
                $result_data = $this->allowField(true)->save($post_data,$where);
            }else{
                $result_data = $this->allowField(true)->save($post_data);
            }
            if($result_data){
                $return = $this->car_id;
            }
        }

        return $return;
    }
    
}