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
// | @Description 文件描述： 接待订单服务选择相关接口

namespace app\api\controller;

use EasyWeChat\Foundation\Application;
use think\Cache;
use app\common\controller\ApiBase;

class ShopOrderJob extends ApiBase
{
	//定义参数
	protected $model_shop_order_recp;
	protected $model_shop_order_job;

	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();

		$this->model_shop_order_recp = model('common/ShopOrderRecp');
		$this->model_shop_order_job = model('common/ShopOrderJob');
	}
	
	/**
	 * 默认方法
	 *
	 * @return void
	 */
	public function index(){
		$result = array();
		
		return $this->sendResult($result);
	}

	/**
	 * 通过工单job_sn获取服务工单详情
	 *
	 * @param string $job_sn
	 * @return void
	 */
	public function getOrderJobInfo($job_sn=''){
		//初始化
		$return = $result = $post_data = $data = array();
		$message = 'success';
		$errcode = 0;
		
		//获取参数
		$job_sn = input('job_sn',$job_sn,'string');

		if($job_sn){
			$map['job_sn'] = array('eq',$job_sn);
			$data = $this->model_shop_order_job->get_order_job_by_jobsn($job_sn);
			if($data){
				$return = $data;
			}else{
				$errcode = 1;
				$message = '订单服务信息不存在';
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}


	/**
	 * 接车单检查信息入库
	 *
	 * @return void
	 */
	public function addOrderCustomerJob($inupt_data=array()){
		//初始化
		$return = $result = $post_data = $data = array();
		$message = 'success';
		$errcode = 0;
		
		if(request()->isPost()){
			//获取输入参数
			$post_data  = input('post.',$inupt_data);
			$shop_user_id = $this->shop_user_id;

			if(!empty($post_data['shop_id']) && (!empty($post_data['job_sn']) || !empty($post_data['order_sn']))){
				//查询订单是否存在
				if($post_data['job_sn']){
					$map['job_sn'] = array('eq',$post_data['job_sn']);
				}elseif($post_data['order_sn']){
					$map['order_sn'] = array('eq',$post_data['order_sn']);
				}
				$order_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($map,'*');
				if($order_recp_data){
					$post_data = array_merge($order_recp_data,$post_data);
					//更新价格等附加字段到接单订单表
					$recp_data = $this->model_shop_order_recp->shop_order_recp_save($post_data,$map);
					
					//服务项
					$service_list  = $post_data['service_list'];
					//材料项
					$material_list = $post_data['material_list'];
					
					//把服务项目批量添加进入记录表
					if($order_recp_data['job_sn'] && is_array($service_list)){
						$return_service_data = $this->model_shop_order_job->shop_check_job_saveall($service_list,$order_recp_data);
					}

					//把材料项目批量添加进入记录表
					if($order_recp_data['job_sn'] && is_array($material_list)){
						$return_material_data = $this->model_shop_order_job->shop_check_job_saveall($material_list,$order_recp_data);
					}

					//获取自增主键
					$job_sn = $order_recp_data['job_sn'];
					if($job_sn){
                        //更新接车单价格，待支付价格、总价、服务费、材料费
                        $this->model_shop_order_recp->calculate_recp_price($job_sn);
						$return = $this->model_shop_order_job->get_order_job_by_jobsn($job_sn);
                        //更新接车单状态为接待已确认
                        $this->model_shop_order_recp->where(array('carorder_id'=>$order_recp_data['carorder_id']))->setField('order_status', ShopOrderRecp::$order_status['recepter_confirm']);
                        //客户已经确认
                        $this->model_shop_order_recp->customerConfirm($order_recp_data['order_sn'], $this->customer_id);
                        //需要派工
                        $this->model_shop_order_recp->where(array('carorder_id'=>$order_recp_data['carorder_id']))->setField('order_status', ShopOrderRecp::$order_status['need_work']);
                    }else{
						$errcode = 1;
						$message = '订单服务保存失败';
					}
				}else{
					$errcode = 1;
					$message = '所属接待订单不存在';
				}
			}else{
				$errcode = 1;
				$message = '请求操作不合法';
			}

		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}


	/**
	 * 添加接待订单的子订单
	 *
	 * @param array $inupt_data
	 * @return void
	 */
	public function addSubOrderCustomerJob($inupt_data=array()){
		//初始化
		$return = $post_data = $sub_data = $data = array();
		$message = 'success';
		$errcode = 0;
		
		if(request()->isPost()){
			//获取输入参数
			$post_data  = input('post.',$inupt_data);
			$shop_user_id = $this->shop_user_id;

			if(!empty($post_data['shop_id']) && !empty($post_data['order_psn'])){
				//获取父级接待订单
				$field = 'carorder_id,order_pid,job_sn,car_id,car_plateno,car_vin,car_mileage,car_oil,carorder_remarks,car_outside_checkid,car_outside_remarks,car_inside_checkid,car_inside_remarks,customer_name,customer_sex,customer_tel,customer_id,shop_id,shop_group_id,shop_user_id,order_debit,order_identity_customer,order_identity_car,order_remarks_customer';
				$p_order_recp_info = $this->model_shop_order_recp->get_shop_order_recp_info($whrer=['order_sn'=>$post_data['order_psn']],$field);
				if($p_order_recp_info){

					$post_data['order_pid'] = $p_order_recp_info['carorder_id'];
					unset($p_order_recp_info['carorder_id'],$p_order_recp_info['order_pid'],$p_order_recp_info['order_sn'],$p_order_recp_info['job_sn'],$p_order_recp_info['pay_price'],$p_order_recp_info['material_list_price'],$p_order_recp_info['service_list_price'],$p_order_recp_info['all_price'],$p_order_recp_info['order_discount'],$p_order_recp_info['order_allprice_discount'],$p_order_recp_info['pay_status'],$p_order_recp_info['pay_type']);

					$post_data = array_merge($post_data,$p_order_recp_info);

					//调用模型方法入库
//					$post_data['shop_user_id'] = $shop_user_id;
					$order_recp_id = $this->model_shop_order_recp->shop_order_recp_save($post_data);
					if ($order_recp_id !== false) {
						$carorder_id = $order_recp_id;
						$order_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($whrer=['carorder_id'=>$carorder_id],$field);
						if($order_recp_data){
							$post_data = array_merge($post_data,$order_recp_data);

							//服务项
							$service_list  = $post_data['service_list'];
							//材料项
							$material_list = $post_data['material_list'];
							
							//把服务项目批量添加进入记录表
							if($order_recp_data['job_sn'] && is_array($service_list)){
								$return_server_data = $this->model_shop_order_job->shop_check_job_saveall($service_list,$order_recp_data);
							}

							//把材料项目批量添加进入记录表
							if($order_recp_data['job_sn'] && is_array($material_list)){
								$return_material_data = $this->model_shop_order_job->shop_check_job_saveall($material_list,$order_recp_data);
							}

							//获取自增主键
							$job_sn = $order_recp_data['job_sn'];
							if($job_sn){
//                                dump($order_recp_data);
                                //查询父亲信息，这里先查询出所有信息
                                $p_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($whrer=['carorder_id'=>$order_recp_data['order_pid']],'*');
                                //这里的程序使用ShopOrderRecp/getOrderRecpList的格式
                                //子单包含job信息
                                $order_recp_data['job'] = $this->model_shop_order_job->get_order_job_by_jobsn($job_sn);
                                //获取子订单
                                $map_sub['order_pid']    = $order_recp_data['carorder_id'];
                                $map_sub['order_status'] = ['egt',0];
                                $p_recp_data['chilren'] = $order_recp_data;
                                $return = $p_recp_data;
							}
						}
					}else{
						$errcode = 1;
						$message = '子订单添加失败';
					}
				}else{
					$errcode = 1;
					$message = '关联订单不存在';
				}		
			}else{
				$errcode = 1;
				$message = '请求操作不合法';
			}
			
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}

	

}