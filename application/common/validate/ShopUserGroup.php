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
//2017/11/23 14:43
namespace app\common\validate;
use app\common\validate\Validate;

class ShopUserGroup extends Validate{

    // 当前验证的规则
    protected $rule = [
        'group_name'     => 'require',
        'shop_id'    => 'require|number',

    ];

    // 验证提示信息
    protected $message = array(
        'group_name.require'      => '组名不能为空',
        'shop_id.require'     =>  '菜单必须绑定店铺',
        'shop_id.number'     =>  '店铺唯一标识错误',
    );
}