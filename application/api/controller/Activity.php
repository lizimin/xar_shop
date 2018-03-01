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

class Activity extends ApiBase{

    protected function _initialize(){
        parent::_initialize();
    }

    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * 获取我的页面，活动列表
     * 返回
     * count
     * page
     */
    public function userHomeList(){
        $input_data = request()->param();
        //获取参数
        $page         = input('page',1,'intval');
        $field = 'art_id AS id,art_title AS title,create_time, art_open_value AS link';
        $return_arr = model('common/Article')->getTops($page, 10, 'art_sort DESC', $field);
        return $this->sendResult($return_arr);
    }

    public function getActivity(){
        $art_id = input('art_id');
        $return_arr = [];
        if($art_id){
            $return_arr = model('common/Article')->getArticle($art_id);
        }else{
            $errcode = 1001;
        }
        return $this->sendResult($return_arr, $errcode);
    }

}