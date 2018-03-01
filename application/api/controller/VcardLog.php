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
use app\common\controller\ApiBase;

class VcardLog extends ApiBase
{	
	//操作类型 0-系统分配 1-消费  2-赠送， 3、领取赠送的洗车卡, 4过期退回
	public $action_type = [
		0 => '系统分配',
		1 => '消费',
		2 => '赠送',
		3 => '领取赠送的卡',
		4 => '过期退回',
	];
	public function getVcardLog(){
		$result = $map = [];
		$errcode = 0;
		$message = '';
		$out_trade_no = input('out_trade_no', '');
		if($out_trade_no && $this->customer_id){
			$map['out_trade_no'] = $out_trade_no;
			$map['customer_id'] = $this->customer_id;
			$order = db('mall_order')->where($map)->find();
			if($order){
				$result = model('Vcard')->alias('a')->join('mall_pro_vcard_log b', 'a.vc_id = b.vc_id')->where(['a.out_trade_no'=>$out_trade_no, 'b.is_status'=>1, 'b.vc_action_type' => ['neq',3]])->field('b.*')->order('b.create_time desc')->select();
				foreach ($result as $key => $value) {
					$result[$key]['action'] = $this->action_type[$value['vc_action_type']];
				}
				// dump($vcard_log);
			}else{
				$errcode = 14001;  //订单不是他的。不能查看
			}
		}else{
			$errcode = 1001;
		}
		return $this->sendResult($result,$errcode,$message);
	}

    /**
     * 获取已经领取的log信息
     * 用在领取卡片页面的跑马灯
     */
    public function vcardReceiveLog(){
        //获取参数
        $page         = input('page',1,'intval');
        $page_size    = input('page_size',10,'intval');
        $where = array();
        $list = model('common/MallProVcardLog')->get_recevice_list($where, $page, $page_size);
        return $this->sendResult($list);
    }
}



