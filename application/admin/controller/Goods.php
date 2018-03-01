<?php
// +----------------------------------------------------------------------
// |  [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 商品控制器 ]
//2017/11/30 14:03
namespace app\admin\controller;

class Goods extends Admin{
    private $model_goods, $model_cat;
    protected function _initialize(){
        parent::_initialize();
        $this->model_goods = model('common/Goods');
        $this->model_cat = model('common/GoodsCat');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $post_data = request()->param();
            if(isset($post_data['kw'])){
                $map['goods_name|goods_dep|goods_cont'] = array('like', '%'.$post_data['kw'].'%');
            }
            if($post_data['cat_id'] != ''){
                $map['cat_id'] = $post_data['cat_id'] ? $post_data['cat_id'] : 0;
                $map['cat_id'] = array('in', $this->get_all_cat_ids($map['cat_id'], 0 , array()));
            }
            $map['shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_goods->where($map)->count();
            $flows = $this->model_goods->where($map)->order('create_time DESC,goods_id DESC')->field($field)->select();
            $flows = $flows ? $flows : array();
            $flows = collection($flows)->toArray();
            foreach($flows as &$flow){

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

    private function get_all_cat($pid=0, $level=0, $map=array()){
        $list = array();
        $cats = $this->model_cat->where(array('p_cat_id'=>$pid,'is_status'=>1))->where($map)->field('*')->order('create_time ASC')->select();
        foreach($cats as &$cat){
            $cat = $cat->toArray();
            $cat['cat_name'] = str_repeat('&nbsp;', $level*10).$cat['cat_name'];
            $list[] = $cat;
            $child_groups = $this->model_cat->where(array('p_cat_id'=>$cat['cat_id'],'is_status'=>1))->where($map)->count();
            if($child_groups){
                $list = array_merge($list, $this->get_all_cat($cat['cat_id'], $level+1, $map));
            }
        }
        return $list;
    }

    private function get_all_cat_ids($pid=0, $level=0, $map=array()){
        $list = array();
        $list[] = $pid;
        $cats = $this->model_cat->where(array('p_cat_id'=>$pid,'is_status'=>1))->where($map)->column('cat_id');
        foreach($cats as &$cat){
            $list[] = $cat;
            $child_groups = $this->model_cat->where(array('p_cat_id'=>$cat,'is_status'=>1))->where($map)->count();
            if($child_groups){
                $list = array_merge($list, $this->get_all_cat_ids($cat, $level+1, $map));
            }
        }
        return $list;
    }
}