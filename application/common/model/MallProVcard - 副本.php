<?php
// +----------------------------------------------------------------------
// | [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 洗车卡模型 ]
//2017/12/20 15:35
namespace app\common\model;

use app\common\model\Model;

class MallProVcard extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_vcard';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'vc_id';

    protected $dateFormat = false;

    /**
     * @param $data包含下列数据
     * $type 1：下拉刷新（获取最新） 2：上拉加载更多
     * $id 分割ID
     * $customer_id 客户ID
     * 通过opend等查询用户洗车卡，支持上拉和下拉
     */
    public function get_list_by_user($data){
        $customer_id = $data['customer_id'];
        $type = $data['type'];
        $id = $data['id'];
        $map = array();
        $map['customer_id'] = $customer_id;
        $list = $this->query_list($map, $id, $type, '*', 20);
        return $list;
    }

    /**
     * @param $data包含下列数据
     * $type 1：下拉刷新（获取最新） 2：上拉加载更多
     * $id 分割ID
     * $customer_id 客户ID
     * 通过opend等查询用户获赠的洗车卡，支持上拉和下拉
     */
    public function get_given_list_by_user($data){
        $customer_id = $data['customer_id'];
        $type = $data['type'];
        $id = $data['id'];
        $map = array();
        $map['customer_id'] = $customer_id;
        $list = model('common/MallProVcardGive')->query_list($map, $id, $type, '*', 20);
        return $list;
    }

    /**
     * 获取推荐的卡片
     */
    public function get_recommend_vcards(){
        $model_sku = model('common/MallProSku');
        //推荐的洗车卡先指定几个
        $temp_ids = array(117,118,119);
        $map = array();
        $map['sku_id'] = array('in', $temp_ids);
        $field = 'sku_id,sku_name AS goods_name,mall_price AS goods_price,sku_cover AS photo';
        $list = $model_sku->where($map)->field($field)->select();
        $list = $list ? collection($list)->toArray() : array();
        $return['list'] = $list;


        $total = count($list);

        $return['page_data']['total']        = $total;
        $return['page_data']['current_page'] = 1;
        //总页数
        $return['page_data']['last_page']    = 1;
        return $return;
    }

    public function get_card_info($out_trade_no, $customer_id){
        $map = array();
        $map['customer_id'] = $customer_id;
        $map['out_trade_no'] = $out_trade_no;
        $card = $this->where($map)->field('sku_id')->find();
        if(!$card){
            return array('status'=>-1, 'msg'=>'卡片不存在！');
        }
        $model_sku = model('common/MallProSku');
        $map = array();
        $map['sku_id'] = $card->sku_id;
        $sku = $model_sku->where($map)->field('sku_name,extra_data')->find();
        if(!$sku){
            return array('status'=>-1, 'msg'=>'卡片基础信息不存在！');
        }
        $return_arr = array();
        $return_arr['status'] = 0;
        $card_info = array();
        $card_info['name'] = $sku->sku_name;
        $card_info['out_trade_no'] = $out_trade_no;
        $card_info['card_use_info'] = $this->get_card_use_info($out_trade_no, $customer_id);
        $card_info['extend_info'] = \GuzzleHttp\json_decode($sku->extra_data, true);
        $return_arr['card'] = $card_info;
        return $return_arr;
    }

    /**
     * @param $customer_id
     * 获取用户所有卡数据
     */
    public function get_all_card_info($customer_id){
        $map['customer_id'] = $customer_id;
        $map['is_status'] = array('eq', 1);
        //使用out_trade_no作区分
        $list = $this->where($map)->group('out_trade_no')->field('out_trade_no')->order('create_time DESC')->select();
        $list = $list ? collection($list)->toArray() : array();
        //处理必要信息
        $return_arr = array();
        $can_use = 0;
        foreach($list as &$v){
            $temp_ = $this->get_card_use_info($v['out_trade_no'], $customer_id);
            if($temp_['status'] == 1 || $temp_['status'] == 2){
                $can_use++;
            }
        }
        $return_arr['msg'] = '可用的卡共（'.$can_use.'张）';
        return $return_arr;
    }

    /**
     * @param $out_trade_no
     * @param $customer_id
     * @return array
     * 卡片使用情况
     */
    public function get_card_use_info($out_trade_no, $customer_id){
        $map = array();
        $map['customer_id'] = $customer_id;
        $map['out_trade_no'] = $out_trade_no;
        $list = $this->where($map)->field('begin_time,exp_time,is_use,is_status')->select();
        //0：不可用 1：未使用 2：可用 3：用完
        $status_code = 0;
        if(!$list){
            return array('status'=>$status_code, 'msg'=>'');
        }
        $count = count($list);
        $can_use = 0;
        $used = 0;
        $canceled = 0;
        foreach($list as &$v){
            if(($v->exp_time > time() && $v->begin_time <= time()) || time() <= $v->begin_time){
                if($v->is_use <= 0 && $v->is_status == 1){
                    $can_use++;
                }
            }
            //只要大于可用，就当做用掉了
            if($v->is_status > 1){
                $used++;
            }
            if($v->is_status < 0){
                $canceled++;
            }
        }
        $return_arr = array();
        //使用情况
        $return_arr['use_info'] = array(
            'count' => $count,
            'used' => $used,
            'cancel' => $canceled,
            'can_use' => $count - $used - $canceled
        );
        $return_arr['msg'] = '剩余'.$return_arr['use_info']['can_use'].'/'.$return_arr['use_info']['count'].'次';
        return $return_arr;
    }

    /**
     * @param array $where
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $field
     * @return array
     * 分页数据
     */
    public function get_vcard_list($where=array(),$page=0,$pagesize=10,$order='out_trade_no DESC',$field='*'){
        $where['is_status'] = array('eq', 1);
        //初始化
        $return = $card_list = $page_data= [];
        //条件查询
        if($where){

            if($pagesize){
                $list_data = $this->where($where)->group('out_trade_no')->field('out_trade_no,sku_id,create_time,begin_time,exp_time')->page($page,$pagesize)->order('create_time DESC')->select();
            }else{
                $list_data = $this->where($where)->group('out_trade_no')->field('out_trade_no,sku_id,create_time,begin_time,exp_time')->order('create_time DESC')->select();
            }

            if($list_data){
                $list_data = collection($list_data)->toArray();
                $model_sku = model('common/MallProSku');
                foreach($list_data as &$v){
                    //提取sku名称
                    $sku = $model_sku->where(array('sku_id'=>$v['sku_id']))->field('*')->find();
                    if(!$sku){
                        $sku = array();
                    }
                    $temp_ = array();
                    $temp_['out_trade_no'] = $v['out_trade_no'];
                    $temp_['name'] = $sku['sku_name'];
                    $temp_['background'] = $sku['sku_cover'];
                    $temp_['exp_time_str'] = $this->expTimeFormat($v['exp_time']);
                    $temp_['extra_data'] = \GuzzleHttp\json_decode($sku['extra_data'], true);
                    //查询剩余次数,过期次数
                    if($where['customer_id']){
                        $temp_ = array_merge($temp_, $this->get_card_use_info($temp_['out_trade_no'], $where['customer_id']));
                    }
                    //过期时间格式化：200天后过期
                    $card_list[] = $temp_;
                }

                $return['list'] = $card_list;


                $total = $this->where($where)->group('out_trade_no')->count();

                $return['page_data']['total']        = $total;
                $return['page_data']['current_page'] = $page;
                //总页数
                $return['page_data']['last_page']    = (int) ceil($total / $pagesize);


            }
        }
        return $return;
    }

    /**
     * @param array $map
     * @param $id 分割ID
     * @param $type 1：下拉刷新（获取最新） 2：上拉加载更多
     * @param string $field
     * @param int $limit
     * @param string $sort
     * 支持上拉和下拉
     */
    public function query_list($map=array(), $id=0, $type=1, $field='*', $limit=20, $sort='begin_time DESC'){
        $map['is_status'] = array('eq', 1);
        switch($type){
            case 1:
                if($id){
                    $map['out_trade_no'] = array('gt', $id);
                }
                break;
            case 2:
                $map['out_trade_no'] = array('lt', $id);
        }
        //使用out_trade_no作区分
        $list = $this->where($map)->group('out_trade_no')->field('out_trade_no,sku_id,create_time,begin_time')->order('create_time DESC')->select();
        $list = $list ? collection($list)->toArray() : array();
        //处理必要信息
        $return_arr = array();
        $model_sku = model('common/MallProSku');
        foreach($list as &$v){
            //提取sku名称
            $sku = $model_sku->where(array('sku_id'=>$v['sku_id']))->field('*')->find();
            if(!$sku){
                $sku = array();
            }
            $temp_ = array();
            $temp_['out_trade_no'] = $v['out_trade_no'];
            $temp_['name'] = $sku['sku_name'];
            $temp_['create_time'] = $v['create_time'];
            $temp_['create_time_str'] = date('Y-m-d H:i:s', $v['create_time']);
            $temp_['extra_data'] = \GuzzleHttp\json_decode($sku['extra_data'], true);
            //查询剩余次数,过期次数
            if($map['customer_id']){
                $temp_ = array_merge($temp_, $this->get_card_use_info($temp_['out_trade_no'], $map['customer_id']));
            }
            $return_arr[] = $temp_;
        }
        return $return_arr;
    }

    /**
     * @param array $map
     * @param string $field
     * 查询卡的信息，并查询sku
     */
    public function queryCard($map=array(), $field='*', $show_extend_info=true){
        if(empty($map)){
            return array();
        }
        $card = $this->where($map)->field($field)->find();
        if(!$card){
            return array();
        }
        $card = $card->toArray();
        $card['exp_time_str'] = $this->expTimeFormat($card['exp_time']);
        $model_sku = model('common/MallProSku');
        //提取sku名称
        $sku = $model_sku->where(array('sku_id'=>$card['sku_id']))->field('*')->find();
        if(!$sku){
            return array();
        }
        $card['name'] = $sku['sku_name'];
        $card['background'] = $sku['sku_cover'];
        if($show_extend_info){
            $card['extend_info'] = $sku['extra_data'] ? \GuzzleHttp\json_decode($sku['extra_data'], true) : array();
        }
        return $card;
    }

    /**
     * @param int $exp_time 过期时间 时间戳
     * @param string $format 格式化格式：day:天
     */
    private function expTimeFormat($exp_time=0, $format='day'){
        if(!is_numeric($exp_time)){
            return '时间格式错误';
        }

        if($exp_time < time()){
            return '已过期';
        }

        $format_str = '';
        //格式化为天
        if($format == 'day'){
            //计算剩余秒数
            $surplus_sec = $exp_time - time();
            $day_sec = 86400;
            $surplus_day = intval($surplus_sec / $day_sec);
            //具体输出
            if($surplus_day < 1){
                $format_str = '今天'.date('H:i').'过期';
            }else{
                $format_str = $surplus_day.'天后过期';
            }
        }

        return $format_str;
    }
}