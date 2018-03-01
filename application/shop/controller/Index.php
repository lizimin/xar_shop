<?php

namespace app\shop\controller;
use think\Db;
use think\Cache;
use app\common\controller\Common;

class Index extends Common
{

    /**
     * 初始化方法
     */
    protected function _initialize(){
        parent::_initialize();
    }

    /**
     * 默认方法
     *
     * @return void
     */
    public function index()
    {
        //$pro_info = db('mall_pro_t_sku')->where(['pro_id'=>3,'is_status'=>1])->select();

        $list = Db::table('cys_mall_pro_t_sku')
            ->where('pro_id', '=', 3)
            ->limit(8)
            ->select();
        dump($list);

       
        $result = Db::view('cys_mall_pro_t_sku', 'sku_id,sku_name,mall_price')
            ->view('cys_mall_pro_t_info', ['pro_name' => 'pro_name', 'pro_type', 'pro_dep'], 'cys_mall_pro_t_info.pro_id=cys_mall_pro_t_sku.pro_id')
            ->where('is_status', 1)
            ->order('sku_id desc')
            ->select();
        dump($result);



        return 'okokokoko';
    }

    /**
     * 上传文件测试表单
     *
     * @return void
     */
    public function upload_test(){

        return $this->fetch();
    }
    
}
