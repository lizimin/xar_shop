<?php
namespace app\common\validate;
use app\common\validate\Validate;

class ShopAssetmgmt extends Validate{

    // 当前验证的规则
    protected $rule = [
        'ass_name'     => 'require',
    ];

    // 验证提示信息
    protected $message = array(
        'ass_name.require'      => '资产设备名称必填',
    );
}