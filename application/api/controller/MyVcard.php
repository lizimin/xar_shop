<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Cache;
use think\Db;

class MyVcard extends ApiBase
{
    protected function _initialize(){
        parent::_initialize();
    }

    public function index($customer_id = 0){
        echo "MyVcard index";
    }

    //列出某人的卡列表
    public function vardlist($customer_id = 0){
        $customer_id = input('customer_id', $customer_id ,'intval');
        $xresult = array();
        if($customer_id){
           /* $map = [
                'to_customer_id' => $customer_id,
                'is_status' => 1,
            ];
            $result = db('mall_pro_vcard_relation')->where($map)->order('rel_id desc')->select();
            print_r($map);*/

            $xresult = Db::view('mall_pro_vcard_relation','rel_id,vc_id')
                        ->view('mall_pro_vcard','vc_id,pro_id,sku_id,out_trade_no,pay_id,customer_id,vcard_no,begin_time,exp_time,is_use,is_buy','mall_pro_vcard_relation.vc_id=mall_pro_vcard.vc_id')
                        ->where('mall_pro_vcard_relation.to_customer_id','=',$customer_id)
                        ->where('mall_pro_vcard_relation.is_status','=',1)//赠送状态待定
                        ->find();
        }
        return $xresult;
    }


    //提供给外部验证使用的卡信息 单卡
    //为保证使用安全，需要提供3个id进行统一验证
    //customer_id 客户id
    //rel_id  关系id
    //vc_id   卡id

    public function getVcardinfo($customer_id=0,$rel_id=0,$vc_id=0){
        $customer_id = input('customer_id', $customer_id ,'intval');
        $rel_id = input('rel_id', $rel_id ,'intval');
        $vc_id = input('vc_id', $vc_id ,'intval');
        $xresult = array();
        if($customer_id && $rel_id && $vc_id){

            $xresult = Db::view('mall_pro_vcard_relation','rel_id,vc_id,founder_user_id,to_customer_id')
                        ->view('mall_pro_vcard','vc_id,pro_id,sku_id,out_trade_no,pay_id,customer_id,vcard_no,begin_time,exp_time,is_use,is_buy','mall_pro_vcard_relation.vc_id=mall_pro_vcard.vc_id')
                        ->where('mall_pro_vcard_relation.to_customer_id','=',$customer_id)
                        ->where('mall_pro_vcard_relation.rel_id','=',$rel_id)
                        ->where('mall_pro_vcard_relation.vc_id','=',$vc_id)
                        ->where('mall_pro_vcard_relation.is_status','=',1)//赠送状态待定
                        ->find();
        }
        return $xresult;
    }



    public function get_row_by_relid($rel_id=0){
        $rel_id = input('rel_id', $rel_id ,'intval');
        $xresult = array();
        if($rel_id){
            $xresult = Db::name('mall_pro_vcard_relation')->where('rel_id',$rel_id)->find();
        }
        return $xresult;
    }







}