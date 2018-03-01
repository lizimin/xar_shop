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
// | @Description 文件描述：获得赠与卡片模型

namespace app\common\model;

use app\common\model\Model;

class MallProVcardGive extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard_give';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * @param array $where
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $field
     * @return array
     * 获取用户获赠的卡片，只提取已经领取的卡片
     * 分页数据
     */
    public function get_given_vcard_list($where=array(),$page=0,$pagesize=10,$order='create_time DESC',$field='*'){
        $where['status'] = array('in', array(0,1));
        $where_or['expire_time'] = array('egt', time());
        //初始化
        $return = $card_list = $page_data= [];
        //条件查询
        $field = 'id,customer_id,to_customer_id,create_time,vc_id,card_code,expire_time,status';
        if($pagesize){
            $list_data = $this->where($where)->field($field)->page($page,$pagesize)->order('create_time DESC')->select();
        }else{
            $list_data = $this->where($where)->field($field)->order('create_time DESC')->select();
        }

        $list_data = $list_data ? collection($list_data)->toArray() : array();
        $model_card = model('common/MallProVcard');
        $card_field = 'vc_id,list_no,sku_id,exp_time';
        foreach($list_data as &$v){
            $v['given_customer'] = model('common/Customer')->getCustomerBaseInfo($v['customer_id'], 'cname,cavatar');
            $v['card_info'] = $model_card->queryCard(array('vc_id'=>$v['vc_id']), $card_field);
            $v['card_info']['use_info'] = array(
                'count' => 1,
                'used' => 0,
                'cancel' => 0,
                'can_use' => 1
            );
            $temp_ = $v;
            $card_list[] = $temp_;
        }

        $return['list'] = $card_list;


        $total = $this->where($where)->count();

        $return['page_data']['total']        = $total;
        $return['page_data']['current_page'] = $page;
        //总页数
        $return['page_data']['last_page']    = (int) ceil($total / $pagesize);
        return $return;
    }

    public function query_list($map=array(), $id=0, $type=1, $field='*', $limit=20, $sort='create_time DESC')
    {
        $map['status'] = array('eq', 1);
        switch ($type) {
            case 1:
                $map['id'] = array('gt', $id);
                break;
            case 2:
                $map['id'] = array('lt', $id);
                break;
        }
        //使用list_no作区分
        $list = $this->where($map)->field($field)->order($sort)->select();
        $list = $list ? collection($list)->toArray() : array();
        //处理必要信息
        $model_card = model('common/MallProVcard');
        $return_arr = array();
        foreach ($list as &$v) {
            $v['create_time_str'] = date('Y/m/d H:i');
            $v['expire_time_str'] = date('Y/m/d H:i');
            $v['card_info'] = $model_card->queryCard(array('vc_id' => $v['vc_id']));
            $return_arr[] = $v;
        }
        return $return_arr;
    }
}