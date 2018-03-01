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

class GoodsCat extends Validate{

    // 当前验证的规则
    protected $rule = [
        'cat_name'     => 'require|length:1,100',
        'shop_id'     => 'require|number',
        'shop_group_id'    => 'require|number',

    ];

    // 验证提示信息
    protected $message = array(
        'cat_name.require'      => '分类名称不能为空',
        'cat_name.length'      => '分类名称长度为1-100个字',
        'shop_id.require'     =>  '必须绑定店铺',
        'shop_id.number'     =>  '店铺格式错误',
        'shop_group_id.require' => '必须绑定集团',
        'shop_group_id.number' => '集团格式错误',
    );
}