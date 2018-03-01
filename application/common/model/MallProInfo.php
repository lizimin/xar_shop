<?php
namespace app\common\model;

use app\common\model\Model;

class MallProInfo extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_info';
    protected $pk = 'pro_id';

    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');

    public static $pro_tpye_arr = array(0=>'common',1=>'vcard');

    public function getProInfoById($pro_id, $field='*'){
        if(!$pro_id){
            return array();
        }
        $product = $this->field($field)->find($pro_id);
        if(!$product){
            return array();
        }
        return $product->toArray();
    }

    public function getCover($str){
        if(!is_array($str)){
            $str = explode(',', $str);
        }
        $map = array();
        if(empty($str)){
            $map['aid'] = -9999;
        }else{
            $map['aid'] = array('in', $str);
        }
        $covers = model('common/Attachment')->where($map)->field('aid,url,domain')->order('field(aid,'.implode(',', $str).')')->select();
        if(!$covers){
            return '';
        }
        $return_arr = array();
        foreach ($covers as &$v){
            $return_arr[] = $v->domain.$v->url;
        }
        return $return_arr;
    }

    /**
     * @param $str
     * @return string
     * 获取第一张封面图
     */
    public function getCoverOne($str, $pos=0){
        if(!is_array($str)){
            $str = explode(',', $str);
        }
        $map = array();
        if(empty($str)){
            $map['aid'] = -9999;
        }else{
            $map['aid'] = $str[$pos];
        }
        $obj = model('common/Attachment')->where($map)->field('url,domain')->find();
        if(!$obj){
            return '';
        }
        return $obj->domain.$obj->url;
    }

    /**
     * @param $pro_id
     * @param string $sort 排序方式
     * 获取商品的默认sku
     */
    public function getDefaultSku($pro_id, $sort='mall_price ASC'){
        if(!$pro_id){
            return array();
        }
        $sku_map = array();
        $sku_map['is_status'] = 1;
        $sku_map['pro_id'] = $pro_id;
        $obj = model('common/MallProSku')->where($sku_map)->field('sku_id,pro_id,sku_code,sku_cover,sku_name,mall_price')->order($sort)->find();
        if(!$obj){
            return array();
        }
        return $obj->toArray();
    }

    /**
     * @param array $where
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $field
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 前端获取产品列表时，用这个方法
     */
    public function getFrontDataList($where=array(),$page=0,$pagesize=10,$order='create_time DESC',$field='*'){
        $where['is_status'] = 1;
        $where['is_sale'] = 1;
        $field = 'pro_id,cat_id,pro_name AS goods_name,pro_img';
        if($pagesize){
            $list = $this->where($where)->field($field)->page($page,$pagesize)->order($order)->field($field)->select();
        }else{
            $list = $this->where($where)->field($field)->order($order)->field($field)->select();
        }
        $list = $list ? collection($list)->toArray() : array();
        foreach($list as &$v){
            $default_sku = $this->getDefaultSku($v['pro_id']);
            if(empty($default_sku)){
                unset($v);
                continue;
            }

            $v['sku_id'] = $default_sku['sku_id'];
            $v['sku_code'] = $default_sku['sku_code'];
            $v['goods_price'] = $default_sku['mall_price'];
            $v['photo'] = $default_sku['sku_cover'] ? $default_sku['sku_cover'] : $this->getCoverOne($v['pro_img']);
            unset($v['pro_img']);
        }
        $return['list'] = $list;

        $total = $this->where($where)->count();

        $return['page_data']['total']        = $total;
        $return['page_data']['current_page'] = $page;
        //总页数
        $return['page_data']['last_page']    = (int) ceil($total / $pagesize);

        return $return;
    }
}