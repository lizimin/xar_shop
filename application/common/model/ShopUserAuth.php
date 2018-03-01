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
// | @Last Modified time: 2017-12-06 10:14:15 
// +----------------------------------------------------------------------
// | @Description 文件描述： 

namespace app\common\model;

use app\common\model\Model;

class ShopUserAuth extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_user_auth';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'auth_id';

    //自动写入时间戳
	protected $autoWriteTimestamp = true;
    
   // 定义时间戳字段名
   protected $createTime = 'create_time';
   protected $updateTime = 'update_time';


   /**
    * 通过条件查询用户信息
    *
    * @param array $where
    * @param string $order
    * @param string $field
    * @return void
    */
   public function get_auth_user_info($where=array(),$field='*', $extends = true){
        //初始化
        $return = array();
        //条件查询
        if($where){
            $return = $this->where($where)->field($field)->find();
            if($return && $extends){
              
            }
        }
        
        return $return;
   }

}