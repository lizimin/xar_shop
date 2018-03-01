<?php
namespace app\common\model;

use app\common\model\Model;

class MallProSkuRelation extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_sku_relation';
    protected $pk = 'rel_id';

    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');
}