<?php
namespace app\cgj\controller;
use think\Controller;
use think\Cache;
use app\common\controller\BaseApi;
class Service extends BaseApi
{
	private $model_info, $model_user;
    protected function _initialize(){
        parent::_initialize();
        //$this->model_info = model('common/ShopInfo');
        //$this->model_user = model('common/ShopUser');
    }

    public function getshopService()
    {
    	$service_group = $list = $list1 = array();
		
		$shop_id = input('shop_id/d');
		if($shop_id){
			$service_group = db('shop_order_service_group')->where(['shop_id'=>$shop_id,'pid'=>0, 'is_status'=>1])->select();

	        foreach ($service_group as $key => $value) {

	        	$service_list = $service_info = array();

				$service_list = db('shop_order_service_relation')->where(['group_id'=>$value['sg_id'],'is_status'=>1])->column('service_id');
				$service_info = db('shop_order_service')->where(['service_id'=>array('in',$service_list), 'is_status'=>1])->select();
				$service_group[$key]['group'] = $service_info;
			}
		}
        $result = array('status'=> $shop_id,'list'=>$service_group);


        return $this->sendResult($result);
    }


}