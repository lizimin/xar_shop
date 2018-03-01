<?php
namespace app\common\model;

use app\common\model\Model;

class MallProSku extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_sku';
    protected $pk = 'sku_id';

    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');


    public function getSkuCoverAttr($str){
        $obj = model('common/Attachment')->where(array('aid'=>$str))->field('url,domain')->find();
        if(!$obj){
            return '';
        }
        return $obj->domain.$obj->url;
    }

    public function getPhotoAttr($str){
        $obj = model('common/Attachment')->where(array('aid'=>$str))->field('url,domain')->find();
        if(!$obj){
            return '';
        }
        return $obj->domain.$obj->url;
    }

    public function getProSku($map = [], $field = '*'){
    	$result = $this->where($map)->field($field)->select();
    	foreach ($result as $key => $value) {
    		$extra_data = json_decode($value['extra_data']);
    		$result[$key]['extra_data'] = $extra_data ? $extra_data : [];
    	}
    	return $result;
    }

    /**
     * @param array $where
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $field
     * @return mixed
     * 返回按照分类分组的商品列表，这里的where必选带cat_id
     */
    public function getFrontDataListGroup($where=array(),$page=0,$pagesize=10,$order='a.create_time DESC',$field='*'){
        //如果没有带cat_id就获取全部的，这里只有一级的数据
        $list = $cats = array();
        if(!isset($where['a.cat_id'])){
            $cats = model('common/MallProCat')->get_all_cat_id(0);
            if(empty($cats)){
                $where['a.cat_id'] = 0;
            }else{
                $where['a.cat_id'] = array('in', $cats);
            }
        }else{
            $cats[] = $where['cat_id'][1];
        }
        $where['a.is_status'] = 1;
        $where['b.is_status'] = 1;
        $where['b.is_sale'] = 1;
//        $where['b.is_show'] = 1;

        $field = 'a.sku_id,a.sku_code,a.pro_id,a.cat_id,a.sku_name AS goods_name,a.mall_price AS goods_price,a.sku_cover,a.sku_cover AS photo,a.fun_str,a.mall_price,a.market_price,a.detail_des';

        if($pagesize){
//            $list_pro = $this->where($where)->field($field)->page($page,$pagesize)->order($order)->field($field)->select();
            $list_pro = $this->alias('a')->join('mall_pro_info b', 'a.pro_id=b.pro_id')->field($field)->where($where)->page($page,$pagesize)->order($order)->select();
        }else{
            $pagesize = 1;
//            $list_pro = $this->where($where)->field($field)->order($order)->field($field)->select();
            $list_pro = $this->alias('a')->join('mall_pro_info b', 'a.pro_id=b.pro_id')->field($field)->where($where)->order($order)->select();
        }
        $list_pro = $list_pro ? collection($list_pro)->toArray() : array();

        //处理分组
        $model_cat = model('common/MallProCat');
        foreach($cats as &$v){
            $list[$v] = $model_cat->get_cat_by_id($v);
            $list[$v]['product'] = array();
        }

        //塞进分组
        $model_product = model('common/MallProInfo');
        foreach($list_pro as &$v){
            $v['sku_code'] = $this->getSkuCode($v);
            $product = $model_product->getProInfoById($v['pro_id'], 'pro_name,pro_type');
            if(!empty($product)){
                $v['goods_name'] = $product['pro_name'].' '.$v['goods_name'];
            }
            $v['goods_redirect_type'] = MallProInfo::$pro_tpye_arr[$product['pro_type']];
            $v['photo'] = $v['sku_cover'] ? $v['sku_cover'] : $model_product->getCoverOne($v['pro_img']);
            unset($v['fun_str']);
            unset($v['pro_img']);

            $list[$v['cat_id']]['product'][] = $v;
        }

        //插入推荐商品
        array_unshift($list, array('cat_id'=>0, 'cat_name'=>'推荐', 'product'=>$this->getRecommendCatSku()));
        $return['list'] = array_values($list);

        $total = $this->alias('a')->join('mall_pro_info b', 'a.pro_id=b.pro_id')->where($where)->count();

        $return['page_data']['total']        = $total;
        $return['page_data']['current_page'] = $page;
        //总页数
        $return['page_data']['last_page']    = (int) ceil($total / $pagesize);

        return $return;
    }

    /**
     * @param $pro
     * 获取sku_code分隔
     * 这是一个零时方法
     */
    public function getSkuCode($sku){
        $sufx = 'P';
        $val = model('common/MallProSkuRelation')->where(array('sku_id'=>$sku['sku_id'],'is_status'=>1))->column('property_value_id');
        sort($val);
        $code = $sufx.implode($sufx, $val);
        return $code;
    }

    /**
     * @return array|false|\PDOStatement|string|\think\Collection
     * 这是一个零时接口
     */
    private function getRecommendCatSku(){
        $where['a.is_status'] = 1;
        $where['a.is_recommend'] = 1;
        $where['b.is_status'] = 1;
        $where['b.is_sale'] = 1;
//        $where['b.is_show'] = 1;
        $field = 'a.sku_id,a.sku_code,a.pro_id,a.cat_id,a.sku_name AS goods_name,a.mall_price AS goods_price,a.sku_cover,a.sku_cover AS photo,a.fun_str,a.mall_price,a.market_price,a.detail_des';
        $list = $this->alias('a')->join('mall_pro_info b', 'a.pro_id=b.pro_id')->field($field)->where($where)->order('a.sku_sort DESC')->select();
        $list = $list ? collection($list)->toArray() : array();
        $model_product = model('common/MallProInfo');
        foreach($list as &$v){
            $v['sku_code'] = $this->getSkuCode($v);
            $product = $model_product->getProInfoById($v['pro_id'], 'pro_name,pro_type');
            if(!empty($product)){
                $v['goods_name'] = $product['pro_name'].' '.$v['goods_name'];
            }
            $v['goods_redirect_type'] = MallProInfo::$pro_tpye_arr[$product['pro_type']];
            unset($v['fun_str']);
        }
        return $list;
    }
}