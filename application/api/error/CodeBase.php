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
// | @Last Modified time: 2017-12-08 14:42:20 
// +----------------------------------------------------------------------
// | @Description 文件描述：API错误code及信息

namespace app\api\error;

class CodeBase
{
    public static $errorArr = [
        0       => 'success',
        1       => 'error',
        1001    => '参数错误',
        1002    => '提交的信息不正确',
        1000001 => 'Toekn错误',
        1000003 => '接口路径错误',
        1000004 => '数据签名错误',
        1000005 => '请求参数错误',
        1010001 => '用户模型错误',

        //微信相关  微信用户信息获取失败
        10001 => '微信用户信息获取失败',


        // 1200开头  洗车卡相关
        12000 => '支付渠道错误',
        12001 => '没有可用的洗车卡',
        12002 => '洗车卡不存在',
        12003 => '您没有可以赠送的洗车卡',
        12004 => '当前赠送的洗车卡不存在',
        12005 => '赠送时间超过24小时、已自动退回',
        12006 => '自己不能领取自己的洗车卡',
        12007 => '该洗车卡已被领取',
        12008 => '您有没操作该卡的权限',
        12009 => '操作失败',
        12010 => '洗车卡已退回',
        // 1300开头  小程序相关
        13001 => '小程序登录态过期',
        13002 => '小程序解密参数错误',
        13003 => '员工加入失败。请联系管理员',
        13004 => '员工信息获取失败。',
        13005 => '生成二维码地址参数必须',
        13006 => '员工已存在。',
        //订单相关
        14001 => '您没有权限查看该订单详情',
        14002 => '订单不存在',
        14003 => '该订单已经评价过',

        //1500开头支付相关
        15001 => '支付发起错误',
        15002 => '该产品仅商城新用户可以购买哟',
        15003 => '您已超过该产品的最大购买次数',
        //活动相关
        16001 => '活动不存在'


        
    ];

}
