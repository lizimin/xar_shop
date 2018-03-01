<?php
namespace app\wechat\controller;

use think\Controller;
use think\Cache;
use think\Db;
use EasyWeChat\Message\News;
class Scanwxqr extends Controller
{

    public function xindex($order_sn = ''){
        $resault = '';
        //url //qrurl
        //过期时间|接车单订单id|校验码
        $x_now = time();
        $x_exp_s = 3600;
        $x_qr_arr = [];
        $x_str = '';

        //扫码动作  扫码后用户绑定查看
        $scan_type = 'scan_cus_bd';
        //参数
        $scan_parm = $order_sn;
//        $scan_parm = 'order_sn';
//        $scan_parm = 'Snorder120171223151804A12345';
        //参数值
        $scan_parm_value = 1;//1为接车单号

        //随机生成
        $verify_code = mt_rand(10000,99999);

        //补充参数值 ，放最后，防止没有的时候解析问题
        $qr_otherstr = 'otherother';
        $x_str = $scan_type.'|x|'.$scan_parm.'|x|'.$scan_parm_value.'|x|'.$verify_code.'|x|'.$qr_otherstr;
        //$x_qr_arr = controller('api/WechatQr')->createQr($x_str, $x_exp_s);
        //new controller
        $C_WechatQr = controller( 'api/WechatQr');
        $x_qr_arr = $C_WechatQr->createQr($x_str, $x_exp_s);



        if($x_qr_arr['qrurl']){
            $save_arr = [];

            $save_arr = [
                'qr_scan_type' => $scan_type,
                'qr_scan_parm' => $scan_parm,
                'qr_scan_parm_value' => $scan_parm_value,
                'qr_otherstr' => $qr_otherstr,
                'wx_ticket' => $x_qr_arr['ticket'],
                'wx_url' => $x_qr_arr['url'],
                'wx_qrurl' => $x_qr_arr['qrurl'],
                'wx_exp' => $x_exp_s,
                'qr_exp_time' => intval($x_now+$x_exp_s),
                'verify_code' => $verify_code
            ];
            $resault = db('qrcode')->insert($save_arr);
        }

        if($x_qr_arr){
//            echo "<div>";
//            echo "<img src='$x_qr_arr[qrurl]' />";
//            echo "</div>";
        }
        echo json_encode(array('data'=>$x_qr_arr['qrurl'], 'error'=>0, 'message'=>''));
    }


