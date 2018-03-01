<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 接车单
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/23 14:34
namespace app\admin\controller;

class Orderrecp extends Admin{

    private $model_recp;
    protected function _initialize(){
        parent::_initialize();
        $this->model_recp = model('admin/ShopOrderRecp');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            echo json_encode($this->model_recp->getPageList());
        }
    }

    public function show_detail($id = 0){
        if(!request()->isGet()){
            exit;
        }
        $detail_data = $this->model_recp->showRecpDetail($id);
//        dump($detail_data);
        $this->assign('recp', $detail_data['recp']);
        $this->assign('outside_check_order', $detail_data['outside_check_order']);
        $this->assign('inside_check_order', $detail_data['inside_check_order']);
        $this->assign('order_job_service', $detail_data['order_job_service']);
        $this->assign('order_job_material', $detail_data['order_job_material']);
        $this->assign('job_service', $detail_data['job_service']);
        $this->assign('job_material', $detail_data['job_material']);
        $this->assign('pay_log', $detail_data['pay_log']);
        return $this->fetch();
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $data['shop_id'] = $this->shop_id;
//            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            $result = $this->model_recp->validate(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_recp->getError()));
            }
        }else{
            $info = array();
            $this->get_users();
            $this->assign('info', $info);
            $this->setMeta('添加接车单');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['carorder_id']);
            $data['shop_id'] = $this->shop_id;
//            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            $result = $this->model_recp->validate(true)->save($data, array('carorder_id' => $data['carorder_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_recp->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_recp->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->get_users();
            $this->assign('info', $info);
            $this->setMeta('编辑接车单');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('group_id' => $id))->delete();
        $result =  $this->model_recp->where(array('carorder_id' => $id))->setField('order_status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_recp->field('carorder_id,shop_id')->find($id);
        if ($info === false) {
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'信息不存在！'));
            }
            exit;
        }
        if($this->shop_id != $info->shop_id){
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'你只能管理您店铺的信息！'));
            }
            exit;
        }
    }
}