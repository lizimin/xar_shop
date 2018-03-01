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
//2017/11/21 14:06
namespace app\admin\controller;

class Index extends Admin{

    protected function _initialize(){
        parent::_initialize();
    }

    public function index(){
        return $this->fetch();
    }

    public function no_access(){
        return $this->fetch();
    }

    public function count_paser(){
        $model_recp = model('common/ShopOrderRecp');
        $map = array();
        $map['order_status'] = array('gt', 0);
        //接车单总数量
        $total_count = $model_recp->where($map)->count();
        //接车单待付款数量
        $map['pay_status'] = 0;
        $need_pay_count = $model_recp->where($map)->count();
        unset($map['pay_status']);
        //已收款
        $map['pay_status'] = array('neq', 0);
        $recive_money = $model_recp->where($map)->sum('all_price');
        //待收款
        $map['pay_status'] = 0;
        $need_recive_money = $model_recp->where($map)->sum('all_price');

        $this->assign('total_count', $total_count);
        $this->assign('need_pay_count', $need_pay_count);
        $this->assign('recive_money', $recive_money);
        $this->assign('need_recive_money', $need_recive_money);
        $this->assign('recp_charts', $this->get_recp_charts(30));
        return $this->fetch();
    }

    private function get_recp_charts($days=10){
        $days_sec_arr = $days_arr = $root = array();
        $current_time = date('Y-m-d', time());
        for($i=$days-1;$i>=1;$i--){
            $days_sec_arr[] = strtotime("-$i day $current_time");
            $days_arr[] = date('m-d', strtotime("-$i day $current_time"));
        }
        $current_time = date('m-d', time());
        $days_sec_arr[] = time();
        $days_sec_arr[] = time() + 86400;
        $days_arr[] = $current_time;
        $root['title'] = array('text' => '近'.$days.'天接车单数量');
        $root['color'] = array('#3398DB');
        $root['tooltip'] = array(
            'trigger' => 'axis',
            'axisPointer' => array(
                'type' => 'shadow'
            )
        );
        $root['grid'] = array(
            'left' => '3%',
            'right' => '3%',
            'bottom' => '2%',
            'containLabel' => true
        );
        $root['xAxis'] = array(
            array(
                'type' => 'category',
                'data' => $days_arr,
                'axisTick' => array(
                    'alignWithLabel' => true
                )
            )
        );
        $root['yAxis'] = array(
            array('type' => 'value')
        );
        $model_recp = model('common/ShopOrderRecp');
        $map = array();
        $map['shop_id'] = $this->shop_id;
        $y = array();
        for($i=0;$i<count($days_sec_arr)-1;$i++){
            $map['create_time'] = array('between', array($days_sec_arr[$i], $days_sec_arr[$i+1]));
            $y[] = $model_recp->where($map)->count();
        }
        $root['series'] = array(
            array(
                'name' => '接车单',
                'type' => 'bar',
                'data' => $y
            )
        );
        return \GuzzleHttp\json_encode($root);
    }

    public function login($username = '', $password = '', $verify = ''){
        if (request()->isPost()) {
            if (!$username || !$password || !$verify) {
                return $this->error('用户名或者密码不能为空！', '');
            }
            //验证码验证
            $this->checkVerify($verify);

            $user = model('common/ShopUser');
            $uid  = $user->login($username, $password);
            if ($uid > 0) {
                return $this->success('登录成功！', "/index.php?s=admin/index/index");
            } else {
                switch ($uid) {
                    case -1:$error = '用户不存在或被禁用！';
                        break; //系统级别禁用
                    case -2:$error = '密码错误！';
                        break;
                    default:$error = '未知错误！';
                        break; // 0-接口参数错误（调试阶段使用）
                }
                return $this->error($error, '');
            }
        }else{
            if (is_login()) {
                $this->success("您已经是登录状态！", "/index.php?s=admin/index/index");
                exit;
            }

            $this->setMeta('登录');
            // 记录当前列表页的cookie
            cookie('__forward__', $_SERVER['REQUEST_URI']);
            return $this->fetch();
        }
    }

    public function logout() {
        $user = model('common/ShopUser');
        $user->logout();
        $this->redirect("/index.php?s=admin/index/login");
    }

    public function add_shop_with_tpl(){
        $map  = array();
        $map['shop_id'] = array('neq', 1);
        $shop = model('ShopInfo')->where($map)->select();
        $map = array();
        $menu = model('ShopMenu')->select();
        $menu = $menu ? collection($menu)->toArray() : array();
        $tpl = \think\Db::table('cys_config_temp')->where(array('conf_key'=>'shop_template'))->find();
        $tpl = \GuzzleHttp\json_decode($tpl['conf_value'], true);

        foreach ($shop as &$v){
            dump($v['shop_name']);
            $this->addMenu($tpl['menu'], $v['shop_id'], 0);
        }
    }

    private function addMenu($list, $shop_id, $pid=0){
        $model_menu = model('ShopMenu');
        foreach ($list as $v){
            $save_data = array();
            $save_data['p_id'] = $pid;
            $save_data['m_title'] = $v['m_title'];
            $save_data['m_url'] = $v['m_url'];
            $save_data['m_order'] = $v['m_order'];
            $save_data['m_type'] = $v['m_type'];
            $save_data['is_status'] = $v['is_status'];
            $save_data['shop_id'] = $shop_id;
            $shopmenu_id = $model_menu->insertGetId($save_data);
            if(!$v['child_menu']){
                continue;
            }
            $this->addMenu($v['child_menu'], $shop_id, $shopmenu_id);
        }
    }
}