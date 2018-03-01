<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 用户组
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/23 14:40
namespace app\common\model;

use app\common\model\Model;

class ShopUserGroup extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_user_group';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'group_id';

    // 保存自动完成列表
//    protected $auto = array();
    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');

    /**
     * @param $uid
     * @param string $field
     * @return array
     * @throws \think\Exception
     * 获取组长
     */
    public function getLeaderInfo($group_id, $field='*'){
        $group = $this->where(array('group_id'=>$group_id))->field('shop_user_id')->find();
        if(!$group){
            return array();
        }
        $group = $group->toArray();
        $user = model('ShopUser')->where(array('shop_user_id'=>$group['shop_user_id']))->field($field)->find();
        if(!$user){
            return array();
        }
        return $user->toArray();
    }

    /**
     * 获取工种列表
     *
     * @param array $where
     * @param integer $pagesize
     * @param string $order
     * @param string $field
     * @return void
     */
    public function get_shop_user_group_list($where=array(),$limit='',$order='group_id desc',$field='group_id,group_name,shop_user_id,shop_id'){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->limit($limit)->order($order)->field($field)->select();
            if($data){
                foreach($data as &$val){
                    $val = $val->getData();
                    //获取用户扩展信息
                    //$val['shop_user_name'] = get_model_data($model='shop_user',$id=$val['shop_user_id'],$field='uname',$field_where='shop_user_id');
                    $val['group_leader'] = [];
                    if($val['shop_user_id']){
                        $val['group_leader'] = get_db_where_data($model='shop_user',$where=['shop_user_id'=>$val['shop_user_id']],$field='urealname,utel',$cache_id=$val['shop_user_id']);
                    }
                }
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
    public function get_shop_user_group_info($where=array(),$field='*', $extend=false){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $data = $this->where($where)->field($field)->find();
            if($data &&  $extend){
                $data    = $data->getData();
                //获取用户扩展信息
                //$data['shop_user_name'] = get_model_data($model='shop_user',$id=$return['shop_user_id'],$field='uname',$field_where='shop_user_id');
                $data['group_leader'] = [];
                if($data['shop_user_id']){
                    $data['group_leader'] = get_db_where_data($model='shop_user',$where=['shop_user_id'=>$data['shop_user_id']],$field='urealname,utel',$cache_id=$data['shop_user_id']);
                }

                $return = $data;
            }else{
                $return    = $data->getData();
            }
        }

        return $return;
    }
}