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
// | @Description 文件描述： 

namespace app\api\controller;

use EasyWeChat\Foundation\Application;
use think\Cache;
use app\common\controller\ApiBase;

class ShopOrder extends ApiBase
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
		$this->model_shop_order_discount_log = model('common/ShopOrderDiscountLog');
		$this->model_shop_order_pay_log = model('common/ShopOrderPayLog');
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
	 * 接待订单申请折扣
	 *
	 * @param string $order_sn
	 * @param string $discount
	 * @return void
	 */
	public function applyOrderDiscount($order_sn='',$discount=''){
		//初始化
		$return = $result = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		//获取输入参数
		$order_sn = input('order_sn',$order_sn,'string');
		$discount = input('discount',$discount,'intval');

		if($order_sn && $discount){
			$map['order_sn']     = array('eq',$order_sn);
			$map['order_status'] = array('egt',0);
			
			//查询订单信息
			$oder_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($map);
			$discount = [];
			if($oder_recp_data){
				//折扣记录处理
				$discount = [
					'carorder_id' => $oder_recp_data['carorder_id'],
					'order_sn' => $oder_recp_data['order_sn'],
					'job_sn' => $oder_recp_data['job_sn'],
					'material_list_price' => $oder_recp_data['material_list_price'],
					'service_list_price' => $oder_recp_data['service_list_price'],
					'all_price' => $oder_recp_data['all_price'],
					'shop_user_id' => $oder_recp_data['shop_user_id'],
					'shop_id' => $oder_recp_data['shop_id'],
					'shop_group_id' => $oder_recp_data['shop_group_id'],
					//折扣处理
					'order_discount' => $discount,
					'order_allprice_discount' => $oder_recp_data['material_list_price'] + ($oder_recp_data['service_list_price'] * ($discount/100)),
				];

				$result_discount = $this->model_shop_order_discount_log->shop_order_discount_log_save($discount);
				if($result_discount != false){
					$log_id = $result_discount;

					$result = $this->model_shop_order_discount_log->get_shop_order_discount_log_info($where=['log_id'=>$log_id]);
				}else{
					$errcode = 1;
					$message = '折扣申请失败';
				}
			}else{
				$errcode = 1;
				$message = '订单不存在或已作废';
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}
		
		return $this->sendResult($result,$errcode,$message);
	}

	/**
	 * 申请折扣审批
	 *
	 * @param string $log_id
	 * @param integer $shop_user_id
	 * @param integer $discount_status
	 * @param string $discount_tips
	 * @return void
	 */
	public function approveOrderDiscount($log_id='',$shop_user_id=0,$discount_status=0,$discount_tips=''){
		//初始化
		$return = $result = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		//获取输入参数
		$log_id          = input('log_id',$log_id,'intval');
		$shop_user_id    = input('shop_user_id',$shop_user_id,'intval');
		$discount_status = input('discount_status',$discount_status,'intval');
		$discount_tips   = input('discount_tips',$discount_tips,'string');

		if($log_id && $shop_user_id && $discount_status){
			$map['log_id']       = array('eq',$log_id);
			//查询订单信息
			$oder_order_discount_info = $this->model_shop_order_discount_log->get_shop_order_discount_log_info($map);
			if($oder_order_discount_info){
				//折扣记录处理
				$discount_arr = [
					'log_id'          => $oder_order_discount_info['log_id'],
					'discount_tips'   => $discount_tips,
					'discount_status' => $discount_status,
				];
				//审批人权限检查
				if($oder_order_discount_info['confirm_user_id'] == $shop_user_id){
					$result_discount = $this->model_shop_order_discount_log->shop_order_discount_log_save($discount_arr,$map);
					if($result_discount){

						$log_id = $oder_order_discount_info['log_id'];
						$result = $this->model_shop_order_discount_log->get_shop_order_discount_log_info($where=['log_id'=>$log_id]);

						if($discount_status == 1){
							//更新接待订单主记录表
							$map_order_recp['order_sn'] = $oder_order_discount_info['order_sn'];
							$order_recp_info = $this->model_shop_order_recp->get_shop_order_recp_info($map_order_recp);
							if($order_recp_info){
								//材料费不可以打折，工时费可以打折
								$order_allprice_discount = $order_recp_info['material_list_price'] + ($order_recp_info['service_list_price'] * ($oder_order_discount_info['order_discount']/100));
								$update_order_recp = [
									'order_discount'          => $oder_order_discount_info['order_discount'],
									'order_allprice_discount' => $order_allprice_discount,
									'pay_price'				  => $order_allprice_discount,
								];
								
								$update_order_status = $this->model_shop_order_recp->shop_order_recp_save($update_order_recp,$map_order_recp);
								if($update_order_status){
									$errcode = 0;
									$message = '折扣申请审批成功，且订单更新成功';
								}
							}
						}
					}else{
						$errcode = 1;
						$message = '折扣申请审批失败';
					}
				}else{
					$errcode = 1;
					$message = '审批人不正确，操作非法';
				}
			}else{
				$errcode = 1;
				$message = '申请折扣信息不存在';
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}
		
		return $this->sendResult($result,$errcode,$message);
	}

	/**
	 * 通过order_sn获取接待订单的进度信息
	 *
	 * @param string $job_sn
	 * @return void
	 */
	public function getOrderProgress($job_sn=''){
		//初始化
		$return = $result = $post_data = $data = $job_list = array();
		$message = 'success';
		$errcode = 0;
		
		//获取参数
        $job_sn = input('job_sn',$job_sn,'string');

		if($job_sn){
			$map['job_sn'] = array('eq',$job_sn);
			$oder_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($map,$field='*');
			if($oder_recp_data){
				$job_sn = $oder_recp_data['job_sn'];
				$result['recp_info'] = $oder_recp_data;

				//获取sg_id组信息
				if($job_sn){
					//派工组的工作信息
					$model_shop_job = model('common/ShopJob');
					//这里的派工组应该是cys_shop_user_group
					$server_group   = $model_shop_job->where($where=['job_sn'=>$job_sn])->group('work_group_id')->column('sg_id,job_remarks,work_group_id,status');
					if($server_group){
						foreach($server_group as $key=>$val){
							$worker_group_info['worker_group_id']    = $val['work_group_id'];
							$worker_group_info['worker_group_name']  = get_model_data($model='shop_user_group',$id=$val['work_group_id'],$field='group_name',$field_where='group_id');
							$worker_group_info['worker_user_name']   = get_model_data($model='shop_user',$id=$val['confirm_id'],$field='urealname',$field_where='shop_user_id');
                            $worker_group_info['worker_user_name'] = $worker_group_info['worker_user_name'] ? $worker_group_info['worker_user_name'] : '';
                            $worker_group_info['worker_leader_name'] = model('common/ShopUserGroup')->getLeaderInfo($val['work_group_id'], 'urealname');
                            $worker_group_info['worker_leader_name'] = $worker_group_info['worker_leader_name']['urealname'];
                            $worker_group_info['worker_status']      = $val['status'];
                            $worker_group_info['job_sn'] = $result['recp_info']['job_sn'];

							$job_list[] = $worker_group_info;
						}
					}
				}
				$result['job_list'] = $job_list;
				
				//获取支付信息
				$pay_info = [
					'pay_type'   =>  $data['pay_type'],
					'pay_status' =>  $data['pay_status'],
					'pay_price'  =>  $data['pay_price'],
				];
				$result['pay_info'] = $pay_info;

			}else{
				$errcode = 1;
				$message = '所属接待订单信息不存在';
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($result,$errcode,$message);
	}


	/**
	 * 添加支付信息
	 *
	 * @param array $inupt_data
	 * @return void
	 */
	public function addPayInfo($inupt_data=array()){
		//初始化
		$return = $result = $post_data = $data = array();
		$message = 'success';
		$errcode = 0;
		
		if(request()->isPost()){
			//获取输入参数
			$post_data  = input('post.',$inupt_data);
			$order_sn   = $post_data['order_sn'];
			if(!empty($order_sn ) && !empty($post_data['pay_type'])){
				$map['order_sn'] = array('eq',$order_sn);
				$oder_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($map,$field='*');
				if($oder_recp_data){
					//支付记录处理
					$pay_data = [
						'carorder_id'         => $oder_recp_data['carorder_id'],
						'order_sn'            => $oder_recp_data['order_sn'],
						'material_list_price' => $oder_recp_data['material_list_price'],
						'service_list_price'  => $oder_recp_data['service_list_price'],
						'all_price'           => $oder_recp_data['all_price'],
						'shop_user_id'        => $oder_recp_data['shop_user_id'],
						'shop_id'             => $oder_recp_data['shop_id'],
						'shop_group_id'       => $oder_recp_data['shop_group_id'],
						//折扣处理
						'order_discount'      => $oder_recp_data['order_discount'],
						'order_allprice_discount' => $oder_recp_data['order_allprice_discount'],

						'pay_type'            => $post_data['pay_type'],
						'pay_amount'          => $post_data['pay_amount'],
						'pay_image'           => $post_data['pay_image'],
						'pay_bill_no'         => $post_data['pay_bill_no'],
						'pay_remarks'         => $post_data['pay_remarks'],
						'pay_status'          => 1,
					];

					$result_pay = $this->model_shop_order_pay_log->shop_order_pay_log_save($discount);
					if($result_pay != false){
						$pay_id = $result_pay;

						$update_order_recp = [
							'pay_type'            => $post_data['pay_type'],
							'pay_amount'          => $post_data['pay_amount'],
							'pay_image'           => $post_data['pay_image'],
							'pay_bill_no'         => $post_data['pay_bill_no'],
							'pay_remarks'         => $post_data['pay_remarks'],
							'pay_status'          => 1,
						];

						$result_update = $this->model_shop_order_recp->shop_order_recp_save($update_order_recp,$where=['order_sn'=>$order_sn]);
						if($result_update != false){
							$return = $this->model_shop_order_pay_log->get_shop_order_pay_log_info($whrer=['pay_id'=>$pay_id]);
						}
					}else{
						$errcode = 1;
						$message = '支付记录添加失败';
					}
				}else{
					$errcode = 1;
					$message = '所属订单信息不存在';
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
	 * 接待员对整个接车单进行确认交车
	 *
	 * @param string $order_sn
	 * @param integer $shop_user_id
	 * @return void
	 */
	public function confirmOrderRecp($job_sn='',$shop_user_id=0){
		//初始化
		$return = $result = $post_data = $data = [];
		$message = 'success';
		$errcode = 0;
		
		if(request()->isPost()){
			//获取输入参数
			$job_sn        = input('post.job_sn',$job_sn,'string');
			$shop_user_id  = input('post.shop_user_id',$shop_user_id,'intval');
			$work_group_id = input('post.work_group_id',0,'intval');

			if($job_sn && $shop_user_id){
				$order_info = $this->model_shop_order_recp->get_shop_order_recp_info($where=['job_sn'=>$job_sn]);
				if($order_info && $order_info['shop_user_id'] == $shop_user_id){
					$status = ShopOrderRecp::$order_status['car_gone'];
					//为了方便测试，先跳过该步骤
					/*if(($order_info['order_status'] >= 0) && ($order_info['pay_status'] > 0)){
						$result_data = $this->model_shop_order_recp->where('order_sn',$order_info['order_sn'])->setField('order_status', $status);
						if($result_data){
							$return = $order_info;
						}else{
							$errcode = 1;
							$message = '订单确认交车失败';
						}
					}else{
						$errcode = 1;
						$message = '订单未支付不能进行确认交车';
					}*/
					$result_data = $this->model_shop_order_recp->where('order_sn',$order_info['order_sn'])->setField('order_status', $status);
					if($result_data){
						$message = '确认成功';
						$return = $this->model_shop_order_recp->get_shop_order_recp_info($where=['job_sn'=>$job_sn]);;
					}else{
						$errcode = 1;
						$message = '已确认过该步骤';
					}
				}else{
					$errcode = 1;
					$message = '非法操作或订单信息不存在';
				}
			}else{
				$errcode = 1;
				$message = '不合法参数';
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}
	

}