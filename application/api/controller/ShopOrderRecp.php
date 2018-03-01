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
// | @Description 文件描述： 订单接待单

namespace app\api\controller;

use EasyWeChat\Foundation\Application;
use think\Cache;
use app\common\controller\ApiBase;

class ShopOrderRecp extends ApiBase
{
	//定义参数
	protected $model_shop_order_recp;
	protected $model_shop_car,$model_shop_check_order;

    //接车单状态
    public static $order_status = array(
	    'cancel'=>-1,
        'recept' => 1, //接车
        'recepter_confirm' => 10, //接待确认
        'customer_confirm' => 20, //客户确认
        'need_work' => 30, //未派单
        'working' => 31, //已派未完成
        'done' => 32, //已派已完成
        'repaired_recpter_confirm' => 40, //修完接待确认
        'car_gone' => 50, //已提车
        'finish' => 100 //已完成
    );

	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();

		$this->model_shop_order_recp  = model('common/ShopOrderRecp');
		$this->model_shop_car         = model('common/ShopCar');
		$this->model_shop_check_order = model('common/ShopCheckOrder');
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
	 * 获取每个接带人员自己的接车单
	 * 0：全部 5：作废 10：未派 20：已派
	 * @param string $shop_user_id
	 * @param integer $limit
	 * @param integer $type
	 * @param integer $id
	 * @param string $oder
	 * @param string $field
	 * @return array
	 */
	public function getOrderRecpList($order_status=0,$page=0,$page_size=10,$shop_user_id=0){
		//初始化
		$return = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		//获取参数
		$order_status = input('order_status',$order_status,'intval');
		$page         = input('page',$page,'intval');
		$page_size    = input('page_size',$page_size,'intval');
		$shop_user_id = input('shop_user_id',$shop_user_id,'intval');

		
		//根据接待人获取自己的接待单
		if($shop_user_id){
			//查询条件
			$map['shop_user_id'] = array('eq',$shop_user_id);
			//$map['order_pid']    = array('eq',0);
			
			//订单状态判断
			switch ($order_status) {
				case '0':
					//全部
					$map['order_status']=array('gt',0);
					break;
				case '10':
					//未派
					$map['order_status']=array('eq',self::$order_status['need_work']);
					break;
				case '20':
					//已派，包括已派已完成和已派未完成
					$map['order_status']=array('in',array(self::$order_status['working'],self::$order_status['done']));
					break;
				case '5':
					//作废
					$map['order_status']=array('eq',self::$order_status['cancel']);
					break;
				default:
					break;
			}
			//调取模型方法
			$recp_list = $this->model_shop_order_recp->get_shop_order_recp_list($map,$page,$page_size);
			if($recp_list){
				$return = $recp_list;
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}

	/**
	 * 获取订单信息
	 *
	 * @param string $carorder_id
	 * @param string $field
	 * @return array
	 */
	public function getorderRecpInfo($order_sn='',$job_sn='',$field='*'){
		//初始化
		$return = $data = $map = array();
		$message = 'success';
		$errcode = 0;
		
		//获取参数
		$order_sn = input('order_sn',$order_sn,'string');
		$job_sn = input('job_sn',$job_sn,'string');
		
		//获取每个接车单的信息
		if($order_sn || $job_sn){
			if($order_sn){
				$map['order_sn'] = array('eq',$order_sn);
			}
			if($job_sn){
				$map['job_sn'] = array('eq',$job_sn);
			}
			$data = $this->model_shop_order_recp->get_shop_order_recp_info_group($map,'*',true);


			if($data){
				$return = $data;
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}

	/**
	 * 通过订单sn获取订单二维码
	 *
	 * @param string $order_sn
	 * @return void
	 */
	public function getQRcodeByOrdersn($order_sn=''){
		//初始化
		$return = $result_data = $data = $map = [];
		$message = 'success';
		$errcode = 0;

		//获取输入参数
		$order_sn = input('order_sn',$order_sn,'string');
		if($order_sn){
			$map['order_sn'] = array('eq',$order_sn);
			//查询订单信息
			$data = $this->model_shop_order_recp->get_shop_order_recp_info($map);
			if($data){
				//进行二维码生成
				$wechatqr_info = controller('WechatQr')->createQr('order_sn|'.$order_sn, 3600);
				if($wechatqr_info){
					$result_data['qrurl'] = $wechatqr_info->qrurl;
					$result_data['expire'] = $wechatqr_info->expire_seconds;
				}
				$return = $result_data;
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}
		
		return $this->sendResult($return,$errcode,$message);
	}

	/**
	 * 更新接车单状态目前默认为只可将未派单的接车单作废
	 *
	 * @param string $order_sn
	 * @param integer $status_type
	 * @return void
	 */
	public function changeOrderStatus($order_sn='',$status_type=0){
		//初始化
		$return = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		//获取输入参数
		$order_sn = input('order_sn',$order_sn,'string');
		if($order_sn){
			$map['order_sn']     = array('eq',$order_sn);
			$map['order_status'] = array('lt',2);
			$map['pay_status']   = array('eq',0);
			
			//根据条件进行设置接待订单状态
			$data = $this->model_shop_order_recp->where($map)->setField('order_status','-1');
			if($data){
				//进行二维码生成
				$return = $data;
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
	 * @param array $inupt_data
	 * @return void
	 */
	public function addOrderRecp($inupt_data=array()){
		//初始化
		$return = $post_data = $data = array();
		$message = 'success';
		$errcode = 0;
		
		if(request()->isPost()){
			//获取输入参数
			$post_data  = input('post.',$inupt_data);
			$shop_user_id = $this->shop_user_id;

            //if(!empty($post_data['shop_id']) && (!empty($post_data['car_plateno']) || !empty($post_data['car_vin'])))
			if(!empty($post_data['shop_id']) && (!empty($post_data['car_plateno']))){
				
				//判断车辆信息不为空进行车辆信息入库
				if(!empty($post_data['car_plateno'])){
					//判断车辆信息是否存在
					$shop_car_info = $this->model_shop_car->where($where=['car_plateno'=>$post_data['car_plateno']])->field('*')->find();
					if(!$shop_car_info){
						$shop_car_info = $this->model_shop_car->allowField(true)->save($post_data);
					}
					//车辆信息关联car_id
					$post_data['car_id'] = $shop_car_info['car_id'];
				}

				//调用模型方法入库
//				$post_data['shop_user_id'] = $shop_user_id;
				$order_recp_id = $this->model_shop_order_recp->shop_order_recp_save($post_data);
				if ($order_recp_id !== false) {
					$order_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($map=['carorder_id'=>$order_recp_id],'*');
					//外观检查项，json字符串
					$car_outside_check = json_decode($post_data['car_outside_check'], true);
					//功能检查项，json字符串
					$car_inside_check  = json_decode($post_data['car_inside_check'], true);
					if($car_outside_check || $car_inside_check){
						//把检查项目批量添加进入记录表
						if($order_recp_data['order_sn'] && is_array($car_outside_check)){
							$return_check_data = $this->model_shop_check_order->shop_check_order_saveall($car_outside_check,$order_recp_data);
							if($return_check_data){
								$outsite_check_ids = implode(',',$return_check_data['checkids']);
								$return_data = $this->model_shop_order_recp->setFieldValue($where=['order_sn'=>$order_recp_data['order_sn']], $field = 'car_outside_checkid', $value = $outsite_check_ids);
							}
						}

						//把检查项目批量添加进入记录表
						if($order_recp_data['order_sn'] && is_array($car_inside_check)){
							$return_check_data = $this->model_shop_check_order->shop_check_order_saveall($car_inside_check,$order_recp_data);
							if($return_check_data){
								$inside_check_ids = implode(',', $return_check_data['checkids']);
								$return_data = $this->model_shop_order_recp->setFieldValue($where=['order_sn'=>$order_recp_data['order_sn']], $field = 'car_inside_checkid', $value = $inside_check_ids);
							}
						}
					}
					//获取自增主键
					$carorder_id = $order_recp_data['carorder_id'];
					if($carorder_id){
						$return = $this->model_shop_order_recp->get_shop_order_recp_info($map=['carorder_id'=>$carorder_id],'*');
					}else{
						$errcode = 1;
						$message = '接待订单检查项保存失败';
					}
				}else{
					$errcode = 1;
					$message = '接待订单保存失败';
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
	 * 更新接待订单字段
	 *
	 * @param array $inupt_data
	 * @return array()
	 */
	public function updateOrderRecp($inupt_data=array()){
		//初始化
		$return = $post_data = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		if(request()->isPost()){
			//获取输入参数
			$post_data  = input('post.',$inupt_data);
			//店铺id
			$shop_id = intval($post_data['shop_id']);

			if(!empty($shop_id) && (!empty($post_data['carorder_id']) || !empty($post_data['order_sn']))){
				//模型入库更新
				//$map['order_sn'] = array('eq',$post_data['order_sn']);
				if($post_data['carorder_id']){
					$map['carorder_id'] = array('eq',$post_data['carorder_id']);
				}elseif($post_data['order_sn']){
					$map['order_sn'] = array('eq',$post_data['order_sn']);
				}
				
				//查询订单是否存在
				$order_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($map,'*',true);
				if($order_recp_data){
					$post_data = array_merge($order_recp_data,$post_data);
					$recp_data = $this->model_shop_order_recp->shop_order_recp_save($post_data,$map);
					if($recp_data){
						$order_recp_data = $recp_data;
					}
					//外观检查项
					$car_outside_check = $post_data['car_outside_check'];
					//功能检查项
					$car_inside_check  = $post_data['car_inside_check'];
					if($car_outside_check || $car_inside_check){
						//把检查项目批量添加进入记录表
						if($order_recp_data['order_sn'] && is_array($car_outside_check)){
							$return_outside_check_data = $this->model_shop_check_order->shop_check_order_saveall($car_outside_check,$order_recp_data);
							if($return_outside_check_data){
								$order_recp_data['car_outside_check'] = $return_outside_check_data['data'];
								$car_outside_checkid = arr2str($return_outside_check_data['checkids']);
								if($car_outside_checkid){
									$return_data = $this->model_shop_order_recp->setFieldValue($where=['order_sn'=>$order_recp_data['order_sn']], $field = 'car_outside_checkid', $value = $car_outside_checkid);
								}
							}
						}
						
						//把检查项目批量添加进入记录表
						if($order_recp_data['order_sn'] && is_array($car_inside_check)){
							$return_inside_check_data = $this->model_shop_check_order->shop_check_order_saveall($car_inside_check,$order_recp_data);
							if($return_inside_check_data){
								$order_recp_data['car_outside_check'] = $return_inside_check_data['data'];
								$car_inside_checkid = arr2str($return_inside_check_data['checkids']);
								if($car_inside_checkid){
									$return_data = $this->model_shop_order_recp->setFieldValue($where=['order_sn'=>$order_recp_data['order_sn']], $field = 'car_inside_checkid', $value = $car_inside_checkid);
								}
							}
						}
					}
					
					//获取自增主键
					$carorder_id = $order_recp_data['carorder_id'];
					if($carorder_id){
						$return = $this->model_shop_order_recp->get_shop_order_recp_info($map=['carorder_id'=>$carorder_id],'*',true);
					}else{
						$errcode = 1;
						$message = '接待订单更新失败';
					}
				}else{
					$errcode = 1;
					$message = '接待订单不存在';
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
	 * 订单作废接口
	 */
	public function cancelOrderRecp($inupt_data=array()){
		//初始化
		$return = $post_data = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		if(request()->isPost()){
			//获取输入参数
			$post_data  = input('post.', $inupt_data);
			//店铺id
			$shop_id = intval($post_data['shop_id']);
			$shop_user_id = intval($post_data['shop_user_id']);

			if(!empty($shop_id) && (!empty($post_data['carorder_id']) || !empty($post_data['order_sn']))){
				if($post_data['carorder_id']){
					$map['carorder_id'] = array('eq',$post_data['carorder_id']);
				}elseif($post_data['order_sn']){
					$map['order_sn'] = array('eq',$post_data['order_sn']);
				}

				//查询订单是否存在
				$order_recp_data = $this->model_shop_order_recp->get_shop_order_recp_info($map,'*',true);

				if($order_recp_data){
					if($order_recp_data['shop_user_id'] == $shop_user_id){
						//将订单状态改为
						if($order_recp_data['order_status'] > 0){
							$this->model_shop_order_recp->cancalOrderRecp($order_recp_data['carorder_id']);
						}else{
							$errcode = 1;
							$message = '接待订单当前状态已作废，不可以重复操作';
						}
					}else{
						$errcode = 1;
						$message = '该单不属于您不可操作';
					}
				}else{
					$errcode = 1;
					$message = '接待订单不存在';
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
	 * 获取订单信息，提供给前端客户页面使用
	 *
	 * @param string $carorder_id
	 * @param string $field
	 * @return array
	 */
	public function getorderRecpInfoFront($order_sn='',$field='*'){
		//初始化
		$return = $data = $map = array();
		$message = 'success';
		$errcode = 0;

		//获取参数
		$order_sn = input('order_sn',$order_sn,'string');

		//获取每个接车单的信息
		if($order_sn){
			$map['order_sn'] = array('eq',$order_sn);
			$data = $this->model_shop_order_recp->get_shop_order_recp_info_fomat($map,'*',true);
			if($data){
				$return = $data;
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 客户确认
     */
    public function customerConfirm(){
        $req_data = request()->param();
        $order_sn = isset($req_data['order_sn']) ? $req_data['order_sn'] : '';
        $result = $this->model_shop_order_recp->customerConfirm($order_sn, $this->customer_id);
        return $this->sendResult($result[0], $result[1], $result[2]);
    }

    /**
     * 支付
     */
    public function pay(){
        $req_data = request()->param();
        $order_sn = isset($req_data['order_sn']) ? $req_data['order_sn'] : '';
        $result = $this->model_shop_order_recp->pay($order_sn, $this->customer_id);
        return $this->sendResult($result[0], $result[1], $result[2]);
    }

    /**
     * 支付回调
     */
    public function payCallback(){
        $req_data = request()->param();
    }
}