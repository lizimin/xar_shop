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
//2017/11/22 18:57
namespace app\common\validate;
use app\common\validate\Validate;

class ShopMenu extends Validate{

    // 当前验证的规则
    protected $rule = [
        'p_id'      =>'require',
        'm_title'     => 'require',
        'm_url'       => 'require',
        'm_order'     => 'require|number',
        'm_type'     => 'require|number',
        'shop_id'    => 'require|number',

    ];

    // 验证提示信息
    protected $message = array(
        'p_id.require'      => '所属菜单不能为空',
        'm_title.require'     =>  '菜单名称不能为空',
        'm_url.require'     =>  '菜单连接不能为空',
        'm_order.require'     =>  '菜单排序不能为空',
        'm_order.number'     =>  '菜单排序必须为数字',
        'm_type.require'     =>  '菜单类型不能为空',
        'm_type.number'     =>  '菜单类型必须为数字',
        'shop_id.require'     =>  '菜单必须绑定店铺',
        'shop_id.number'     =>  '店铺唯一标识错误',
    );
}