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
// | @Description 文件描述： API接口

namespace app\api\controller;

use think\Controller;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;

class Mallvcard extends ApiBase{

    private $model_card, $model_given_card, $model_card_log, $model_card_relation;

    protected function _initialize(){
        parent::_initialize();
        $this->model_card = model('common/MallProVcard');
        $this->model_given_card = model('common/MallProVcardGive');
    }

    public function get_vcard_list(){
        $input_data = request()->param();
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录');
        }
        $type         = input('type',1,'intval');
        $id    = input('id',10,'intval');
        $data = array('type'=>$type, 'id'=>$id, 'customer_id'=>$this->customer_id);
        $list = $this->model_card->get_list_by_user($data);
        return $this->sendResult($list);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取用户卡片列表
     * 返回
     * count
     * page
     */
    public function getVcardsByUser(){
        $input_data = request()->param();
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');
        $is_use       = input('is_use', 0);
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录');
        }
        $where = array();
        if(isset($is_use)){
            $where['is_use'] = $is_use;
        }
        $where['exp_time'] = array('gt', time());
        $where['customer_id'] = $this->customer_id;
        $list = $this->model_card->get_vcard_list($where, $page, $page_size);
        return $this->sendResult($list);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取推荐卡片
     */
    public function getRecommendVcards(){
        $return_arr = array();
        $return_arr = model('common/MallProVcard')->get_recommend_vcards();
        return $this->sendResult($return_arr);
    }

    public function getGivenList(){
        $input_data = request()->param();
        
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录');
        }
        $type = input('type',1,'intval');
        $id   = input('id',10,'intval');
        $data = array('type'=>$type, 'id'=>$id, 'customer_id'=>$this->customer_id);
        $list = $this->model_card->get_given_list_by_user($data);
        return $this->sendResult($list);
    }

    /**
     * 赠送卡列表
     */
    public function getVcardGivenList(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录');
        }
        $where = array();
        $where['to_customer_id'] = $this->customer_id;
        $list = $this->model_given_card->get_given_vcard_list($where, $page, $page_size);
        return $this->sendResult($list);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取客户洗车卡概况
     */
    public function getCustomerCardInfo(){
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录');
        }
        $return_arr = $this->model_card->get_all_card_info($this->customer_id);
        return $this->sendResult($return_arr);
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取卡片详情
     */
    public function getCardInfo(){
        //获取参数
        $input_data = request()->param();
        $id = isset($input_data['id']) ? $input_data['id'] : 0;
        $return_arr = $this->model_card->get_card_info($id, $this->customer_id);
        if($return_arr['status'] == 0){
            return $this->sendResult($return_arr['card']);
        }else{
            return $this->sendResult(array(), -1, $return_arr['msg']);
        }
    }

    /**
     * 获取已经买的log信息
     * 用在页面的跑马灯
     */
    public function vcardBuyLog(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');
        $where = array();
        $list = $this->model_card->get_buy_list($where, $page, $page_size);
        return $this->sendResult($list);
    }


}