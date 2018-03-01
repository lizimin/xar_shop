<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/23 14:34
namespace app\admin\controller;

class Customer extends Admin{

    private $model_customer;
    protected function _initialize(){
        parent::_initialize();
        $this->model_customer = model('common/ShopCustomer');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_group_id'] = $this->shop_group_id;
            $map['is_status'] = array('gt', 0);
            $field = '*';
            $count = $this->model_customer->where($map)->count();
            $customers = $this->model_customer->where($map)->order('create_time DESC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $customers = $customers ? $customers : array();
            $customers = collection($customers)->toArray();
            foreach($customers as &$customer){
                $customer['is_status'] = get_common_status($customer['is_status']);
                $customer['csex'] = get_sex($customer['csex']);
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $customers;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->customer_exist(array('ctel'=>$data['ctel']));
            $data['cbirth'] = strtotime($data['cbirth']);
            $data['shop_group_id'] = $this->shop_group_id;
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_customer->validate(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_customer->getError()));
            }
        }else{
            $info = array();
            $info['csex'] = 1;
            $this->assign('info', $info);
            $this->setMeta('添加客户');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['group_id']);
            $data['cbirth'] = strtotime($data['cbirth']);
            $data['shop_group_id'] = $this->shop_group_id;
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_customer->validate(true)->save($data, array('customer_id' => $data['customer_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_customer->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_customer->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('info', $info);
            $this->setMeta('编辑客户');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('group_id' => $id))->delete();
        $result =  $this->model_customer->where(array('customer_id' => $id))->setField('is_status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function customer_exist($map = array()){
        $map['shop_group_id'] = $this->shop_group_id;
        $map['is_status'] = 1;
        $info = $this->model_customer->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'客户(手机)已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_customer->field('shop_group_id,shop_id')->find($id);
        if ($info === false) {
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'信息不存在！'));
            }
            exit;
        }
        if($this->shop_group_id != $info->shop_group_id){
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'你只能管理您相关的信息！'));
            }
            exit;
        }
    }
}