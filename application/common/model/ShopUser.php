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
// | @Last Modified time: 2017-12-07 10:49:44 
// +----------------------------------------------------------------------
// | @Description 文件描述： 店铺用户模型

namespace app\common\model;

use app\common\model\Model;

class ShopUser extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_user';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'shop_user_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    //所属店铺信息
    public function shop(){
        return $this->hasOne('ShopInfo', 'shop_id', 'shop_id')->field('*');
    }
    
    //所属用户组
    public function shopgroup(){
        return $this->belongsTo('ShopUserGroup', 'group_id')->field('group_id,group_name,shop_user_id,shop_id,status');
    }

    //所属用户组
    public function shoproles(){
        return $this->hasMany('ShopUserRoleRelation', 'shop_user_id')->field('role_id,shop_user_id');
    }
    
    public function login($username = '', $password = '', $type = 1){
        $map = array();
        if (\think\Validate::is($username,'email')) {
            $type = 2;
        }elseif (preg_match("/^1[34578]{1}\d{9}$/",$username)) {
            $type = 3;
        }
        switch ($type) {
            case 1:
                $map['uname'] = $username;
                break;
            case 2:
                $map['uemail'] = $username;
                break;
            case 3:
                $map['utel'] = $username;
                break;
            default:
                return 0; //参数错误
        }

        $user = $this->where($map)->find();

        if(isset($user['status']) && $user['status']){
            /* 验证用户密码 */
            if($password === $user['upassword']){
                $this->autoLogin($user); //更新用户登录信息
                return $user['shop_user_id']; //登录成功，返回用户ID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    public function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'shop_user_id'             => $user['shop_user_id'],
        );

        $this->save($data,array('shop_user_id'=>$user['shop_user_id']));
        $user = $this->where(array('shop_user_id'=>$user['shop_user_id']))->find();
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'shop_user_id'             => $user['shop_user_id'],
            'uname'        => $user['uname'],
            'urealname'        => $user['urealname'],
            'group_id'          => $user['group_id'],
            'shop_id'      => $user['shop_id'],
//            'roleid'          => $user['roleid'],
        );

        //查询角色
        $model_role_rela = model('common/ShopUserRoleRelation');
        $r_map = array();
        $r_map['shop_user_id'] = $user['shop_user_id'];
        $auth['roleid'] = $model_role_rela->where($r_map)->column('role_id');
        //店铺是否过期
        $model_shop = model('common/ShopInfo');
        $shop = $model_shop->where(array('shop_id'=>$user['shop_id']))->field('shop_name,shop_group_id,shop_user_id,exp_time')->find();
        $auth['shop_group_id'] = $shop['shop_group_id'];
        $auth['shop_name'] = $shop['shop_name'];
        if($shop->exp_time <= time()){
            //店铺过期
        }

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
    }

    public function logout(){
        $uid = session('user_auth.shop_user_id');
        $_SESSION['_auth_list_'.$uid.'1'] ='';
        $_SESSION['_auth_list_'.$uid.'2']='';
        $_SESSION['_auth_list_'.$uid.'in,1,2']='';

        session('user_auth', null);
        session('user_auth_sign', null);
        session('user_access_', null);
    }

    /**
     * 获取用户列表信息
     *
     * @param array $where
     * @param integer $pagesize
     * @param string $order
     * @param string $field
     * @return void
     */
    public function get_shop_user_list($where=array(),$page=1,$pagesize=10,$order='carorder_id desc',$field='*'){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $return = $this->where($where)->limit($pagesize)->page($page)->order($order)->field($field)->select();
        }

        return $return;
    }

    /**
     * 获取用户信息
     *
     * @param array $where
     * @param string $field
     * @return void
     * 18年1月16日删除openid使用部分
     */
    public function get_shop_user_info($openid = '', $unionid = '', $field='*', $extend=false){
        //初始化
        $return = $data = array();
        //条件查询
        if($unionid){
            $this->where(['unionid'=>$unionid, 'status' => 1]);

            $data = $this->field($field)->find();
            if($data && $extend){
                unset($data['upassword']);
                //用户信息
                $return['user']       = $data->getData();
                //用户所属店铺信息
                $return['user_shop'] = array();
                if($data->shop){
                    $return['user_shop']   = $data->shop->getData();
                }
                //用户所属小组信息
                $return['user_group'] = array();
                if($data->shopgroup){
                    $return['user_group'] = $data->shopgroup->getData();
                }
                //用户所属角色信息
                $return['user_role'] = array();
                $user_role_data = $role_discount = [];
                if($data->shoproles){
                    $roledata = $data->shoproles;
                    foreach($roledata as $key=>$val){
                        $val = $val->getData();
                        $val['role_name'] = get_model_data($model='shop_user_role',$id=$val['role_id'],$field='role_name',$field_where='role_id');
                        //获取角色折扣ids
                        $discount_ids = db('shop_discount_role_relation')->where(['role_id'=>$val['role_id']])->column('discount_id');
                        if($discount_ids){
                            $role_discount = db('shop_discount')->where(['discount_id'=>['in',$discount_ids]])->column('discount_id,discount_name,discount_value');
                        }
                        
                        $user_role_data[] = $val;
                    }
                    $return['user_role']      = $user_role_data;
                    $return['discount_list']  = $role_discount;
                }
            }elseif($data){
                $return     = $data->getData();
            }
        }
        
        return $return;
    }

    public function getUserInfoById($id, $field='*'){
        if(!$id){
            return array();
        }
        $map = array();
        $map['shop_user_id'] = $id;
        $info = $this->where($map)->field($field)->find();
        if(!$info){
            return array();
        }
        return $info->toArray();
    }

}