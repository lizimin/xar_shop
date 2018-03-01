<?php
namespace app\common\model;

use app\common\model\Model;

class MallProVcardDestory extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard_destory';
    protected $pk = 'id';

    // 新增自动完成列表
    protected $insert = array('add_time');

    public function getImgListAttr($ids){
        $ids = array_filter(explode(',', $ids));
        $return_arr = $map = array();
        if(empty($ids)){
            $map['aid'] = -1;
        }else{
            $map['aid'] = array('in', $ids);
        }
        $imgs = model('common/Attachment')->where($map)->field('url,domain')->select();
        if(!$imgs){
            return array();
        }
        foreach($imgs as &$v){
            $return_arr[] = $v->domain.$v->url;
        }
        return $return_arr;
    }

    /**
     * @param $vc_id
     * 获取核销记录信息，返回单条记录
     */
    public function getDestoryInfo($vc_id, $field='*'){
        if(!$vc_id){
            return array();
        }
        $log = $this->where(array('vc_id'=>$vc_id))->field($field)->order('add_time DESC')->find();
        $log = $log ? $log->toArray() : array();
        //处理店面信息
        $shop_info = model('common/ShopInfo')->getShopInfoById($log['shop_id'], 'shop_name');
        $log['shop_name'] = $shop_info['shop_name'];
        $log['add_time'] = date('Y-m-d H:i', $log['add_time']);
        return $log;
    }
}