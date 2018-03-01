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
//2017/11/23 15:44
namespace app\common\validate;
use app\common\validate\Validate;

class ShopUser extends Validate{

    // 当前验证的规则
    protected $rule = [
        'uname'     => 'require|length:4,8',
        'urealname'     => 'require',
        'utel'     => 'require',
        'shop_id'    => 'require|number',

    ];

    // 验证提示信息
    protected $message = array(
        'uname.require'      => '账号不能为空',
        'uname.require'      => '账号长度为4到8位',
        'urealname.require'      => '姓名不能为空',
        'utel.require'      => '电话不能为空',
        'shop_id.require'     =>  '菜单必须绑定店铺',
        'shop_id.number'     =>  '店铺唯一标识错误',
    );
}