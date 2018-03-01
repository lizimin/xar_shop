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

class ShopCustomer extends Validate{

    // 当前验证的规则
    protected $rule = [
        'ctel'     => 'require',
    ];

    // 验证提示信息
    protected $message = array(
        'ctel.require'      => '电话不能为空',
    );
}