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
// | @Last Modified time: 2017-12-02 10:52:50 
// +----------------------------------------------------------------------
// | @Description 文件描述： 订单接待表模型

namespace app\common\model;

use app\common\model\Model;

class ShopOrderJob extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_order_job';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'job_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $dateFormat = false;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //自动处理
    protected $auto = [];
    //插入时状态处理
    protected $insert = ['status' => 1];
    

    /**
     * 获取接车单选择服务信息
     *
     * @param array $where
     * @param string $field
     * @return void
     */
    public function get_order_job_by_jobsn($job_sn='',$field='*',$is_orderinfo=true){
        //初始化
        $return = array();
        //条件查询
        if($job_sn){
            $model_order_recp = model('common/ShopOrderRecp');
            if($is_orderinfo){
                $data = $model_order_recp->where($where=['job_sn'=>$job_sn])->field($field)->find();
                $data     = $data->getData();
                $return['order_info'] = $data;
            }
            
            //获取sg_id组信息
            $server_group = $this->where($where=['job_sn'=>$job_sn])->group('sg_id,service_id')->column('sg_id,job_remarks,work_group_id');
            if($server_group){
                foreach($server_group as $key=>$val){
                    $val['sg_name'] = get_model_data($model='shop_order_service_group',$id=$val['sg_id'],$field='sg_name',$field_where='sg_id');
                    $val['work_group_info'] = [];
                    if($val['work_group_id']){
                        $val['work_group_info'] = get_db_where_data($model='shop_user_group',$where=['group_id'=>$val['work_group_id']],$field='group_id,group_name,shop_user_id,shop_id',$cache_id=$val['work_group_id']);
                        
                        $val['work_group_info']['group_leader'] = [];
                        if($val['work_group_info']['shop_user_id']){
                            $val['work_group_info']['group_leader'] = get_db_where_data($model='shop_user',$where=['shop_user_id'=>$val['work_group_info']['shop_user_id']],$field='urealname,utel',$cache_id=$val['work_group_info']['shop_user_id']);
                        }
                    }
                    
                    $val['material_list'] = $this->get_shop_order_job_list($where=['job_sn'=>$job_sn,'sg_id'=>$val['sg_id'],'job_type'=>1]);
                    $val['service_list']  = $this->get_shop_order_job_list($where=['job_sn'=>$job_sn,'sg_id'=>$val['sg_id'],'job_type'=>0]);

                    $return['order_service'][] = $val;
                }
            }
        }
        
        return $return;
    }

    /**
     * 获取接车单job列表
     *
     * @param array $where
     * @param integer $pagesize
     * @param string $order
     * @param string $field
     * @return void
     */
    public function get_shop_order_job_list($where=[],$limit='',$order='job_id desc',$field='job_id,job_type,job_sn,carorder_id,job_remarks,car_id,car_plateno,sg_id,service_id,service_name,service_price,server_count,server_totalprice,work_group_id,confirm_id,shop_id,shop_user_id,create_time'){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->limit($limit)->order($order)->field($field)->select();
            if($data){
                $data = collection($data)->toArray();
                
                /*
                foreach($data as $key=>&$val){
                    $val['sg_name'] = get_model_data($model='shop_order_service_group',$id=$val['sg_id'],$field='sg_name',$field_where='sg_id');
                    if($val['work_group_id']){
                        $val['work_group_info'] = get_db_where_data($model='shop_user_group',$where=['group_id'=>$val['work_group_id']],$field='group_id,group_name,shop_user_id,shop_id',$cache_id=$val['work_group_id']);
                        
                        $val['work_group_info']['group_leader'] = [];
                        if($val['work_group_info']['shop_user_id']){
                            $val['work_group_info']['group_leader'] = get_db_where_data($model='shop_user',$where=['shop_user_id'=>$val['work_group_info']['shop_user_id']],$field='urealname,utel',$cache_id=$val['work_group_info']['shop_user_id']);
                        }
                    }
                }*/

                $return = $data;
            }
        }
        
        return $return;
    }
    
    /**
     * 获取接车单信息
     *
     * @param array $where
     * @param string $field
     * @return void
     */
    public function get_shop_order_job_info($where=array(),$field='*'){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->field($field)->find();
            $return     = $data->getData();
        }

        return $return;
    }

    /**
    * 添加接车单信息
    *
    * @param array $post_data
    * @return void
    */
    public function shop_order_job_save($post_data=[],$where=[]){
        //初始化
        $return = $map = array();

        if(!empty($post_data)){
            if($where){
                $result_data = $this->allowField(true)->save($post_data,$where);
            }else{
                $result_data = $this->allowField(true)->save($post_data);
            }
            if($result_data){
                $return = $this->job_id;
            }
        }
        return $return;
    }

    /**
    * 添加接车单信息
    *
    * @param array $post_data
    * @return void
    */
    public function shop_check_job_saveall($post_data=[],$order_recp_data){
        //初始化
        $return = array();

        if(!empty($post_data) && $order_recp_data){
            
            $shop_order_job = array();
            foreach ($post_data as $key => &$value) {
                //和接待订单关联id
                $value['carorder_id']      = $order_recp_data['carorder_id'];
                $value['car_id']           = $order_recp_data['car_id'];
                $value['car_plateno']      = $order_recp_data['car_plateno'];

                if($value['sg_id'] && $value['service_id']){
                    $value['job_type'] = 0;
                }else{
                    $value['job_type'] = 1;
                }
                if($order_recp_data['job_sn']){
                    $value['job_sn']      = $order_recp_data['job_sn'];
                }
                //服务id和服务名称
                if($value['service_id']){
                    $value['service_name']  = get_model_data($model='shop_order_service',$id=$value['service_id'],$field='service_name',$field_where='service_id');
                }

                //计算单条价格
                $value['server_count'] = $value['server_count'] ? $value['server_count'] : 1;
                $value['server_totalprice'] = $value['server_count'] * $value['service_price'];
                 
                $value['shop_id']      = $order_recp_data['shop_id'];
                $value['shop_user_id'] = $order_recp_data['shop_user_id'];
                
                //条件查询获取数据库中的值
                if($value['sg_id'] &&($value['job_sn'] || $value['carorder_id'])){
                    if($value['job_id']){
                        $map['job_id']      = $value['job_id'];
                    }elseif($value['job_type'] ==1 && empty($value['service_id'])){
                        $map['job_sn']      = $value['job_sn'];
                        $map['sg_id']       = $value['sg_id'];
                        $map['service_name'] = $value['service_name'];
                    }else{
                        $map['job_sn']     = $value['job_sn'];
                        $map['sg_id']      = $value['sg_id'];
                        $map['service_id'] = $value['service_id'];
                    }
                    $data = $this->where($map)->find();
                    if($data){
                        $data = $data->getData();
                        $value = array_merge($data,$value);
                    }
                }else{
                    continue;
                }

                $shop_order_job[] = $value;
            }
            
            $result_data = $this->allowField(true)->saveAll($shop_order_job);
            if($result_data){
                //遍历数据对象数组
                foreach ($result_data as $list){
                    $order_job_ids[]    = $list->getData('job_id');
                    $order_job_data[] = $list->getData();
                }

                $return = array(
                    'job_ids' => $order_job_ids,
                    'data'    => $order_job_data,
                );
            }
        }
        return $return;
    }

}