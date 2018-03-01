<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 商品表
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 18:15
namespace app\common\model;

use app\common\model\Model;

class MallCustomerAddress extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_customer_address';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'add_id';

    public function get_all_address_by_cus($customer_id=-999, $field='*'){
        $list = $this->where(array('customer_id'=>$customer_id))->field($field)->order('is_default DESC, create_time DESC')->select();
        $list = $list ? collection($list)->toArray() : array();
        $list = $this->fomartList($list);
        return $list;
    }

    public function del_address($id){
        $current_address = $this->where(array('add_id' => $id, 'add_id' => $id, 'is_status'=>1))->field('add_id,customer_id')->find();
        $this->where(array('add_id'=>$id))->setField(array('is_status'=>-1,'is_default'=>0));
        if($current_address){
            $has_ = $this->where(array('customer_id' => $current_address->customer_id,'is_status'=>1))->field('add_id,customer_id')->select();
            $has_ = collection($has_)->toArray();
            if(count($has_) == 1){
                $this->where(array('add_id' => $has_[0]['add_id']))->setField('is_default', 1);
            }
        }
        $list = $this->where(array('customer_id' => $current_address->customer_id,'is_status'=>1))->select();
        $list = $list ? collection($list)->toArray() : array();
        $list = $this->fomartList($list);
        return $list;
    }

    public function set_default($id){
        $current_address = $this->where(array('add_id' => $id))->field('add_id,customer_id')->find();
        if($current_address){
            $map = array();
            $map['add_id'] = array('neq', $id);
            $map['customer_id'] = $current_address->customer_id;
            $this->where($map)->setField('is_default', 0);
            $this->where(array('add_id' => $id, 'is_status'=>1))->setField('is_default', 1);
        }
        $list = $this->where(array('customer_id' => $current_address->customer_id,'is_status'=>1))->select();
        $list = $list ? collection($list)->toArray() : array();
        $list = $this->fomartList($list);
        return $list;
    }

    public function addNewAddress($data){
        $temp_ = array();
        $temp_['customer_id'] = $data['customer_id'];
        $temp_['cust_name'] = $data['cust_name'];
        $temp_['cust_mobile'] = $data['cust_mobile'];
        $temp_['cust_country'] = $data['cust_country'];
        $temp_['cust_post'] = $data['cust_post'];
        $temp_['cust_province'] = $data['cust_province'];
        $temp_['cust_city'] = $data['cust_city'];
        $temp_['cust_district'] = $data['cust_district'];
        $temp_['cust_detail_add'] = $data['cust_detail_add'];
        $temp_['is_default'] = $data['is_default'] ? $data['is_default'] : 0;
        $temp_['is_default'] = $temp_['is_default'] ? 1 : 0;
        $temp_['is_status'] = 1;
        $result = $this->isUpdate(false)->save($temp_);
        if($result){
            if($temp_['is_default']){
                //如果当前这条被指定为默认地址，重置状态
                $map = array();
                $map['add_id'] = array('neq', $this->add_id);
                $this->where($map)->setField('is_default', 0);
            }
        }
        return $result;
    }

    public function updateAddress($data){
        $temp_ = array();
        $temp_['cust_name'] = $data['cust_name'];
        $temp_['cust_mobile'] = $data['cust_mobile'];
        $temp_['cust_country'] = $data['cust_country'];
        $temp_['cust_post'] = $data['cust_post'];
        $temp_['cust_province'] = $data['cust_province'];
        $temp_['cust_city'] = $data['cust_city'];
        $temp_['cust_district'] = $data['cust_district'];
        $temp_['cust_detail_add'] = $data['cust_detail_add'];
        $temp_['is_default'] = isset($data['is_default']) ? $data['is_default'] : 0;
        $temp_['is_default'] = $temp_['is_default'] ? 1 : 0;
        $result = $this->where(array('add_id'=>$data['add_id'], 'customer_id'=>$data['customer_id']))->update($temp_);
        if($result){
            if($temp_['is_default']){
                //如果当前这条被指定为默认地址，重置状态
                $map = array();
                $map['add_id'] = array('neq', $data['add_id']);
                $this->where($map)->setField('is_default', 0);
            }
        }
        return $result;
    }

    public function getAddress($id){
        $current_address = $this->where(array('add_id' => $id))->field('*')->find();
        $current_address = $current_address ? $current_address->toArray() : array();
        return $current_address;
    }

    private function fomartList($list){
        foreach($list as &$v){
            $v['address_str'] = get_model_data('MallArea', $v['cust_province'], 'name', 'code').' '.get_model_data('MallArea', $v['cust_city'], 'name', 'code').' '.get_model_data('MallArea', $v['cust_district'], 'name', 'code');
        }
        return $list;
    }
}