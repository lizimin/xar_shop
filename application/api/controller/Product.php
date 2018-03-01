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
// | @Description 文件描述： API接口

namespace app\api\controller;
use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;
class Product extends ApiBase
{

    private $model_product;

    protected function _initialize(){
        parent::_initialize();
        $this->model_product = model('common/MallProInfo');
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取产品详情
     */
	public function getProduct(){
		$result = array();
		$pro_id = input('pro_id', 0);
		$errcode = 0;
		$msg = '';
		if($pro_id){
			$map = [
				'is_sale' => 1,
				'is_status' => 1,
				'pro_id' => $pro_id
			];
        	$result = db('mall_pro_info')->where($map)->find();
        	if($result){
        	    //轮播图
                $result['pro_img'] = model('MallProInfo')->getCover($result['pro_img']);
		        //查询服务条款
		        $result['clause'] = model('common/MallClause')->getClauseByIds($result['cla_id'], 'cla_name AS title,cla_icon AS icon,cla_des AS des');
        		$result['property'] = $this->getProductProperty($pro_id);
	        	foreach ($result['property'] as $key => $value) {
	        		$result['property'][$key]['value'] = $this->getProductPropertyValue($value['property_id'], $pro_id);
	        	}
	        	$result['pro_sku'] = model('mall_pro_sku')->getProSku(['is_status'=>1, 'pro_id'=>$pro_id]);
	        	foreach ($result['pro_sku'] as $key => $value) {
	        		$result['pro_sku'][$key]['share_amount'] = model('MallProSkuPolicy')->getUserPolicy($this->customer_id, $value);
	        	}
        	}

		}else{
			$errcode = 10001;
		}
		return $this->sendResult($result, $errcode, $msg);
	}

	public function getProductProperty($pro_id = 0){
		$result = array();
		if($pro_id){
			$result = db('mall_pro_sku_relation')->alias('a')->join('mall_pro_property b', 'a.property_id = b.property_id')->distinct(true)->field('b.property_id, b.property_name')->where(['a.pro_id' => $pro_id, 'a.is_status' => 1])->select();
		}
		return $result;
	}
	public function getProductPropertyValue($property_id = 0, $pro_id = 0){
		$result = array();
		if($property_id && $pro_id){
			$property_id_list = db('mall_pro_sku_relation')->alias('a')->join('mall_pro_sku b', ['a.sku_id=b.sku_id and b.is_status=1'])->where(['a.property_id'=>$property_id, 'a.pro_id'=>$pro_id, 'a.is_status' => 1])->column('a.property_value_id');
			$result = db('mall_pro_property_value')->where(['pv_id'=>['in',$property_id_list]])->select();
		}
		return $result;
	}

	public function shareProduct(){
		
	}

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取产品列表
     */
	public function getProductList(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');
        $cat_id       = input('cat_id',0,'intval');
        $page_size = 0;
        $where = array();

        if($cat_id){
	        //暂时只获取一级的，关闭递归
            /*$cats = model('common/MallProCat')->get_all_cat_id_recursion($cat_id);
            if(empty($cats)){
                $where['cat_id'] = $cat_id;
            }else{
                $where['cat_id'] = array('in', $cats);
            }*/
	        $where['a.cat_id'] = array('eq', $cat_id);
        }
        $model_sku = model('common/MallProSku');
        $list = $model_sku->getFrontDataListGroup($where, $page, $page_size);
        return $this->sendResult($list);
    }

}