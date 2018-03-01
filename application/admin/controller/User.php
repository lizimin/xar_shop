<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 用户信息
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/23 15:02

namespace app\admin\controller;

class User extends Admin{

    private $model_user;
    protected function _initialize(){
        parent::_initialize();
        $this->model_user = model('common/ShopUser');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_user->where($map)->count();
            $users = $this->model_user->where($map)->order('shop_user_id ASC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $users = $users ? $users : array();
            $users = collection($users)->toArray();
            foreach($users as &$user){
                $user['status'] = get_common_status($user['status']);
                $user['usex'] = get_sex($user['usex']);
                $user['group_id'] = ($user['group_id'] != 0) ? get_model_data('ShopUserGroup', $user['group_id'], 'group_name', 'group_id') : '无';
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $users;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->user_exist(array('urealname'=>$data['urealname'], 'utel'=>$data['utel']));
            $roles = $data['roles'];
            unset($data['roles']);
            $data['shop_id'] = $this->shop_id;
            $data['shop_id'] = $this->shop_id;
            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            $result = $this->model_user->validate(true)->save($data);
            if ($result !== false) {
                if($roles){
                    $roles = is_array($roles) ? array_unique(array_filter($roles)) : array_unique(array_filter(explode(',', $roles)));
                    $roles_arr = array();
                    foreach($roles as &$role){
                        $temp = array();
                        $temp['role_id'] = $role;
                        $temp['shop_user_id'] = $this->model_user->shop_user_id;
                        $temp['status'] = 1;
                        $roles_arr[] = $temp;
                    }
                    $result = model('common/ShopUserRoleRelation')->saveAll($roles_arr);
                    if(!$result){
                        return json_encode(array('status'=> -888, 'msg'=>'绑定角色错误！'));
                    }
                }
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_user->getError()));
            }
        }else{
            $info = array();
            $this->get_groups();
            $this->get_rolse();
            $this->assign('info', $info);
            $this->setMeta('添加用户');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['shop_user_id']);
            //检查手机号码是否重复
            $map = array();
            $map['utel'] = $data['utel'];
            $map['shop_user_id'] = array('neq', $data['shop_user_id']);
            $tel_exist = $this->model_user->where($map)->count();
            if($tel_exist){
                return json_encode(array('status'=> -888, 'msg'=>'电话已被别人使用，请重新输入！'));
            }
            $data['shop_id'] = $this->shop_id;
            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            $roles = $data['roles'];
            unset($data['roles']);
            if($roles){
                $model_rela = model('common/ShopUserRoleRelation');
                $model_rela->where(array('shop_user_id'=>$data['shop_user_id']))->delete();
                $roles = is_array($roles) ? array_unique(array_filter($roles)) : array_unique(array_filter(explode(',', $roles)));
                $roles_arr = array();
                foreach($roles as &$role){
                    $temp = array();
                    $temp['role_id'] = $role;
                    $temp['shop_user_id'] = $data['shop_user_id'];
                    $temp['status'] = 1;
                    $roles_arr[] = $temp;
                }
                $result = $model_rela->saveAll($roles_arr);
                if(!$result){
                    return json_encode(array('status'=> -888, 'msg'=>'绑定角色错误！'));
                }
            }
            $result = $this->model_user->validate(true)->save($data, array('shop_user_id' => $data['shop_user_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_user->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_user->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $model_rela = model('common/ShopUserRoleRelation');
            $info['roles'] = $model_rela->where(array('shop_user_id'=>$id))->column('role_id');
            if(!empty($info['roles'])){
                $map = array();
                $map['role_id'] = array('in', $info['roles']);
                $role_arr = model('common/ShopUserRole')->where($map)->field('role_id,role_name')->select();
                $this->assign('role_arr', $role_arr);
            }
            $this->get_groups();
            $this->get_rolse();
            $info = $info->toArray();
            $this->assign('info', $info);
            $this->setMeta('编辑用户');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('group_id' => $id))->delete();
        $result =  $this->model_user->where(array('shop_user_id' => $id))->setField('status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function set_password($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['shop_user_id']);
            if($data['upassword'] != $data['confirm_upassword']){
                return json_encode(array('status'=> -888, 'msg'=>'密码输入不一致，请重新输入！'));
            }
            unset($data['confirm_upassword']);
            $result = $this->model_user->isUpdate(true)->save($data, array('shop_user_id'=>$data['shop_user_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_role->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_user->field('shop_user_id')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('info', $info);
            $this->setMeta('设置密码');
            return $this->fetch();
        }
    }

    public function user_exist($map = array()){
        $map['shop_id'] = $this->shop_id;
        $map['status'] = 1;
        $info = $this->model_user->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'用户已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_user->field('shop_user_id,shop_id')->find($id);
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

    private function get_groups(){
        $model_group = model('common/ShopUserGroup');
        $map['shop_id'] = $this->shop_id;
        $map['status'] = 1;
        $groups = $model_group->where($map)->field('group_id,group_name')->order('group_name ASC')->select();
        $this->assign('groups', $groups);
    }

    public function get_rolse(){
        $model_group = model('common/ShopUserRole');
        $map['shop_id'] = $this->shop_id;
        $map['is_status'] = 1;
        $roles = $model_group->where($map)->field('role_id,role_name')->order('role_name ASC')->select();
        $this->assign('roles', $roles);
    }
}