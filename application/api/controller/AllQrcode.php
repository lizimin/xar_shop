<?php
namespace app\api\controller;
use app\common\controller\ApiBase;
use think\Cache;

class AllQrcode extends ApiBase
{
    public $plate = '';
    public $remark = '';
    public $exp_time = 60 * 5;
    protected function _initialize(){
        parent::_initialize();
    }

    public function index($customer_id = 0){
        echo "xxxindex";
    }

    private function gen_xc_qrstr($customer_id=0,$rel_id=0,$vc_id=0){
        //思路：
        //关系id+vc_id+out_trade_no+founder_user_id+to_customer_id+过期时间
        $customer_id = input('customer_id', $customer_id ,'intval');
        $rel_id = input('rel_id', $rel_id ,'intval');
        $vc_id = input('vc_id', $vc_id ,'intval');
        $xresult = array();


        if($customer_id && $rel_id && $vc_id){
            $xarrs = ['customer_id'=>$customer_id,'rel_id'=>$rel_id,'vc_id'=>$vc_id];
            $C_MyVcard = controller( 'MyVcard');

            $xresult = $C_MyVcard->getVcardinfo($customer_id, $rel_id, $vc_id);

            //获取
            $x_rel_id = $xresult['rel_id'];
            $x_vc_id = $xresult['vc_id'];
            $x_out_trade_no = $xresult['out_trade_no'];
            $x_founder_user_id = $xresult['founder_user_id'];
            $x_to_customer_id = $xresult['to_customer_id'];
            $x_exp_time = $xresult['exp_time'];
            $x_time = time();
            $x_time_exp = $x_time + $this->exp_time;

            if($x_rel_id && $x_vc_id && $x_out_trade_no && $x_founder_user_id && $x_to_customer_id && $x_exp_time){
                //$secret_str  = $x_rel_id.'|x|'.$x_vc_id.'|x|'.$x_out_trade_no.'|x|'.$x_founder_user_id.'|x|'.$x_to_customer_id.'|x|'.$x_exp_time;
                //100 是洗车模式
                //abynkm 是固定验证字符串
                $secret_str  = $x_time_exp .'100'. md5($x_vc_id.$x_rel_id.$x_time_exp).$x_rel_id;
                $xiaoyan_str = substr(md5($x_time_exp.$x_rel_id.'abynkm'),-5);
                $secret_str = $secret_str.$xiaoyan_str;

                //echo '<Br>'.$xiaoyan_str.'<Br>';
            } else {
                //echo $x_rel_id.'-'.$x_vc_id . '-'.$x_out_trade_no . '-'.$x_founder_user_id . '-'.$x_to_customer_id . '-'.$x_exp_time . '';

            }

            // dump($C_MyVcard);
        }

        return $secret_str;
    }

    public function showstr($customer_id=0,$rel_id=0,$vc_id=0){
        $customer_id = input('customer_id', $customer_id ,'intval');
        $rel_id = input('rel_id', $rel_id ,'intval');
        $vc_id = input('vc_id', $vc_id ,'intval');
        $gen_str = '';
        $x_now = time();
        $arr_gen = array();

        if($customer_id && $rel_id && $vc_id){

            $gen_str = $this->gen_xc_qrstr($customer_id,$rel_id,$vc_id);
            if($gen_str){
                //echo $gen_str;

                $arr_gen = ['x_now'=>$x_now,'x_str'=>$gen_str];
            }

        //var_dump($arr_gen);
        }
        return $this->sendResult($arr_gen);
    }

    //通过订单号获取洗车卡详情。
    public function getMyVcard(){
        $result = [];
        $msg = '';
        $errcode = 0;
        $list_no = input('list_no', '');
        $card_code = input('card_code', '');
        if($this->customer_id){
            if($list_no || $card_code){
                $vc_id = $rel_id = 0;
                if($list_no){
                    $vc_id = model('Vcard')->where(['customer_id'=>$this->customer_id, 'list_no'=>$list_no, 'is_status'=>1, 'is_use'=>0])->order('vc_id asc')->value('vc_id');
                }elseif($card_code){
                    $give_card = model('VcardGive')->getGiveCard($card_code);
                    //判断当前用户是否是该赠送洗车卡的获赠用户
                    if($give_card['to_customer_id'] == $this->customer_id) $vc_id = $give_card['vc_id'];
                }
                if($vc_id) $rel_id = model('VcardRelation')->where(['to_customer_id'=>$this->customer_id,'vc_id'=>$vc_id])->value('rel_id');
                if($vc_id && $rel_id){
                    $count = db('mall_pro_vcard')->alias('a')->join('cys_mall_pro_vcard_relation b', 'a.vc_id = b.vc_id')->where(['a.is_status'=>1, 'a.is_use'=>0, 'b.to_customer_id'=>$this->customer_id, 'b.customer_id'=>$this->customer_id, 'a.list_no'=>$list_no])->count();   //判断当前用户购买的卡还有几张可以赠送
                    $result['x_str'] = $this->gen_xc_qrstr($this->customer_id, $rel_id, $vc_id);
                    $result['x_now'] = time();
                    $result['count'] = $count;
                }else{
                    $errcode = 12001; //没有可以用的洗车卡。
                }
            }else{
                $errcode = 1001;  //参数错误
            }
            
            // dump($this->customer_id);
        }else{
            $errcode = 10001;
        }
        return $this->sendResult($result, $errcode, $msg);
    }

