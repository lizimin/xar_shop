<?php
namespace app\common\model;

use app\common\model\Model;

class MallProCat extends Model{
    //设置数据表（不含前缀)
    protected $name = 'mall_pro_cat';
    protected $pk = 'cat_id';

    // 新增自动完成列表
    protected $insert = array('create_time');
    // 更新自动完成列表
    protected $update = array('update_time');

    public function get_cat_by_id($id, $field='cat_id,cat_name'){
        if(!$id){
            return false;
        }
        $cat = $this->field($field)->find($id);
        if(!$cat){
            return array();
        }
        return $cat->toArray();
    }


    /**
     * @param int $pid
     * @return array
     * 获取所有分类，不分层级
     * 返回ID数组
     */
    public function get_all_cat_id($pid=0){
        $list = array();
        $cats = $this->where(array('pid'=>$pid,'status'=>1))->field('cat_id')->select();
        foreach($cats as &$cat){
            $cat = $cat->toArray();
            $list[] = $cat['cat_id'];
        }
        return $list;
    }

    /**
     * @param int $pid
     * 默认拉取第一级
     */
    public function get_cat_list($pid=0){
        $map = array();
        $map['pid'] = $pid;
        $map['status'] = 1;
        $field = 'cat_id,cat_name';
        $list = $this->where($map)->field($field)->order('cat_id ASC')->select();
        $list = $list ? collection($list)->toArray() : array();
        foreach ($list as $key => $value) {
//           $list[$key]['sub'] = $this->where(['pid'=>$value['cat_id']])->select();
        }
        return $list;
    }

    /**
     * @param int $pid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 递归获取分类ID数组
     */
    public function get_all_cat_id_recursion($pid=0){
        $list = array($pid);
        $cats = $this->where(array('pid'=>$pid,'status'=>1))->field('cat_id')->select();
        foreach($cats as &$cat){
            $cat = $cat->toArray();
            $list[] = $cat['cat_id'];
            $child_cats = $this->where(array('pid'=>$cat['cat_id'],'status'=>1))->count();
            if($child_cats){
                $list = array_merge($list, $this->get_all_cat_id($cat['cat_id']));
            }
        }
        return $list;
    }
}