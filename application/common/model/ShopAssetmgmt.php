<?php
namespace app\common\model;

use app\common\model\Model;

class ShopAssetmgmt extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_assetmgmt';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'ass_id';
}