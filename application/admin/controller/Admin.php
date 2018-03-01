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
//2017/11/21 14:00
namespace app\admin\controller;

use app\common\controller\Common;

class Admin extends Common{

    private $model_menu;
    protected $shop_group_id, $shop_id, $super_edit_prive;

    protected function _initialize(){
        $this->model_menu = model('common/ShopMenu');
        $this->requestInfo();
        //定义网站地址
        if(config('home_page')){
            $this->site_url = config('home_page');
        }else{
            $this->site_url = request()->domain();
            config('home_page',$this->site_url);
        }
        define('SITE_URL', $this->site_url);
        //未登录跳转
        $_noaccess_url_arr = array('admin/index/login', 'admin/index/logout', 'admin/index/verify');
        if (!is_login() and !in_array($this->url, $_noaccess_url_arr)) {
            $this->redirect('/index.php?s=admin/index/login');
        }
        if (!in_array($this->url, $_noaccess_url_arr)){
            $except_url_arr = array('admin/index/index', 'admin/index/get_menu');
            $need_left_menu = array('admin/index/index');
            if(!is_administrator($this->uid)){
                if(!in_array($this->url, $except_url_arr)){
                    if(!$this->has_access() && $this->url != 'admin/index/no_access'){
                        $this->redirect("/index.php?s=admin/index/no_access");
                        exit;
                    }
                }
            }
            //获取request信息
            $this->requestInfo();
            //用户登录信息
            if(is_login()){
                $this->uid = is_login();
                $this->user_info = get_user_info();
                $this->shop_group_id = $this->user_info['shop_group_id'];
                $this->shop_id = $this->user_info['shop_id'];
            }else{

            }
            $this->assign('uid', $this->uid);
            $this->assign('user_info', $this->user_info);

            //权限判断
            $this->super_edit_prive = is_administrator($this->uid);
            if(in_array($this->url, $need_left_menu)){
                $this->assign('top_sidebar', $this->get_top_menu());
//                $this->assign('left_sidebar', $this->get_menu());
            }
            //权限判断
            $this->assign('meta_title', '');
            $this->assign('meta_keywords', config('web_site_keyword'));
            $this->assign('meta_description', config('web_site_description'));
            $this->assign('_page_name', strtolower(request()->module() . '_' . request()->controller() . '_' . request()->action()));
        }
    }

    public function get_top_menu(){
        //这里可以考虑用缓存
        $user_info = get_user_info();
        $is_super = is_administrator($this->uid);
        $map = array();
        $map['is_status'] = 1;
        $map['shop_id'] = $this->shop_id;
        $map['p_id'] = 0;
        if(!$is_super){
            $role = $user_info['roleid'];
            if(empty($role) || !$role){
                $map['p_id'] = -99999;
            }else{
                $model_access = model('common/ShopUserRoleAccess');
                $a_map = array();
                $a_map['role_id'] = array('in', $role);
                $access_in = $model_access->where($a_map)->column('shopmenu_id');
                if(empty($access_in) || !$access_in){
                    $map['p_id'] = -99999;
                }else{
                    $map['shopmenu_id'] = array('in', $access_in);
                }
            }
        }
        $str = '';
        $first_menus = $this->model_menu->where($map)->field('shopmenu_id,m_title,m_url')->order('m_order ASC')->select();
        $this->assign('first_first_menu', $first_menus[0]);
        foreach($first_menus as &$first_menu){
            $str .= '<li class="layui-nav-item layui-nav-itemed">';
            $map['p_id'] = $first_menu->shopmenu_id;
            $second_menus = $this->model_menu->where($map)->field('shopmenu_id,m_title,m_url')->order('m_order ASC')->count();
            if($second_menus){
                $str .= '<a class="" href="javascript:get_left_slider('.$first_menu->shopmenu_id.');">'.$first_menu->m_title.'</a>';
            }else{
                $str .= '<a class="" href="javascript:get_left_slider(-1);">'.$first_menu->m_title.'</a>';
            }
            $str .= '</li>';
        }
        return $str;
    }

    public function get_menu($pid=0){
        //这里可以考虑用缓存
        $user_info = get_user_info();
        $is_super = is_administrator($this->uid);
        $map = array();
        $map['is_status'] = 1;
        $map['shop_id'] = $this->shop_id;
        $map['p_id'] = $pid;
        if(!$is_super){
            $role = $user_info['roleid'];
            if(empty($role) || !$role){
                $map['p_id'] = -99999;
            }else{
                $model_access = model('common/ShopUserRoleAccess');
                $a_map = array();
                $a_map['role_id'] = array('in', $role);
                $access_in = $model_access->where($a_map)->column('shopmenu_id');
                if(empty($access_in) || !$access_in){
                    $map['p_id'] = -99999;
                }else{
                    $map['shopmenu_id'] = array('in', $access_in);
                }
            }
        }
        $str = '';
        $first_menus = $this->model_menu->where($map)->field('shopmenu_id,m_title,m_url')->order('m_order ASC')->select();
        foreach($first_menus as &$first_menu){
            $str .= '<li class="layui-nav-item layui-nav-itemed">';
            $str .= '<a class="" href="javascript:;">'.$first_menu->m_title.'</a>';
            $map['p_id'] = $first_menu->shopmenu_id;
            $second_menus = $this->model_menu->where($map)->field('shopmenu_id,m_title,m_url')->order('m_order ASC')->select();
            if($second_menus){
                $str .= '<dl class="layui-nav-child">';
                foreach($second_menus as &$second_menu){
                    $str .= '<dd class="layui-nav-item layui-nav-itemed">';
                    $str .= '<a href="javascript:add_tab('.$second_menu->shopmenu_id.', \''.$second_menu->m_title.'\', \''.$second_menu->m_url.'\');">'.$second_menu->m_title.'</a>';
//                    $str .= '<a canclick="true" m_id="'.$second_menu->shopmenu_id.'" m_title="'.$second_menu->m_title.'", m_url="'.$second_menu->m_url.'">'.$second_menu->m_title.'</a>';
                    $str .= '</dd>';
                }
                $str .= '</dl>';
            }
            $str .= '</li>';
        }
        echo $str;
    }

    public function has_access(){
        $map = array();
        $map['shop_id'] = $this->shop_id;
        $map['is_status'] = 1;
        $map['m_url'] = $this->url;
        $menu = model('common/ShopMenu')->where($map)->field('shopmenu_id')->count();
        if(!$menu){
            return false;
        }
        $map = array();
        $map['shopmenu_id'] = $menu->shopmenu_id;
        $role = get_user_info();
        $role = $role['roleid'];
        if(!empty($role)){
            $map['role_id'] = array('in', $role);
        }else{
            $map['role_id'] = -9999;
        }
        $has_access = model('common/ShopUserRoleAccess')->where($map)->count();
        return $has_access ? true : false;
    }
}