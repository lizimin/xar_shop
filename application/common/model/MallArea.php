<?php
// +----------------------------------------------------------------------
// | [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [  ]
//2017/12/19 16:10
namespace app\common\model;
use app\common\model\Model;
class MallArea extends Model{
    protected $name = 'mall_area';
    protected $insert = array('create_time');
    protected $update = array('update_time');
}