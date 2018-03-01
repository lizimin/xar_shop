<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 角色控制器
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/22 20:14
namespace app\admin\controller;

class Role extends Admin{

    private $model_role, $model_menu;
    protected function _initialize(){
        parent::_initialize();
        $this->model_role = model('common/ShopUserRole');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_role->where($map)->count();
            $roles = $this->model_role->where($map)->order('role_id ASC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $roles = $roles ? $roles : array();
            $roles = collection($roles)->toArray();
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $roles;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->role_exist(array('role_name'=>$data['role_name']));
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_role->validate(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_role->getError()));
            }
        }else{
            $info = array();
            $this->assign('info', $info);
            $this->setMeta('添加角色');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['role_id']);
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_role->validate(true)->save($data, array('role_id' => $data['role_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_role->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_role->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('info', $info);
            $this->setMeta('编辑角色');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('shopmenu_id' => $id))->delete();
        $result =  $this->model_role->where(array('role_id' => $id))->setField('is_status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function set_access($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['role_id']);
            //删除以前的
            $model_access_rela = model('common/ShopUserRoleAccess');
            $model_access_rela->where(array('role_id'=>$data['role_id']))->delete();
            if(!isset($data['role_id'])){
                return json_encode(array('status'=> -888, 'msg'=>'权限为必选要素，请重新选取！'));
                exit;
            }
            $access = is_array($data['role_id']) ? array_unique(array_filter($data['accesss'])) : array_unique(array_filter(explode(',', $data['accesss'])));
            $save_list = array();
            foreach($access as &$v){
                $temp_ = array();
                $temp_['shopmenu_id'] = $v;
                $temp_['role_id'] = $data['role_id'];
                $save_list[] = $temp_;
            }
            $result = $model_access_rela->saveAll($save_list);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_role->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $this->model_menu = model('common/ShopMenu');
            $info = array();
            /* 获取数据 */
            $info = $this->model_role->field('role_id')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('info', $info);
            $this->assign('access', json_encode($this->get_role_access_tree($id)));
            $this->setMeta('设置权限');
            return $this->fetch();
        }
    }

    public function role_exist($map = array()){
        $map['shop_id'] = $this->shop_id;
        $map['is_status'] = 1;
        $info = $this->model_role->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'角色已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_role->field('role_id,shop_id')->find($id);
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

    private function get_role_access_tree($role_id){
        $now_access = model('common/ShopUserRoleAccess')->where(array('role_id'=>$role_id, 'is_status' => 1))->column('shopmenu_id');
        return $this->get_all_menu(0, 0 , $now_access);
    }

    private function get_all_menu($pid=0, $level=0, &$now_access=array()){
        $list = array();
        $menus = $this->model_menu->where(array('p_id'=>$pid,'is_status'=>1,'shop_id'=>$this->shop_id))->field('*')->order('m_order ASC')->select();
        foreach($menus as &$menu){
            $menu = $menu->toArray();
            $temp_arr = array();
            $temp_arr['name'] = $menu['m_title'];
            $temp_arr['spread'] = true;
            $temp_arr['target'] = '_self';
            $temp_arr['checkboxValue'] = $menu['shopmenu_id'];
            if(in_array($menu['shopmenu_id'], $now_access)){
                $temp_arr['checked'] = true;
            }else{
                $temp_arr['checked'] = false;
            }
            $child_menus = $this->model_menu->where(array('p_id'=>$menu['shopmenu_id'],'is_status'=>1,'shop_id'=>$this->shop_id))->count();
            if($child_menus){
                $temp_arr['target'] = '_self';
                $temp_arr['children'] =  $this->get_all_menu($menu['shopmenu_id'], $level+1, $now_access);
            }
            $list[] = $temp_arr;
        }
        return $list;
    }
}