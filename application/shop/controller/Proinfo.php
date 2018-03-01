<?php

namespace app\shop\controller;

use think\DB;
use think\Cache;
use app\common\controller\Common;
use app\index\model\User;

class Proinfo extends Common
{

    /**
     * 初始化方法
     */
    protected function _initialize(){
        parent::_initialize();
    }
    
    public function http_curl($url,$type = 'get', $arr ='' ){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch , CURLOPT_URL,$url);//设置访问的地址
        curl_setopt($ch , CURLOPT_RETURNTRANSFER,1);//获取的信息返回
        if($type == 'post'){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
        }
        $output = curl_exec($ch);//采集
        if(curl_error($ch)){
            return curl_error($ch);
        }
        return $output;
    }

    public function index()
    {
        $jsonurl = "http://127.0.0.1/list.json";
        $httpinfo = $this->http_curl($jsonurl);
        
        $add_info = json_decode($httpinfo,true);

        //print_r($add_info);
        //db('cys_tmp_addcode')->insertAll($add_info);
        //Db::name('tmp_addcode')->insertAll($add_info);
        
        foreach($add_info as $key=>$val){
            echo $key.$val;
            //if($key == '120000'){
            //    break;
            //}
            $data = ['xcode' => $key,'xname' => $val,'pp' => substr($key,0,2),'cc' => substr($key,2,2),'dd' => substr($key,4,2)];
            db('tmp_addcode')->insert($data);
            //Db::name('cys_tmp_addcode')->insert($data);
            
        }    
    }
    /**
     * 默认方法
     *
     * @return void
     */
    public function index_pro()
    {
        $pro_id = 2;
        $MallProInfo = model('MallProInfo');
        //$product = $MallProInfo::get(2);

        //$product = MallProInfo::get(2);
        //dump($product);
        $product_info = DB::view('mall_pro_info', 'pro_id,pro_name,pro_type,pro_cover_id,pro_img,pro_dep,pro_content')
            ->view('mall_pro_cat', ['cat_id', 'cat_name', 'property_id'], 'mall_pro_info.cat_id=mall_pro_cat.cat_id')
            ->where('pro_id', $pro_id)
            ->where('is_sale', 1)
            ->where('is_status', 1)
            //->order('pro_id desc')
            ->find();
        
        echo "产品名称：";
        echo "$product_info[pro_name]"."<Br>";
        echo "产品所属类：";
        echo "$product_info[cat_name]"."<Br>";
        echo "产品描述：";
        echo "$product_info[pro_dep]"."<Br>";
        echo "产品介绍：";
        echo "$product_info[pro_content]"."<Br>";

        echo "--------------------<Br>";
        $pro_sku_info = DB::table('cys_mall_pro_sku')->where('pro_id',$product_info['pro_id'])->select();
        foreach ($pro_sku_info as $sku_info) {
            echo "sku_info：";
            echo "$sku_info[sku_id]&nbsp;&nbsp;$sku_info[sku_code]&nbsp;&nbsp;$sku_info[sku_name]&nbsp;&nbsp;$sku_info[mall_price]&nbsp;&nbsp;$sku_info[stock]&nbsp;&nbsp;"."<Br>";
        }


        //$pro_sku_relation = DB::table('cys_mall_pro_sku_relation')->distinct(true)->field('property_id')->where('pro_id',$product_info['pro_id'])->select();
        $pro_sku_relation = DB::table('cys_mall_pro_sku_relation')->distinct(true)->where('pro_id',$product_info['pro_id'])->column('property_id,property_value_id');
        //print_r($pro_sku_relation);
        foreach ($pro_sku_relation as $k => $v) {
            $all_pv_value = array();
            echo "";
            echo $this->get_propertyname($k);
            echo "&nbsp;&nbsp;";
            
            $all_pv_value = $this->getall_pv_value($k);
            foreach ($all_pv_value as $pv_info) {
                echo $pv_info;
                echo "&nbsp;&nbsp;";
            }
            echo "<Br>";
            echo '已选择：'.$this->get_pv_value_name($v);
            echo "<Br>";
        }
        return;
    }


    public function get_propertyname($prop_id = 0){
        $prop_id = intval($prop_id);
        $get_propertyname = DB::table('cys_mall_pro_property')->field('property_name')->where('property_id',$prop_id)->find();
        
        return $get_propertyname['property_name'];
    }

    public function get_pv_value_name($pv_id = 0){
        $pv_id = intval($pv_id);
        $get_pv_value_name = DB::table('cys_mall_pro_property_value')->field('pv_value')->where('pv_id',$pv_id)->find();

        return $get_pv_value_name['pv_value'];
    }

    public function getall_pv_value($prop_id = 0){
        $prop_id = intval($prop_id);
        $getall_pv_value = DB::table('cys_mall_pro_property_value')->where('property_id',$prop_id)->column('pv_value');
        
        return $getall_pv_value;
    }
    
}
