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

class ShopErpStockOut extends Validate{

    // 当前验证的规则
    protected $rule = [
        'goods_id'      => 'require|number',
        'goods_price'     => 'require|number|length:1,11',
        'goods_price_sum'     => 'require|number',
        'send_shop_id'     => 'require|number',
        'send_shop_user_id'    => 'require|number',
        'send_shop_group_id' => 'require|number'
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
        'send_shop_id.require'      =>  '必须绑定发出店铺',
        'send_shop_id.number'       =>  '发出店铺格式错误',
        'send_shop_user_id.require'=> '必须绑定发出集团',
        'send_shop_user_id.number' => '发出集团格式错误',
        'send_shop_group_id.require'=> '必须绑定发出人',
        'send_shop_group_id.number' => '发出人格式错误',
    );
}