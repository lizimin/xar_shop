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
// | @Description 文件描述： 所有推荐位置信息

namespace app\api\controller;

use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;

class Consumption extends ApiBase{

    protected function _initialize(){
        parent::_initialize();

    }

    /**
     * 获取消费（使用）记录
     * type vcard：洗车卡 conpon：优惠券
     */
    public function getConsumptionList(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');
        //请求类型 type: vcard  coupon，使用type来区分是那一个种类型
        $type    = input('type','vcard','string');
        //卡或者券的ID
        $id = input('id','');
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录');
        }
        $where = array();
        $where['customer_id'] = $this->customer_id;
        if($type == 'vcard'){
            //这里是vc_id，卡的ID
            //查看页面有发现，这里的id是list_no
            if($id){
                $where['list_no'] = $id;
            }

            $return_arr = model('common/MallProVcardLog')->get_list($where, $page, $page_size);
        }
        if($type == 'coupon'){

        }
        return $this->sendResult($return_arr);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取我的页面悬浮窗的师傅
     */
    public function getMasterInMy(){
        $return = array();
        $return['photo'] = 'http://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83epqB1zeh0lsl7ic9JUTwr8BJoLYOibxqYibib4Taj2UHVicY8q9xYoElm3E3Ln6Fco1ibUicOprER0lpzzbQ/0';
        $return['nickname'] = '邵暄';
        //工作经验
        $return['exp_str'] = '9年工作经验';
        //擅长
        $return['skill'] = '擅长：保养、维修、钣喷、验车';
        //用户id
        $return['openid'] = 'openid';
        $return['link'] = '';
        return $this->sendResult($return);
    }
}