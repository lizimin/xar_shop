<?php
// +----------------------------------------------------------------------
// | 小矮人 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 柳天鹏
// +----------------------------------------------------------------------
// [  ]
//2017/11/27 17:33
namespace app\common\model;

use app\common\model\Model;

class CustomerAuth extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_customer_auth';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'auth_id';

    public function getCustomerInfo($unionid = '', $openid = '', $auth_idf = 'xarwx'){
    	$map = $result = array();
    	if($unionid){
    		$map['unionid'] = $unionid;
    	}
    	if($openid){
    		$map['openid'] = $openid;
    	}
        $map['auth_idf'] = $auth_idf;
    	$result = $this->where($map)->find();

    	if($result['customer_id']){
            $result = $result->toArray();
    		$result['customer_info'] = model('Customer')->getDataByPk($result['customer_id']);
    	}
    	return $result;
    }

    public function getDataByCustomerId($customer_id = 0, $auth_idf = 'xarwx'){
        $userInfo = array();
        if($customer_id && $auth_idf){
            $userInfo = model('Customer')->getDataByPk($customer_id);
            $auth = $this->where(['customer_id' => $customer_id, 'auth_idf' => $auth_idf])->find();
            $userInfo['auth'] = $auth ? $auth->toArray() : [];
        }
        return $userInfo;
    }

}