<?php
// +----------------------------------------------------------------------
// |  [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 入库记录控制器 ]
//2017/11/30 14:03
namespace app\admin\controller;

class Stockin extends Admin{
    private $model_stock_in, $model_stock;
    protected function _initialize(){
        parent::_initialize();
        $this->model_stock_in = model('common/StockIn');
        $this->model_stock = model('common/Stock');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $post_data = request()->param();
            if(isset($post_data['start_time']) && isset($post_data['end_time'])){
                $post_data['start_time'] = $post_data['start_time'] ? strtotime($post_data['start_time']) : time();
                $post_data['end_time'] = $post_data['end_time'] ? strtotime($post_data['end_time']) + 86400 : time()+86400;
                $map['create_time'] = array('between', array($post_data['start_time'], $post_data['end_time']));
            }
            if(isset($post_data['kw'])){
                $map['in_remarks'] = array('like', '%'.$post_data['kw'].'%');
            }
            if($post_data['goods_id'] != ''){
                $map['goods_id'] = $post_data['goods_id'] ? $post_data['goods_id'] : 0;;
            }
            $map['accpet_shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_stock_in->where($map)->count();
            $flows = $this->model_stock_in->where($map)->order('create_time DESC,st_inid DESC')->field($field)->select();
            $flows = $flows ? $flows : array();
            $flows = collection($flows)->toArray();
            $model_goods = model('common/Goods');
            foreach($flows as &$flow){
                if($flow['goods_id']){
                    $goods = $model_goods->where(array('goods_id'=>$flow['goods_id']))->field('goods_name,goods_img,goods_dep,goods_price,goods_whoprice')->find();
                    $goods = $goods ? $goods->toArray() : array();
                    $flow = array_merge($flow,$goods);
                    $temp_img = is_array($flow['goods_img']) ? array_unique(array_filter($flow['goods_img'])) : array_unique(array_filter(explode(',', $flow['goods_img'])));
                    $flow['goods_img'] = '';
                    foreach($temp_img as &$img){
                        $path = get_file($img, 'path');
                        $flow['goods_img'] .= '<img src="'.$path.'"/>';
                    }
                }
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $flows;
            echo json_encode($return_arr);
        }
    }

    public function get_cats(){
        $return_arr = $map = array();
        $post_data = request()->param();
        if(isset($post_data['kw'])){
            $map['cat_name|cat_dep'] = array('like', '%'.$post_data['kw'].'%');
        }
        if($post_data['cat_id'] != ''){
            $map['cat_id'] = $post_data['cat_id'] ? $post_data['cat_id'] : 0;;
        };
        $field = '*';
        $cats = $this->get_all_cat(0, 0, $map);
        $count = count($cats);
        $cats = $cats ? $cats : array();
        $cats = collection($cats)->toArray();
        foreach($cats as &$cat){

        }
        $return_arr['code'] = 0;
        $return_arr['msg'] = '';
        $return_arr['count'] = $count;
        $return_arr['data'] = $cats;
        echo json_encode($return_arr);
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $data['accpet_shop_id'] = $this->shop_id;
            $data['accpet_shop_group_id'] = $this->shop_group_id;
            if(!$this->set_stock($data)){
                return json_encode(array('status'=> -888, 'msg'=>'插入库存失败'));
            }
            $result = $this->model_stock_in->validate(true)->allowField(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_stock_in->getError()));
            }
        }else{
            $info = array();
            $this->assign('info', $info);
            $this->assign('shop_users', $this->get_shop_user($this->shop_id));
            $this->assign('group_shops', $this->get_group_shop());
            $this->setMeta('添加入库');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['st_inid']);
            $result = $this->model_stock_in->validate(true)->allowField(true)->save($data, array('st_inid' => $data['st_inid']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_stock_in->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_stock_in->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('info', $info);
            $this->setMeta('编辑入库');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
        $result = $this->model_stock_in->where(array('st_inid' => $id))->delete();
//        $result =  $this->model_stock_in->where(array('st_inid' => $id))->setField('acc_is_status', -1);
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
        $info = $this->model_stock_in->field('st_inid,accpet_shop_id')->find($id);
        if ($info === false) {
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'信息不存在！'));
            }
            exit;
        }
        if($this->shop_id != $info->accpet_shop_id){
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'你只能管理您店铺的信息！'));
            }
            exit;
        }
    }

    public function get_shop_user($shop_id, $format=false){
        $model_user = model('common/ShopUser');
        $map = array();
        $map['shop_id'] = $shop_id;
        $map['is_status'] = array('gt', 0);
        $field = 'shop_user_id,urealname';
        $users = $model_user->where($map)->field($field)->select();
        if(!$format){
            return $users ? collection($users)->toArray() : array();
        }else{
            return $users ? json_encode(collection($users)->toArray()) : json_encode(array());
        }
    }

    public function get_group_shop(){
        $model_shop_info = model('common/ShopInfo');
        $map = array();
        $map['shop_group_id'] = $this->shop_group_id;
        $map['is_status'] = array('gt', 0);
        $map['exp_time'] = array('gt', time());
        $map['shop_id'] = array('neq', $this->shop_id);
        $field = 'shop_id,shop_name';
        $shops = $model_shop_info->where($map)->field($field)->select();
        return $shops ? $shops : array();
    }

    private function set_stock($data){
        $map = array();
        $map['goods_id'] = $data['goods_id'];
        $map['shop_id'] = $this->shop_id;
        $map['shop_group_id'] = $this->shop_group_id;
        $has = $this->model_stock->where($map)->field('stock_id,goods_id,goods_num')->find();
        if($has){
            return $this->model_stock->where($map)->setField('goods_num', ($has->goods_num + $data['goods_num']));
        }else{
            $map['goods_num'] = $data['goods_num'];
            return $this->model_stock->save($map);
        }
    }
}