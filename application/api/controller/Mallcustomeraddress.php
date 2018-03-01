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
// | @Last Modified time: 2017-11-30 10:57:42
// +----------------------------------------------------------------------
// | @Description 文件描述： API接口

namespace app\api\controller;

use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;

class Mallcustomeraddress extends ApiBase{

    protected $model_customer, $model_address;

    protected function _initialize(){
        parent::_initialize();
        $this->model_customer = model('common/ShopCustomer');
        $this->model_address = model('common/MallCustomerAddress');
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取用户地址
     */
    public function getAddressList(){
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录！');
        }
        $customer = $this->model_customer->where(array('customer_id'=>$this->customer_id))->field('customer_id,is_status')->find();
        if(!$customer){
            return $this->sendResult(array(), -1, '用户不存在！');
        }
        if($customer->is_status <= 0){
            return $this->sendResult(array(), -1, '用户被禁用！');
        }
        $list = $this->model_address->get_all_address_by_cus($this->customer_id);
        return $this->sendResult($list);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 删除地址
     */
    public function delAddress(){
        $req_data = request()->param();
        $add_id = isset($req_data['id']) ? $req_data['id'] : 0;
        if(!$add_id){
            return $this->sendResult(array(), -1, '参数错误！');
        }
        $list = $this->model_address->del_address($add_id);
        return $this->sendResult($list);
    }

    /**
     * 设置某个地址为默认值
     */
    public function setDefault(){
        $req_data = request()->param();
        $add_id = isset($req_data['id']) ? $req_data['id'] : 0;
        if(!$add_id){
            return $this->sendResult(array(), -1, '参数错误！');
        }
        //查询是不是自己的

        $list = $this->model_address->set_default($add_id);
        return $this->sendResult($list);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 新增和编辑地址
     */
    public function editAddress(){
        $req_data = request()->param();
        $add_id = isset($req_data['add_id']) ? $req_data['add_id'] : 0;
        $req_data['customer_id'] = $this->customer_id;
        $result = false;
        if($add_id){
            $result = $this->model_address->updateAddress($req_data);
        }else{
            $result = $this->model_address->addNewAddress($req_data);
        }
        if($result){
            return $this->sendResult(array());
        }else{
            return $this->sendResult(array(), -1, '操作失败！');
        }
    }

    public function getAddress(){
        $req_data = request()->param();
        $add_id = isset($req_data['id']) ? $req_data['id'] : 0;
        $address = $this->model_address->getAddress($add_id);
        if(empty($address)){
            return $this->sendResult(array(), -1, '地址不存在！');
        }else{
            return $this->sendResult($address);
        }
    }

}