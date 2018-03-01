<?php
namespace app\common\validate;
use app\common\validate\Validate;

class ShopAccount extends Validate{

    // 当前验证的规则
    protected $rule = [
        'acc_type'     => 'require',
        'acc_direction'=>'require',
    ];

    // 验证提示信息
    protected $message = array(
        'acc_type.require'      => '结算方式必填',
        'acc_direction.require' => '借贷方向必填',
    );
}