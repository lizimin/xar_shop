<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 文章验证器
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/17 14:00
namespace app\common\validate;
use app\common\validate\Validate;

class Thread extends Validate{

    // 当前验证的规则
    protected $rule = array(
//		'title'     => 'require|unique:project_task',
        'subject'     => 'require',
        'author'     => 'require|number|gt:0',
        'cover_img' => 'require'
    );

    // 验证提示信息
    protected $message = array(
        'subject.require'     =>  '标题不能为空',
        'author.require'     =>  '作者不能为空',
        'author.number' => '作者格式错误',
        'author.gt' => '作者ID错误',
        'cover_img.require' => '封面不能为空'
    );

}