    //通过订单号获取赠送的洗车卡信息
    public function giveVcard(){
        $result = [];
        $msg = '';
        $errcode = 0;
        $list_no = input('list_no', '');

        if($this->customer_id && $list_no){
            $result = $this->_giveVcard($this->customer_id, $list_no);
            if(!$result) $errcode = 12003;
        }else{
            $errcode = 1001;
        }
        return $this->sendResult($result, $errcode, $msg);
    }

    public function _giveVcard($customer_id = 0, $list_no = ''){
        $result =false;
        if($customer_id && $list_no){
            $give = db('mall_pro_vcard')->alias('a')->join('cys_mall_pro_vcard_relation b', 'a.vc_id = b.vc_id')->where(['a.is_status'=>1, 'a.is_use'=>0, 'b.to_customer_id'=>$customer_id, 'b.customer_id'=>$customer_id, 'a.list_no'=>$list_no])->order('a.vc_id asc')->find();
            if($give){
                $vcard_md5 = md5($give['vcard_no']. time());
                $save_arr = [
                    'customer_id' => $customer_id,
                    'create_time' => time(),
                    'vc_id' => $give['vc_id'],
                    'vcard_no' => $give['vcard_no'],
                    'list_no' => $give['list_no'],
                    'card_code' => $vcard_md5,
                    'expire_time' => time() + 24 * 60 * 60,
                ];
                db('mall_pro_vcard_give')->insert($save_arr);
                db('mall_pro_vcard')->where(['vc_id'=>$give['vc_id']])->update(['is_status'=>2]);//更新洗车卡状态为赠送中。
                model('VcardLog')->add_log(2, $give['vc_id'], $give['vcard_no'], $customer_id);
                $result['card_code'] = $vcard_md5;
            }
        }
        return $result;
    }

    //扫码获取当前洗车卡商品详情、
    public function getVcard(){
        $qrstr = input('pathmd5', $qrstr, 'string');
        $x_now = time();
        $qrstr_exptime = $qrstr_type = $qrstr_md5str = $tmp_qrstr_rel_id = $qrstr_xiaoyan = $qrstr_rel_id ='';
        $err_str = '';
        $x_status = 0;
        $result = array();
        if($qrstr){
            //解析字符串
            $qrstr_exptime  = substr($qrstr, 0,10);
            $qrstr_type  = intval(substr($qrstr, 10,3));
            $qrstr_md5str  = substr($qrstr, 13,32);
            $tmp_qrstr_rel_id  = substr($qrstr, 45,100);
            $qrstr_xiaoyan  = substr($qrstr, -5);
            $qrstr_rel_id = intval(str_replace($qrstr_xiaoyan,'',$tmp_qrstr_rel_id));
            $result = model('VcardRelation')->get_vcard_by_rel_id($qrstr_rel_id);
        }
        return $this->sendResult($result);
    }


