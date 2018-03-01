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

class Worker extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_user';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'shop_user_id';

    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * @param $id
     * 获取师傅信息
     */
    public function getMasterInfoById($id){
        if(!$id){
            return false;
        }
        $map = array();
        $map['shop_user_id'] = $id;
        $master_info = $this->where($map)->field('*')->find();
        if(!$master_info){
            return false;
        }
        $master_info = $master_info->toArray();
        $master = array();
        $master['photo'] = $master_info['headimgurl'];
        $master['nickname'] = $master_info['urealname'];
        //工作经验
        $master['exp_str'] = $master_info['exp'] ? $master_info['exp'] : '';
        //擅长
        $master['skill'] = $master_info['skill'] ? $master_info['skill'] : '';
        //用户id
        $auth = model('common/ShopUserAuth')->where(array('shop_user_id'=>$id,'auth_type'=>1,'auth_idf'=>'weixin'))->find();
        if($auth){
            $master['openid'] = $auth['openid'];
        }else{
            $master['openid'] = $master_info['openid'];
        }
        $master['link'] = '';
        return $master;
    }

}