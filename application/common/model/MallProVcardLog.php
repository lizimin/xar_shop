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
// | @Last Modified time: 2017-12-01 10:28:38 
// +----------------------------------------------------------------------
// | @Description 文件描述：洗车卡消费记录模型

namespace app\common\model;

use app\common\model\Model;

class MallProVcardLog extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard_log';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'vlog_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //记录动作类型，对应type字段
    public static $action_type = array(
        0 => '购买成功',
        1 => '消费',
        2 => '赠送',
        3 => '领取好友赠送',
        4 => '过期退回',
        5 => '撤销赠送',
    );

    /**
     * @param array $where
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $field
     * @return array
     * 获取洗车卡使用（消费）记录
     */
    public function get_list($where=array(),$page=0,$pagesize=10,$order='create_time DESC',$field='*'){
        if(isset($where['list_no'])){
            $vc_ids = model('common/MallProVcard')->where(array('list_no'=>$where['list_no']))->column('vc_id');
            if(empty($vc_ids)){
                $where['vc_id'] = -1;
            }else{
                $where['vc_id'] = array('in', $vc_ids);
            }
        }
        unset($where['list_no']);

        // $where['vc_action_type'] = 1;
        $where['is_status'] = 1;
        //初始化
        $return = $card_list = $page_data= [];
        //条件查询
        if($where){
            $field = 'vlog_id,vc_id,vc_action_type,type,customer_id,is_status,create_time';
            if($pagesize){
                $list_data = $this->where($where)->field($field)->page($page,$pagesize)->order($order)->select();
            }else{
                $list_data = $this->where($where)->field($field)->order($order)->select();
            }

            $list_data = $list_data ? collection($list_data)->toArray() : array();
            $model_card = model('common/MallProVcard');
            $model_comment = model('common/Comment');
            $card_field = 'vc_id,sku_id,exp_time';
            foreach($list_data as &$v){
                $v['action'] = self::$action_type[$v['vc_action_type']];
                $v['out_trade_no'] = get_model_data('MallProVcard', $v['vc_id'], 'out_trade_no', 'vc_id');
                $v['card_info'] = $model_card->queryCard(array('vc_id'=>$v['vc_id']), $card_field, false);
                $v['comment'] = $model_comment->get_washcar_comment($v);
                unset($v['type']);
                $temp_ = $v;
                $card_list[] = $temp_;
            }

            $return['list'] = $card_list;

            $total = $this->where($where)->count();

            $return['page_data']['total']        = $total;
            $return['page_data']['current_page'] = $page;
            //总页数
            $return['page_data']['last_page']    = (int) ceil($total / $pagesize);
        }
        return $return;
    }

    /**
     * @param array $where
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $field
     * @return array
     * 获取用户领取卡片情况列表
     */
    public function get_recevice_list($where=array(),$page=0,$pagesize=10,$order='create_time DESC',$field='*'){

         $where['vc_action_type'] = 3;
        $where['is_status'] = 1;
        //初始化
        $return = $card_list = $page_data= [];
        //条件查询
        if($where){
            $field = 'vc_id,customer_id,to_customer_id,create_time';
            if($pagesize){
                $list_data = $this->where($where)->field($field)->page($page,$pagesize)->order($order)->select();
            }else{
                $list_data = $this->where($where)->field($field)->order($order)->select();
            }

            $list_data = $list_data ? collection($list_data)->toArray() : array();
            $model_card = model('common/MallProVcard');
            $model_customer = model('common/Customer');
            $model_product = model('common/MallProInfo');
            $model_sku = model('common/MallProSku');
            $card_field = '*';
            foreach($list_data as &$v){
                $v['customer'] = $model_customer->getCustomerBaseInfo($v['customer_id'],'cname');
                $v['customer']['cname'] = strSpcialForma($v['customer']['cname']);
                $v['to_customer'] = $model_customer->getCustomerBaseInfo($v['to_customer_id'], 'cname');
                $v['to_customer']['cname'] = strSpcialForma($v['to_customer']['cname']);
                $v['vcard'] = $model_card->where(array('vc_id'=>$v['vc_id']))->field('list_no,sku_id')->find();
                $v['sku'] = $model_sku->where(array('sku_id'=>$v['vcard']['sku_id']))->field('sku_id,pro_id,sku_name,detail_des,short_des,sku_use_rule,card_use_rule')->find();
                $v['product'] = $model_product->where(array('pro_id'=>$v['sku']['pro_id']))->field('pro_id,pro_name,pro_dep')->find();
                unset($v['vc_id']);
                unset($v['customer_id']);
                unset($v['to_customer_id']);
                $card_list[] = $v;
            }

            $return['list'] = $card_list;

            $total = $this->where($where)->count();

            $return['page_data']['total']        = $total;
            $return['page_data']['current_page'] = $page;
            //总页数
            $return['page_data']['last_page']    = (int) ceil($total / $pagesize);
        }
        return $return;
    }
}