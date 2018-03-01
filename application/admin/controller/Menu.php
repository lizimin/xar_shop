<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 菜单控制器
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 16:28
namespace app\admin\controller;

class Menu extends Admin{

    private $model_menu;
    protected function _initialize(){
        parent::_initialize();
        $this->model_menu = model('common/ShopMenu');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_id'] = $this->shop_id;
            $field = '*';
//            $menus = $this->model_menu->where($map)->order('m_order ASC')->field($field)->select();
            $menus = $this->get_all_menu();
            $menus = collection($menus)->toArray();
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = count($menus);
            $return_arr['data'] = $menus;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->menu_exist(array('m_url'=>$data['m_url']));
            $data['m_url'] = strtolower($data['m_url']);
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_menu->validate(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_menu->getError()));
            }
        }else{
            $p_id = input('p_id', 0);
            $info = array();
            $info['p_id'] = $p_id;
            $this->assign('menus', $this->get_all_menu());
            $this->assign('info', $info);
            $this->setMeta('添加菜单');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['shopmenu_id']);
            $data['m_url'] = strtolower($data['m_url']);
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_menu->validate(true)->save($data, array('shopmenu_id' => $data['shopmenu_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_menu->getError()));
            }
        }else{
            $info = array();
            /* 获取数据 */
            $info = $this->model_menu->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('menus', $this->get_all_menu());
            $this->assign('info', $info);
            $this->setMeta('编辑菜单');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('shopmenu_id' => $id))->delete();
        $result =  $this->model_menu->where(array('shopmenu_id' => $id))->setField('is_status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function menu_exist($map = array()){
        $map['shop_id'] = $this->shop_id;
        $map['is_status'] = 1;
        $info = $this->model_menu->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'菜单已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_menu->field('shopmenu_id,shop_id')->find($id);
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

    private function get_all_menu($pid=0, $level=0){
        $list = array();
        $map = array();
        $map['p_id'] = $pid;
        $map['is_status'] = array('egt', 0);
        $map['shop_id'] = $this->shop_id;
        $menus = $this->model_menu->where($map)->field('*')->order('m_order ASC')->select();
        foreach($menus as &$menu){
            $menu = $menu->toArray();
            $menu['m_title'] = str_repeat('&nbsp;', $level*5).'|--&nbsp;'.$menu['m_title'];
            $list[] = $menu;
            $c_map = array();
            $c_map['p_id'] = $menu['shopmenu_id'];
            $c_map['is_status'] = array('egt', 0);
            $c_map['shop_id'] = $this->shop_id;
            $child_menus = $this->model_menu->where($c_map)->count();
            if($child_menus){
                $list = array_merge($list, $this->get_all_menu($menu['shopmenu_id'], $level+1));
            }
        }
        return $list;
    }
}