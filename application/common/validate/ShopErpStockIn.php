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

class ShopErpStockIn extends Validate{

    // 当前验证的规则
    protected $rule = [
        'goods_id'      => 'require|number',
        'goods_price'     => 'require|number|length:1,11',
        'goods_price_sum'     => 'require|number',
        'accpet_shop_id'     => 'require|number',
        'accpet_shop_group_id'    => 'require|number',
        'accept_shop_user_id' => 'require|number'
    ];

    // 验证提示信息
    protected $message = array(
        'goods_id.require'     => '必选关联商品',
        'goods_id.number'      => '商品格式错误',
        'goods_price.require'    => '商品价格不能为空',
        'goods_price.number'     => '商品价格必须是数字',
        'goods_price.length'     => '商品价格长度为1-11位',
        'goods_price_sum.require'=> '数量不能为空',
        'goods_price_sum.number' => '数量格式错误',
        'accpet_shop_id.require'      =>  '必须绑定店铺',
        'accpet_shop_id.number'       =>  '店铺格式错误',
        'accpet_shop_group_id.require'=> '必须绑定集团',
        'accpet_shop_group_id.number' => '集团格式错误',
        'accept_shop_user_id.require'=> '必须绑定接收人',
        'accept_shop_user_id.number' => '接收人格式错误',
    );
}