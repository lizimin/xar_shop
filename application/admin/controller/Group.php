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

class Group extends Admin{

    private $model_group;
    protected function _initialize(){
        parent::_initialize();
        $this->model_group = model('common/ShopUserGroup');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_group->where($map)->count();
            $groups = $this->model_group->where($map)->order('group_id ASC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $groups = $groups ? $groups : array();
            $groups = collection($groups)->toArray();
            foreach($groups as &$group){
                $group['is_status'] = get_common_status($group['is_status']);
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $groups;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->group_exist(array('group_name'=>$data['group_name']));
            $data['shop_id'] = $this->shop_id;
            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            $result = $this->model_group->validate(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_group->getError()));
            }
        }else{
            $info = array();
            $this->get_users();
            $this->assign('info', $info);
            $this->setMeta('添加组');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['group_id']);
            $data['shop_id'] = $this->shop_id;
            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            $result = $this->model_group->validate(true)->save($data, array('group_id' => $data['group_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_group->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_group->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->get_users();
            $this->assign('info', $info);
            $this->setMeta('编辑组');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('group_id' => $id))->delete();
        $result =  $this->model_group->where(array('group_id' => $id))->setField('status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function group_exist($map = array()){
        $map['shop_id'] = $this->shop_id;
        $map['status'] = 1;
        $info = $this->model_group->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'组已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_group->field('group_id,shop_id')->find($id);
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

    private function get_users(){
        $model_group = model('common/ShopUser');
        $map['shop_id'] = $this->shop_id;
        $map['status'] = 1;
        $groups = $model_group->where($map)->field('shop_user_id,urealname')->order('urealname ASC')->select();
        $this->assign('users', $groups);
    }
}