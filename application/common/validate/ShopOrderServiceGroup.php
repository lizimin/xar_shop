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

class ShopOrderServiceGroup extends Validate{

    // 当前验证的规则
    protected $rule = [
        'sg_name'     => 'require',
        'add_user_id'     => 'require|number',
        'shop_id'    => 'require|number',

    ];

    // 验证提示信息
    protected $message = array(
        'sg_name.require'      => '类型名称不能为空',
        'add_user_id.require'      => '创建人不能为空',
        'add_user_id.number'      => '创建人类型错误',
        'shop_id.require'     =>  '菜单必须绑定店铺',
        'shop_id.number'     =>  '店铺唯一标识错误',
    );
}