    /**
     * @Author   liutianpeng
     * @DateTime 2017-12-19
     * @param    integer     $vc_id       [卡号的主键ID]
     * @param    string      $vcard_no    [核销的卡号]
     * @param    integer     $customer_id [顾客ID]
     * @param    integer     $shop_id     [店铺id]
     * @param    integer     $staff_id    [员工ID]
     * @param    integer     $rel_id      [卡关联ID]
     * @return   [type]                   [description]
     */
    private function vcardDestory($vc_id = 0, $vcard_no = '', $customer_id = 0, $shop_id = 0, $staff_id = 0, $rel_id = 0, $img_list = ""){
        $result = false;
        //更改洗车卡状态
        if($vc_id && $vcard_no && $customer_id && $shop_id && $staff_id && $rel_id ){
            $save_arr = [
                'vc_id' => $vc_id,
                'vcard_no' => $vcard_no,
                'customer_id' => $customer_id,
                'shop_id' => $shop_id,
                'staff_id' => $staff_id,
                'add_time' => time(),
                'rel_id' => $rel_id,
                'img_list' => $img_list,
                'plate' => $this->plate,
                'remark' => $this->remark,
                'order_no' => db('mall_pro_vcard')->where(['vc_id' => $vc_id])->value('out_trade_no')
            ];
            $m_vcard_log = db('mall_pro_vcard_destory');
            $m_vcard_log->insert($save_arr);
            db('mall_pro_vcard')->where(['vc_id'=>$vc_id])->update(['is_use'=>1]);
            //添加消费记录   1为消费
            
            model('VcardLog')->add_log(1, $vc_id, $vcard_no, $customer_id);
            $destory_id = $m_vcard_log->getLastInsId();
            controller('notice/Vcard')->notice($vc_id, $customer_id, $shop_id, $staff_id, $destory_id);
        }
        return $result;
    }

    //验证洗车卡逻辑
    public function deqrcode($qrstr = ''){
        $qrstr = input('pathmd5', $qrstr, 'string');
        $img_list = input('img_list', '', 'string');
        $plate = input('plate', '', 'string');
        $remark = input('remark', '', 'string');
        $this->plate = $plate;
        $this->remark = $remark;
        $xcx_client_info = $this->xcx_client_info;
        $x_now = time();
        $qrstr_exptime = $qrstr_type = $qrstr_md5str = $tmp_qrstr_rel_id = $qrstr_xiaoyan = $qrstr_rel_id ='';
        $err_str = '';
        $x_status = 1;
        $result = [];
        if($qrstr){
            //解析字符串
            $qrstr_exptime  = substr($qrstr, 0,10);
            $qrstr_type  = intval(substr($qrstr, 10,3));
            $qrstr_md5str  = substr($qrstr, 13,32);
            $tmp_qrstr_rel_id  = substr($qrstr, 45,100);
            $qrstr_xiaoyan  = substr($qrstr, -5);
            $qrstr_rel_id = intval(str_replace($qrstr_xiaoyan,'',$tmp_qrstr_rel_id));
            // dump($x_now);
            // dump($qrstr_exptime);
            if($x_now < intval($qrstr_exptime)){

            //调试打开
            //判断是否过期,如果当前时间大于二维码时间
            //换大于符号
            //if($x_now > $qrstr_exptime){

                //判断是否校验成功
                if(substr(md5($qrstr_exptime.$qrstr_rel_id.'abynkm'),-5) == $qrstr_xiaoyan){
                    //echo "success";

                    if($qrstr_type == 100){
                        //echo "进入洗车卡消费模式";
                        if($qrstr_rel_id){
                            $C_MyVcard = controller('MyVcard');
                            $xresult = $C_MyVcard->get_row_by_relid($qrstr_rel_id);
                            $vcard = model('Vcard')->getDataByPk($xresult['vc_id']);
                            if($xresult && $vcard){
                                $xre_vc_id = '';
                                $xre_vc_id = $xresult['vc_id'];
                                //参考生成模式
                                ////$secret_str  = $x_time_exp .'100'. md5($x_vc_id.$x_rel_id.$x_time_exp).$x_rel_id;
                                $secret_new = md5($xre_vc_id.$qrstr_rel_id.$qrstr_exptime);
                                if( $secret_new == $qrstr_md5str){
                                    if(!$vcard['is_use']){
                                        $ok_str = '成功，这里是最终最终。。。。。。。。。。。。。';
                                        $this->vcardDestory($xre_vc_id, $vcard['vcard_no'], $xresult['to_customer_id'], $xcx_client_info['user']['shop_id'], $xcx_client_info['user']['shop_user_id'], $qrstr_rel_id, $img_list);
                                        $x_status = 0;
                                    } else{
                                        $err_str = '卡已被使用'; 
                                    }
                                } else {
                                    $err_str = '卡id验证失败';
                                }
                            } else {
                                $err_str = '卡关系验证失败';
                            }
                        }
                    } else {
                        //其它扫码操作动作
                    }
                } else {
                    $err_str = '效验失败';
                }
            } else {
                $err_str = '已过期';
            }
        }else{
            $err_str = '识别参数不正确';
        }
        return $this->sendResult($result, $x_status, $err_str);
    }


    public function test(){
        $res = $this->vcardDestory(2, '20171218164819ZrbiYMkAEnFk6gdX', 6, 1, 13, 1);
        dump($res);
    }

}