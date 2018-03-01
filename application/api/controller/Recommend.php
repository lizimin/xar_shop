<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com
//+----------------------------------------------------------------------
// | @Author: lishaoenbh@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: lishaoen
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 10:57:42
// +----------------------------------------------------------------------
// | @Description 文件描述： 所有推荐位置信息

namespace app\api\controller;

use app\common\model\MallProInfo;
use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;

class Recommend extends ApiBase{

    protected function _initialize(){
        parent::_initialize();

    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取我的页面悬浮窗的师傅
     */
    public function getMasterInMy(){
        $master = model('common/Worker')->getMasterInfoById(13);
        if(!$master){
            return $this->sendResult(array(), -1, '未找到师傅信息');
        }
        return $this->sendResult($master);
    }

    /**
     * 获取推荐商品列表
     */
    public function getRecommendGoodsList(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');

        $where['a.is_status'] = 1;
        $where['a.is_recommend'] = 1;
        $where['b.is_status'] = 1;
        $where['b.is_sale'] = 1;
//        $where['b.is_show'] = 1;

        //初始化
        $model_goods = model('common/MallProInfo');
        $model_sku = model('common/MallProSku');
        $return = $card_list = $page_data= [];
        $field = 'a.sku_id,a.sku_code,a.pro_id,a.cat_id,a.sku_name AS goods_name,a.mall_price AS goods_price,a.sku_cover,a.sku_cover AS photo,a.fun_str,a.mall_price,a.market_price,a.detail_des';
        //条件查询
        if($page_size){
            $list_data = $model_sku->alias('a')->join('mall_pro_info b', 'a.pro_id=b.pro_id')->field($field)->where($where)->page($page,$page_size)->order('a.sku_sort DESC')->select();
        }else{
            $pagesize = 1;
            $list_data = $model_sku->alias('a')->join('mall_pro_info b', 'a.pro_id=b.pro_id')->field($field)->where($where)->order('a.sku_sort DESC')->select();
        }
        $list_data = $list_data ? collection($list_data)->toArray() : array();
        foreach($list_data as &$v){
            $v['sku_code'] = $model_sku->getSkuCode($v);
            $product = $model_goods->getProInfoById($v['pro_id'], 'pro_name,pro_type');
            if(!empty($product)){
                $v['goods_name'] = $product['pro_name'].' '.$v['goods_name'];
            }
            $v['goods_redirect_type'] = MallProInfo::$pro_tpye_arr[$product['pro_type']];
        }

        $return['list'] = $list_data;

        $total = $model_sku->alias('a')->join('mall_pro_info b', 'a.pro_id=b.pro_id')->where($where)->count();

        $return['page_data']['total']        = $total;
        $return['page_data']['current_page'] = $page;
        //总页数
        $return['page_data']['last_page']    = (int) ceil($total / $page_size);
        return $this->sendResult($return);

    }

    /**
     * 获取推荐商品列表
     */
    public function getRecommendGoodsList_bak_bak(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');
        $return_arr = array();
        $page_data = array();
        $page_data['total'] = 1;
        $page_data['current_page'] = 1;
        $page_data['last_page'] = 1;
        $return_arr['page_data'] = $page_data;
        $model_sku = model('common/MallProSku');
        $sku_map = array();
        $sku_map['is_status'] = 1;
        $sku_map['sku_id'] = array('in', array(117,128,159,160,118));
        $list = $model_sku->where($sku_map)->field('sku_id,pro_id,sku_code,sku_cover AS photo,sku_name AS goods_name,mall_price AS goods_price,mall_price,market_price,detail_des')->select();
        $list = $list ? collection($list)->toArray() : array();
        $model_goods = model('common/MallProInfo');
        foreach($list as &$v){
            $v['sku_code'] = $model_sku->getSkuCode($v);
            $product = $model_goods->getProInfoById($v['pro_id'], 'pro_name,pro_type');
            if(!empty($product)){
                $v['goods_name'] = $product['pro_name'].' '.$v['goods_name'];
            }
            $v['goods_redirect_type'] = MallProInfo::$pro_tpye_arr[$product['pro_type']];
        }
        $return_arr['list'] = $list;
        return $this->sendResult($return_arr);

    }

    /**
     * 获取推荐商品列表
     */
    public function getRecommendGoodsList_bak(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');

        $return_arr = array();
        $page_data = array();
        $page_data['total'] = 1;
        $page_data['current_page'] = 1;
        $page_data['last_page'] = 1;
        $return_arr['page_data'] = $page_data;
        $model_goods = model('common/MallProInfo');
        $map = array();
        $map['is_status'] = 1;
        $map['is_sale'] = 1;
        $map['pro_id'] = array('in', array(2,24,25,26));
        $field = 'pro_id,pro_name AS goods_name,pro_img';
        $list = $model_goods->where($map)->field($field)->select();
        $list = $list ? collection($list)->toArray() : array();
        foreach($list as &$v){
            $default_sku = $model_goods->getDefaultSku($v['pro_id']);
            if(empty($default_sku)){
                unset($v);
                continue;
            }
            $v['sku_id'] = $default_sku['sku_id'];
            $v['sku_code'] = $default_sku['sku_code'];
            $v['goods_price'] = $default_sku['mall_price'];
            $v['photo'] = $default_sku['sku_cover'] ? $default_sku['sku_cover'] : $model_goods->getCoverOne($v['pro_img']);
            unset($v['pro_img']);
        }
        $return_arr['list'] = $list;
        return $this->sendResult($return_arr);

    }
}