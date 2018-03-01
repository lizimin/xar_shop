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

class ShopOrderRecp extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_order_recp';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'carorder_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //自动处理
    protected $auto = ['shop_group_id','order_deadline'];
    //插入时状态处理
    protected $insert = ['order_sn','job_sn','order_status' => 1];

    //所属用户组
    public function shopcheckorder(){
        return $this->hasMany('ShopCheckOrder', 'order_sn')->field('*');
    }

    //获取店铺对应的集团id
    protected function setShopGroupIdAttr($value,$data)
    {   
        if($data['shop_id']){
            $value = get_model_data($model='admin_shop_group_relation',$id=$data['shop_id'],$field='shop_group_id',$field_where='shop_id');
        }
        return $value;
    }

    //获取店铺对应的集团id
    protected function setOrderDeadlineAttr($value,$data)
    {   
        if($value){
            $value = is_int($value) ? $value : strtotime($value);
        }
        return $value;
    }

    //生产订单sn
    protected function setOrderSnAttr($value,$data)
    {
        if($data['shop_id'] && empty($value)){
            $value = create_sn_code($prefix='Snorder',$shopid=$data['shop_id'], $rule='datetime',mb_substr($data['car_plateno'],1,6,'utf-8'));
        }
        return $value;
    }

    //生产工单sn
    protected function setJobSnAttr($value,$data)
    {
        if($data['shop_id'] && empty($value)){
            $value = create_sn_code($prefix='Snjob',$shopid=$data['shop_id'], $rule='datetime',mb_substr($data['car_plateno'],1,6,'utf-8'));
        }
        return $value;
    }

    /**
     * 获取接车单列表
     *
     * @param array $where
     * @param integer $pagesize
     * @param string $order
     * @param string $field
     * @return void
     */
    public function get_shop_order_recp_list($where=array(),$page=0,$pagesize=10,$order='carorder_id desc',$field='*'){
        //初始化
        $return = $recp_list = $page_data= [];
        //条件查询
        if($where){
            if($pagesize){
                $list_data = $this->where($where)->page($page,$pagesize)->order($order)->field($field)->select();
            }else{
                $list_data = $this->where($where)->order($order)->field($field)->select();
            }

            if($list_data){
                $list_data = collection($list_data)->toArray();
                foreach($list_data as $key=>$val){
                    //获取子订单
					$map_sub['order_pid']    = $val['carorder_id'];
					$map_sub['order_status'] = ['egt',0];

                    $val['chilren'] = $this->get_shop_order_recp_list($map_sub);

                    $recp_list[] = $val;
                }

                $return['recp_list'] = $recp_list;

                //if($pagesize){
                    $total = $this->where($where)->count();

                    $return['page_data']['total']        = $total;
                    $return['page_data']['current_page'] = $page ? $page : 1;
                    $return['page_data']['last_page']    = (int) ceil($total / $pagesize);
                //}

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
    public function get_shop_order_recp_info($where=array(),$field='*', $extend=false){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->field($field)->find();
            if($data && $extend){
                $return     = $data->getData();
                
                if($data['car_outside_checkid']){
                    $map['order_sn']     = array('eq',$data['order_sn']);
                    $map['chkk_orderid'] = array('in',$data['car_outside_checkid']);
                    $map['status']       = array('gt',0);
                    $car_outside_check = db('shop_check_order')->where($map)->field('*')->select();
                    
                    $return['car_outside_check']  = $car_outside_check;
                }
                if($data['car_inside_checkid']){
                    $map['order_sn']     = array('eq',$data['order_sn']);
                    $map['chkk_orderid'] = array('in',$data['car_inside_checkid']);
                    $map['status']       = array('gt',0);
                    $car_inside_check = db('shop_check_order')->where($map)->field('*')->select();
                   
                    $return['car_inside_check']  = $car_inside_check;
                }

                if($data['car_id']){
                    $map_car['car_id'] = $data['car_id']; 
                }elseif($data['car_plateno']){
                    $map_car['car_plateno'] = $data['car_plateno'];
                }
                $return['car_info']  = db('shop_car')->where($map_car)->field('*')->find();

            }elseif($data){
                $return     = $data->getData();
            }
            
        }

        return $return;
    }

    /**
     * 获取接车单信息，并按照所需信息分组
     *
     * @param array $where
     * @param string $field
     * @return void
     */
    public function get_shop_order_recp_info_group($where=array(),$field='*', $extend=false){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->field($field)->find();
            if($data && $extend){
                $return     = $data->getData();

                if($data['car_outside_checkid']){
                    $map['order_sn']     = array('eq',$data['order_sn']);
                    $map['chkk_orderid'] = array('in',$data['car_outside_checkid']);
                    $map['status']       = array('gt',0);
                    $car_outside_check = db('shop_check_order')->where($map)->field('*')->select();

                    $return['car_outside_check']  = $car_outside_check;
                }
                if($data['car_inside_checkid']){
                    $map['order_sn']     = array('eq',$data['order_sn']);
                    $map['chkk_orderid'] = array('in',$data['car_inside_checkid']);
                    $map['status']       = array('gt',0);
                    $car_inside_check = db('shop_check_order')->where($map)->field('*')->select();

                    $return['car_inside_check']  = $car_inside_check;
                }

                if($data['car_id']){
                    $map_car['car_id'] = $data['car_id'];
                }elseif($data['car_plateno']){
                    $map_car['car_plateno'] = $data['car_plateno'];
                }
                $return['car_info']  = db('shop_car')->where($map_car)->field('*')->find();

            }elseif($data){
                $return     = $data->getData();
            }
            //给客户看的job
            $temp_customer_service = model('common/ShopOrderJob')->get_order_job_by_jobsn($return['job_sn']);
            $return['customer_service'] = isset($temp_customer_service['order_service']) ? $temp_customer_service['order_service'] : array();
            //取得最后一个时间
            $return['customer_job_create_time'] = 0;
            foreach ($return['customer_service'] as $v){
                if(!isset($v['service_list']) || empty($v['service_list'])){
                    continue;
                }
                foreach ($v['service_list'] as $sv){
                    $return['customer_job_create_time'] = $sv['create_time'];
                }
            }

            //对内具体派工
            $temp_worker_service = model('common/ShopJob')->get_order_jobs_by_jobsn($return['job_sn']);
            $return['worker_service'] = isset($temp_worker_service['order_service']) ? $temp_worker_service['order_service'] : array();
            //取得最后一个时间
            $return['work_job_create_time'] = 0;
            foreach ($return['worker_service'] as $v){
                if(!isset($v['service_list']) || empty($v['service_list'])){
                    continue;
                }
                foreach ($v['service_list'] as $sv){
                    $return['work_job_create_time'] = $sv['create_time'];
                }
            }

            //支付信息
            $return['pay'] = array(
                'pay_type' => $return['pay_type'],
                'pay_status' => $return['pay_status'],
                'pay_remarks' => $return['pay_remarks'],
                'pay_price' => $return['pay_price'],
            );
            unset($return['pay_type']);
            unset($return['pay_status']);
            unset($return['pay_remarks']);
            unset($return['pay_price']);
        }

        return $return;
    }

    /**
    * 添加接车单信息
    *
    * @param array $post_data
    * @return void
    */
    public function shop_order_recp_save($post_data=[],$where=[]){
        //初始化
        $return = $map = array();

        if(!empty($post_data)){
            if($where){
                $result_data = $this->allowField(true)->save($post_data,$where);
            }else{
                $result_data = $this->allowField(true)->save($post_data);
            }
            
            if($result_data){
                $return = $this->carorder_id;
            }
        }
        return $return;
    }

    /**
     * @param array $where
     * @param string $field
     * 提供给前端客户看到的页面
     */
    public function get_shop_order_recp_info_fomat($where=array(),$field='*'){
//初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->field($field)->find();
            if($data){
                $return     = $data->getData();

                if($data['car_outside_checkid']){
                    $map['order_sn']     = array('eq',$data['order_sn']);
                    $map['chkk_orderid'] = array('in',$data['car_outside_checkid']);
                    $map['status']       = array('gt',0);
                    $car_outside_check = db('shop_check_order')->where($map)->field('*')->select();

                    $return['car_outside_check']  = $car_outside_check;
                }
                if($data['car_inside_checkid']){
                    $map['order_sn']     = array('eq',$data['order_sn']);
                    $map['chkk_orderid'] = array('in',$data['car_inside_checkid']);
                    $map['status']       = array('gt',0);
                    $car_inside_check = db('shop_check_order')->where($map)->field('*')->select();

                    $return['car_inside_check']  = $car_inside_check;
                }

                if($data['car_id']){
                    $map_car['car_id'] = $data['car_id'];
                }elseif($data['car_plateno']){
                    $map_car['car_plateno'] = $data['car_plateno'];
                }
                $return['car_info']  = db('shop_car')->where($map_car)->field('*')->find();

                //查询接待人员
                $reper = model('common/ShopUser')->where(array('shop_user_id'=>$data['shop_user_id']))->field('nickname,urealname,utel,headimgurl')->find();
                if(!$reper){
                    $return['recpter'] = array('nickname'=>'','urealname'=>'','utel'=>'','headimgurl'=>'');
                }else{
                    $return['recpter'] = $reper->toArray();
                }

                //查询照片
                $model_check = model('common/ShopCheckOrder');
                $imgs = $model_check->where(array('order_sn'=>$data['order_sn']))->field('chkorder_img')->select();
                $img_arr = array();
                foreach($imgs as &$v){
                    $img_temp_ = array_filter(explode(',', $v->chkorder_img));
                    foreach($img_temp_ as &$path){
                        $img_arr[] = $path;
                    }
                }
                $return['recpte_img'] = $img_arr;

                //查询对应服务项目
                $model_job = model('common/ShopOrderJob');
                $jobs = $model_job->where(array('carorder_id'=>$data['carorder_id'], 'status'=>1))->field('*')->order('job_type ASC,job_id ASC')->select();
                $return['jobs'] = $jobs ? collection($jobs)->toArray() : array();
                $return['jobs_str'] = '';
                foreach($return['jobs'] as &$v){
                    $return['jobs_str'] .= $v['service_name'].'、';
                }

            }elseif($data){
                $return     = $this->dataFormat($data->getData());
            }
        }
        return $this->dataFormat($return);
    }

    /**
     * @param $carorder_id
     * 作废接车单，将接车单改为已删除
     */
    public function cancalOrderRecp($carorder_id){
        if(!$carorder_id){
            return false;
        }
        $map = array();
        $map['carorder_id'] = $carorder_id;
        $result = $this->where($map)->setField('order_status', -1);
        if($result){
            return true;
        }
        return false;
    }

    /**
     * @param $order_sn
     * @param $customer_id
     * @return array
     * 客户确认接车单状态
     */
    public function customerConfirm($order_sn, $customer_id){
        $order_sn = trim($order_sn);
        if(!$order_sn){
            return array('', -1, '接车单号错误');
        }
        $customer = model('common/Customer')->getCustomer($customer_id);
        if(!$customer){
            return array('', -1,  '客户不存在');
        }
        if($customer->is_status < 0){
            return array('', -1, '客户被禁用');
        }
        $recp = $this->where(array('order_sn'=>$order_sn))->field('*')->find();
        if($recp->order_status <= 0){
            return array('', -1,  '接车单不可用');
        }
        if($recp->customer_id != $customer_id){
            return array('', -1, '该单不属于你，不能确认');
        }
        if($recp->order_status < \app\api\controller\ShopOrderRecp::$order_status['recepter_confirm'] || $recp->order_status > \app\api\controller\ShopOrderRecp::$order_status['customer_confirm']){
            return array('', -1, '当前接车单状态不允许确认');
        }
        $result = $this->where(array('order_sn'=>$order_sn))->setField(array('order_status'=>\app\api\controller\ShopOrderRecp::$order_status['customer_confirm'], 'customer_confirm_status'=>1));
        if(!$result){
            return array('', -1, '确认失败');
        }
        return array('', 0,  '客户已确认');
    }

    /**
     * @param string $job_sn
     * 当客户的派工单录入后，用于更新接车单价格
     * 更新：材料价格、服务价格、总价、待支付价格(材料+服务-折扣)
     */
    public function calculate_recp_price($job_sn=''){
        $map = array();
        $map['job_sn'] = $job_sn;
        $recp = $this->where($map)->find();
        if(!$recp){
            return array('', -1, '接车单不存在');
        }
        if($recp['pay_status']){
            return array('', -1, '该单已经付款，不允许修改价格');
        }
        //查找材料和服务的报价，循环计算价格
        $model_shop_order_job = model('common/ShopOrderJob');
        $map['status'] = 1;
        $customer_order_job = $model_shop_order_job->where($map)->select();
        $customer_order_job = $customer_order_job ? collection($customer_order_job)->toArray() : array();
        $service_total_price = 0;
        $material_total_price = 0;
        foreach($customer_order_job as &$v){
            if($v['job_type'] == 0){
                //服务
                $service_total_price += $v['server_totalprice'];
            }else if($v['job_type'] == 1){
                //材料
                $material_total_price += $v['server_totalprice'];
            }
        }
        //计算此时的总价、待支付价格，待支付价格 = 总价 = 服务费 + 材料费
        $pay_price = $all_price = $service_total_price + $material_total_price;

        //更新主表
        $update_array = array(
            'pay_price' => $pay_price,
            'all_price' => $all_price,
            'material_list_price' => $material_total_price,
            'service_list_price' => $service_total_price,
        );
        $this->where(array('job_sn'=>$job_sn))->setField($update_array);
        return array('', 0, '价格已更新');
    }

    /**
     * @param $order_sn
     * @param $uid
     * 付款
     */
    public function pay($order_sn, $uid){
        $order_sn = trim($order_sn);
        if(!$order_sn){
            return array('', -1, '接车单号错误');
        }
        if(!$uid){
            return array('', -1, '当前用户不存在');
        }
        $map = array();
        $map['order_sn'] = $order_sn;
        $rep = $this->where($map)->field('*')->find();
        if(!$rep){
            return array('', -1, '接车单不存在');
        }
        $rep = $rep->toArray();
        //检测支付状态
        if($rep['pay_status']){
            return array('', -1, '该单已付款，不需要重新操作');
        }
        if($rep['customer_id'] != $uid){
//            return array('', -1, '只有该单用户可以操作');
        }
        $orderNo = 'C'.date('ymdHis').mt_rand(100000, 999999);
        $payData = array(
            'subject' => '接车单号：'.$rep['order_sn'],
            'body' => '接车单号：'.$rep['order_sn'],
            'orderNo' => $orderNo,
            'pay_type' => 'wx_pub',
            'amount' => $rep['pay_price'] ? floatval($rep['pay_price']) : floatval($rep['pay_price']),
            'order_type' => 2,
            'return_param' => array('order_sn'=>$rep['order_sn'])
        );
        $return = controller('pay/Payment')->getPayData($payData);
        return array($return, 0, '');
    }

    private function dataFormat(&$data){
        if(isset($data['create_time'])){
            $data['create_time_str'] = date('m-d H:i', $data['create_time']);
        }
        if(isset($data['order_deadline'])){
            $data['order_deadline_str'] = date('m-d H:i', $data['order_deadline']);
        }
        return $data;
    }
}