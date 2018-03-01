<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 店铺控制器
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/23 16:34
namespace app\admin\controller;

class Shopinfo extends Admin{

    private $model_info, $model_user;
    protected function _initialize(){
        parent::_initialize();
        $this->model_info = model('common/ShopInfo');
        $this->model_user = model('common/ShopUser');
    }

    public function edit(){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($this->shop_id);
            $result = $this->model_info->validate(true)->save($data, array('shop_id' => $this->shop_id));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_user->getError()));
            }
        }else{
            $this->is_your_infor($this->shop_id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_info->field('*')->find($this->shop_id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->get_users();
            $this->assign('info', $info);
            $this->setMeta('编辑店铺信息');
            return $this->fetch('edit');
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_info->field('shop_user_id,shop_id')->find($id);
        if ($info === false) {
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'信息不存在！'));
            }
            exit;
        }
        if($this->uid != $info->shop_user_id){
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'你只能管理您店铺的信息！'));
            }
            exit;
        }
    }

    private function get_users(){
        $model_group = model('common/ShopUser');
        $map['shop_id'] = $this->shop_id;
        $map['status'] = 1;
        $groups = $model_group->where($map)->field('shop_user_id,urealname')->order('urealname ASC')->select();
        $this->assign('users', $groups);
    }
}