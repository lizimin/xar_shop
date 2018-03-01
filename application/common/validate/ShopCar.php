<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/23 16:54
namespace app\common\validate;
use app\common\validate\Validate;

class ShopCar extends Validate{

    // 当前验证的规则
    protected $rule = [
        'car_plateno'     => 'require',
    ];

    // 验证提示信息
    protected $message = array(
        'car_plateno.require'      => '车牌必填',
    );
}