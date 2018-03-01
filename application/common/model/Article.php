<?php
// +----------------------------------------------------------------------
// | 小矮人 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 文章表 ]
//2017/11/27 16:40
namespace app\common\model;

use app\common\model\Model;

class Article extends Model{
    //设置数据表（不含前缀)
    protected $name = 'shop_article';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'art_id';

    /**
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return array|false|\PDOStatement|string|\think\Collection
     * 获取置顶文章
     */
    public function getTops($page=0,$pagesize=10,$order='create_time DESC',$field='*'){
        $return = $where = array();
        $where['art_top'] = 1;
        $where['is_status'] = 1;
        if($pagesize){
            $list_data = $this->where($where)->field($field)->page($page,$pagesize)->order($order)->select();
        }else{
            $list_data = $this->where($where)->field($field)->order($order)->select();
        }
        $list_data = $list_data ? collection($list_data)->toArray() : array();
        $return['list'] = $list_data;

        $total = $this->where($where)->count();
        $return['page_data']['total']        = $total;
        $return['page_data']['current_page'] = $page;
        //总页数
        $return['page_data']['last_page']    = (int) ceil($total / $pagesize);
        return $return;
    }
    public function getArticle($art_id = 0){
        $result = array();
        if($art_id){
            $map = [
                'art_id' => $art_id,
                'is_status' => 1,
            ];
            $result = model('common/Article')->where($map)->find();
            if($result) $result['cont_text'] = db('shop_article_content')->where(['art_id'=>$art_id])->value('cont_text');
        }
        return $result;
    }
}