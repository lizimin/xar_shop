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

class Customer extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_customer';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'customer_id';

    public function addCustomer($customer_info = array()){
        $user_info = array();
        if($customer_info['unionid']){
            $user_info = $this->getDataByMap(['unionid'=>$customer_info['unionid']]);
        }
        if(!$user_info){
            $customer_info['cusername'] = $this->generate_str(10);
            $password = $this->generate_str(6);
            $customer_info['cpassword'] = md5($password);
            $customer_info['create_time'] = time();
            $customer_info['update_time'] = time();
            $this->insert($customer_info);
            $customer_id = $this->getLastInsID();
        }else{
            $customer_id = $user_info['customer_id'];
        }
        return $customer_id;
    }
    public function generate_str( $length = 6 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $str;
    }

    public function getCustomer($customer_id = 0, $auth_idf = 'xarwx', $field = "*"){
        $result = array();
        if($customer_id){
            $result = $this->where(['customer_id'=>$customer_id])->field($field)->find();
            if($result) $result = $result->toArray();
            $result['extra'] = db('shop_customer_auth')->where(['customer_id'=>$customer_id, 'auth_idf'=>$auth_idf])->find();
        }
        return $result;
    }

    public function getCustomerBaseInfo($customer_id = 0, $filed='*'){
        $result = array();
        if($customer_id){
            $result = $this->where(['customer_id'=>$customer_id])->field($filed)->find();
            if($result) $result = $result->toArray();
        }
        return $result;
    }

    /**
     * @param string $openid
     * @param string $unionid
     * @return array
     * @throws \think\Exception
     * 根据openid或unionid获取用户信息
     */
    public function getCustomerByW($openid='', $unionid=''){
        if(!$openid && !$unionid){
            return array();
        }
        $map = array();
        if($openid){
            $map['openid'] = $openid;
        }
        if($unionid){
           $map['unionid'] = $unionid;
        }
        $auth = $model_auth = model('common/CustomerAuth')->where($map)->find();
        if(!$auth){
            return array();
        }
        $customer = $this->where(array('customer_id'=>$auth->customer_id))->find();
        return $customer ? $customer->toArray() : array();
    }
}