<?php
//
// +---------------------------------------------------------+
// | PHP version 
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 用户表
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/17 10:10
namespace app\common\model;
use app\common\model\Model;
use TripleDES\TripleDES;
use Util\HttpCurl;
class User extends Model
{
    //设置数据表（不含前缀)
    protected $name = 'member';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'uid';

    //定义类型
    protected $type = array(
        'uid'     => 'integer',
    );

    // 保存自动完成列表
//    protected $auto = array('article_add_time','article_update_time');
    // 新增自动完成列表
//    protected $insert = array('article_add_time');
    // 更新自动完成列表
//    protected $update = array('article_update_time');

}