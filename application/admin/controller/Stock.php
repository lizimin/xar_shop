<?php
// +----------------------------------------------------------------------
// |  [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 库存控制器 ]
//2017/11/30 14:03
namespace app\admin\controller;

class Stock extends Admin{
    private $model_stock;
    protected function _initialize(){
        parent::_initialize();
        $this->model_stock = model('common/Stock');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $post_data = request()->param();
            if($post_data['goods_id'] != ''){
                $map['goods_id'] = $post_data['goods_id'] ? $post_data['goods_id'] : 0;
            }
            $map['shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_stock->where($map)->count();
            $flows = $this->model_stock->where($map)->order('create_time DESC,stock_id DESC')->field($field)->select();
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
}