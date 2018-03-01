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

class MallCat extends ApiBase{

    private $model_cat;

    protected function _initialize(){
        parent::_initialize();
        $this->model_cat = model('common/MallProCat');
    }

    public function getCatList(){
        $input_data = request()->param();
        $pid = isset($input_data['pid']) ? $input_data['pid'] : 0;
        $this->customer_id = 1;
        if($this->customer_id <= 0){
            return $this->sendResult(array(), -1, '请登录');
        }
        $list = $this->model_cat->get_cat_list($pid);
        return $this->sendResult($list);
    }

}