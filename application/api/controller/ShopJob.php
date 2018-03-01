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

use think\Cache;
use app\common\controller\ApiBase;

class ShopJob extends ApiBase
{
	//定义参数
	protected $model_shop_order_recp;
	protected $model_shop_order_job;

    public static $job_status = array(
        'need_recept' => 1, //未接任务
        'recpted' => 20, //已接任务
        'leader_no_confirm' => 30, //组长未确认
        'leader_no_pass' => 31, //组长确认不通过
        'leader_pass' => 32, //组长确认通过
        'recepter_no_confirm' => 40, //接待未确认
        'recepter_no_pass' => 41, //接待确认不通过
        'recepter_pass' => 42 //接待确认
    );

	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();

		$this->model_shop_order_recp = model('common/ShopOrderRecp');
		$this->model_shop_job = model('common/ShopJob');
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
	 * 通过工单job_sn获取派工单服务工单详情
	 *
	 * @param string $job_sn
	 * @return void
	 * 12-29曹瑞修改订单不存在提示
	 */
	public function getOrderWorkerJob($job_sn=''){
		//初始化
		$return = $result = $post_data = $data = array();
		$message = 'success';
		$errcode = 0;
		
		//获取参数
		$job_sn = input('job_sn',$job_sn,'string');

		if($job_sn){
			$map['job_sn'] = array('eq',$job_sn);
			$data = $this->model_shop_job->get_order_worker_by_jobsn($job_sn);
			if($data){
				$return = $data;
			}else{
				$errcode = 1;
//				$message = '订单服务信息不存在';
				$message = '该单没有具体派工';
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}
	
	/**
	 * 派工单确认提交操作
	 *
	 * @param array $inupt_data
	 * @return void
     * 12月26日 曹瑞增加重复提交判断逻辑
	 */
	public function addOrderWorkerJob($inupt_data=[]){
		//初始化
		$return = $result = $post_data = $data = [];
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
				if($order_recp_data['order_status'] < 1){
					return $this->sendResult(array(),-1,'该单已被废弃，不可派工');
				}
				if($order_recp_data['order_status'] < ShopOrderRecp::$order_status['customer_confirm']){
					return $this->sendResult(array(),-1,'客户还未确认，不可派工');
				}
                //不能重复派工
                if($order_recp_data['order_status'] >= ShopOrderRecp::$order_status['working']){
                    $errcode = 1;
                    $message = '不能重复派工';
                }else{
                    if($order_recp_data){
                        //获取自增主键
                        $job_sn = $order_recp_data['job_sn'];
                        $post_data = array_merge($order_recp_data,$post_data);

                        //外观检查项
                        $service_list  = $post_data['service_list'];
                        //功能检查项
                        $material_list = $post_data['material_list'];
                        //把检查项目批量添加进入记录表
                        if($order_recp_data['job_sn'] && is_array($service_list)){
                            $return_service_data = $this->model_shop_job->shop_check_job_saveall($service_list,$order_recp_data);
                        }

                        //把检查项目批量添加进入记录表
                        if($order_recp_data['job_sn'] && is_array($material_list)){
                            $return_material_data = $this->model_shop_job->shop_check_job_saveall($material_list,$order_recp_data);
                        }

                        //派单成功更新接待表字段
                        if($return_service_data || $return_material_data){
                            $result = $this->model_shop_order_recp->where(array('job_sn'=>$job_sn))->setField('order_status', ShopOrderRecp::$order_status['working']);
                            $return = $this->model_shop_job->get_order_worker_by_jobsn($job_sn,'*',true);
                        }else{
                            $errcode = 1;
                            $message = '派工单失败';
                        }
                    }else{
                        $errcode = 1;
                        $message = '所属接待订单信息不存在';
                    }
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
	 * 组长确认工单信息修改状态
	 *
	 * @param string $job_sn
	 * @param integer $shop_user_id
	 * @param integer $work_group_id
	 * @return void
	 */
	public function confirmWorkerJobGroupLeader($job_sn='',$shop_user_id=0,$work_group_id=0){
		//初始化
		$return = $result = $post_data = $data = [];
		$message = 'success';
		$errcode = 0;

		if(request()->isPost()){
			//获取输入参数
			$job_sn        = input('post.job_sn',$job_sn,'string');
			$shop_user_id  = input('post.shop_user_id',$shop_user_id,'intval');
            $shop_user_id  = $this->shop_user_id;
			$work_group_id = input('post.work_group_id',$work_group_id,'intval');
			if($job_sn && $shop_user_id){
				$job_list = $this->model_shop_job->get_shop_job_list($where=['job_sn'=>$job_sn, 'work_group_id'=>$work_group_id],$limit=100,$order='job_id desc',$field='*');
				if($job_list){
					foreach($job_list as $key=>$val){
						if($val['confirm_id'] == $shop_user_id){
							$status = 0;
							if($val['status'] == 1){
								$status = 20;
							}elseif($val['status'] == 20){
								$status = 32;
							}
							$result = $this->model_shop_job->where('job_id',$val['job_id'])->setField('status', $status);
						}else{
							continue;
						}
					}
					$message = '操作成功';
					$return = empty($result) ? array('temp'=>'') : $result;
				}else{
					$errcode = 1;
					$message = '工单信息不存在';
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
	
    /**
	 * 接待员对每个工种组进行确认
	 *
	 * @param string $job_sn
	 * @param integer $shop_user_id
	 * @param integer $work_group_id
	 * @return void
	 */
	public function confirmWorkerJobRecp($job_sn='',$shop_user_id=0,$work_group_id=0){
		//初始化
		$return = $result = $post_data = $data = [];
		$message = 'success';
		$errcode = 0;

		if(request()->isPost()){
			//获取输入参数
			$job_sn        = input('post.job_sn',$job_sn,'string');
			$shop_user_id  = input('post.shop_user_id',$shop_user_id,'intval');
			$shop_user_id  = $this->shop_user_id;
			$work_group_id = input('post.work_group_id',$work_group_id,'intval');
			if($job_sn && $shop_user_id && $work_group_id ){
				$job_list = $this->model_shop_job->get_shop_job_list($where=['job_sn'=>$job_sn, 'work_group_id'=>$work_group_id],$limit='',$order='job_id desc',$field='*');
				if($job_list){
					foreach($job_list as $key=>$val){
						if($val['shop_user_id'] != $shop_user_id){
							continue;
						}
						if($val['status'] == 32){
							$status = 42;
							$result = $this->model_shop_job->where('job_id',$val['job_id'])->setField('status', $status);
						}else{
							continue;
						}
					}
					$message = '操作成功';
					$return = empty($result) ? array('temp'=>'') : $result;
				}else{
					$errcode = 1;
					$message = '工单信息不存在';
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