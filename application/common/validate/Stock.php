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
        'goods_num'     => 'require|number|length:1,11',
        'goods_unit'     => 'require|number',
        'shop_id'     => 'require|number',
        'shop_group_id'    => 'require|number',

    ];

    // 验证提示信息
    protected $message = array(
        'goods_num.require'      => '商品数量不能为空',
        'goods_num.number'      => '商品数量必须是数字',
        'goods_num.length'      => '商品数量长度为1-11位',
        'goods_unit.require'      => '单位不能为空',
        'goods_unit.number'      => '单位格式错误',
        'shop_id.require'     =>  '必须绑定店铺',
        'shop_id.number'     =>  '店铺格式错误',
        'shop_group_id.require' => '必须绑定集团',
        'shop_group_id.number' => '集团格式错误',
    );
}