    public function analyze_qr($x_key_str = '',$user_info = array()){
        $event_str = '';
        // dump(count($user_info));
        // dump($user_info);
        if($x_key_str && count($user_info)){
            $x_key_arr = explode('|x|',$x_key_str);

            $save_arr = [
                'qr_key' => $x_key_str,
                'qr_id' => db('scence_qrcode')->where(['qr_key'=>$x_key_str])->value('qr_id'),
                'customer_id' => $user_info['customer_id'],
                'add_time' => time()
            ];

            db('scan_qrcode_recode')->insert($save_arr);

            switch ($x_key_arr[0]) {
                //接单来源 scan_cus_bd
                case 'qrscene_scan_cus_bd':
                case 'scan_cus_bd':
                    $count_key = count($x_key_arr);
                    if($count_key > 2){
                        $event_parm = $x_key_arr[1];
                        $event_parm_value = $x_key_arr[2];
                        $event_verify = $x_key_arr[3];
                        if($count_key == 5){
                            $event_other = $x_key_arr[4];
                        }
                    }

                    $x_key_type = 'scan_cus_bd';


                    //是否找到qr_id
                    $xdo_qrid = $this->do_check_qrcode($x_key_type,$event_parm_value,$event_verify,$user_info);
//                    $event_str = "二维码已经验证（已作废），请联系接待重新生成，扫描后有礼。";
                    if($xdo_qrid){
                        $qcode = db('qrcode')->where(array('qr_id'=>$xdo_qrid))->find();
//                        $event_str = "订单号为：$event_parm_value . 二维码验证成功。<a http=''>点击进行绑定，绑定成功抽大奖。</a>";
                        //绑定用户和接车单
                        $model_customer = model('commmon/Customer');
                        $customer_info = $model_customer->getCustomerByW($user_info['openid'], $user_info['unionid']);
                        if(empty($customer_info)){
                            //如果客户不存在，先添加
                            $customer_info = controller('api/Customer')->addCustomer($user_info, 1, 'xarwx');
                        }
                        $recp = model('common/ShopOrderRecp')->where(array('order_sn'=>$qcode['qr_scan_parm']))->field('carorder_id')->find();
                        if($recp){
                            $model_relation = model('common/ShopOrderRecpRelation');
                            $has_bind = $model_relation->where(array('carorder_id'=>$recp->carorder_id))->count();
                            if($has_bind){
                                $event_str = "该接车单已被客户认领，不能重复绑定";
                            }else{
                                model('common/ShopOrderRecp')->where(array('order_sn'=>$qcode['qr_scan_parm']))->setField('customer_id', $customer_info['customer_id']);
                                $relation_data = array(
                                    'carorder_id' => $recp->carorder_id,
                                    'customer_id' => $customer_info['customer_id'],
                                    'create_time' => time(),
                                    'update_time' => time()
                                );
                                $model_relation->create($relation_data);
                                $event_str = "订单号为：".$qcode['qr_scan_parm']." . 二维码验证成功。http://mall.gotomore.cn/index.html?#/order_detail/".$qcode['qr_scan_parm']." 点击进行绑定，绑定成功抽大奖。";
                            }
                        }else{
                            $event_str = "接车单不存在，请联系管理员";
                        }
                    } else {
                        $event_str = "二维码已经验证（已作废），请联系接待重新生成，扫描后有礼。";
                    }


                    break;
                //.....
                case 'scan_check':

                    break;
                case 'scan_view':
                    break;
                case 'product':
                    $pro_id = isset($x_key_arr[1]) ? $x_key_arr[1] : 0;
                    $customer_id = isset($x_key_arr[2]) ? $x_key_arr[2] : 0;
                    $shop_id = isset($x_key_arr[3]) ? $x_key_arr[3] : 0;
                    $ac_id = isset($x_key_arr[4]) ? $x_key_arr[4] : 0;
                    $map = [
                        'is_sale' => 1,
                        'is_status' => 1,
                        'pro_id' => $pro_id
                    ];
                    $product = db('mall_pro_info')->where($map)->find();
                    if($product){ 
                        $customer = null;
                        if($customer_id) $customer = model('Customer')->getCustomer($customer_id);
                        $name = '我';
                        if($customer) $name = $customer['cname'];
                        // dump($customer);
                        $news = new News();
                        $news->title = $name.'给你推荐了一个产品【'.$product['pro_name'].'】';
                        $news->description = $product['pro_name'];
                        $news->url = 'http://mall.gotomore.cn/index.html?#/goods/'.$pro_id.'?referer='.$customer_id.'&shop_id='.$shop_id;
                        $image_list = model('Attachment')->where(['aid'=>['in', $product['pro_img']]])->field('domain, path')->select();
                        $news->image = $image_list[0]['domain'].$image_list[0]['path'];
                        $event_str = $news;
                        if($pro_id == 51 && $ac_id){
                            $openid = isset($customer['extra']) && isset($customer['extra']['openid']) ? $customer['extra']['openid'] : ''; 
                            controller('pay/LuckyMoney')->activityLyckyMoney($openid, $ac_id);
                        }
                        // dump($event_str);
                    }else{
                        $event_str = '该产品不存在或不可用';
                    }
                    // $event_str = '参数二维码产品';
                    break;
                case 'youhui':
                    $result = controller('api/Vcard')->giveXarYhq($user_info['customer_id']);
                    $news = new News();
                    if($result){
                        $count = 0;
                        // foreach ($result as $key => $value) {
                        //     if($value[1] == 0) $count ++;
                        // }
                        $news->title = '您已成功领取3张优惠券!';
                    }else{
                        $news->title = '您已经领取过啦。';
                    }
                    $news->description = '点击进入个人中心查看更多!';
                    $news->url = 'http://mall.gotomore.cn/index.html?#/my';
                    $news->image = 'http://opic.gotomore.cn/upload/mall/2018/01/24/1516785317_093EfS.jpg';
                    $event_str = $news;
                    break;
                default:
                    $event_str = '';
                    break;
            }
        }
        return $event_str;
    }

    //进行二维码验证，是否正常渠道生产，校验码是否匹配
    //操作qrcode表
    public function do_check_qrcode($qr_scan_type = '',$qr_scan_parm_value = '',$verify_code = '',$user_info = array()){
        $qr_code_id = '';
        $qr_id = 0;

        //参数齐全
        if($qr_scan_type && $qr_scan_parm_value && $verify_code && count($user_info)){
            $map = [];
            $map['is_verify'] = 0;
            $map['qr_scan_type'] = $qr_scan_type;
            $map['qr_scan_parm_value'] = $qr_scan_parm_value;
            $map['verify_code'] = $verify_code;

            $qr_id = db('qrcode')->where($map)->value('qr_id');
            if($qr_id){

                $save_arr = [];
                $save_arr = [
                    'qr_id' => $qr_id,
                    'scan_time' => time(),
                    'scan_wx_openid' => $user_info['openid'],
                    'scan_wx_unionid' => $user_info['unionid']
                ];
                $qr_resault = db('qrcode_log')->insert($save_arr);


                //验证成功后，需要加入绑定接车单及用户的relation关系
                //待完成

                if($qr_resault){
                    db('qrcode')->where($map)->setField('is_verify', 1);
                }

            }

        }

        return $qr_id;
    }

    //添加扫码记录
    public function do_table_qrcode_log(){

    }


}



