<?php
// +----------------------------------------------------------------------
// |  [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 资产控制器 ]
//2017/11/30 14:03
namespace app\admin\controller;

class Assetmgmt extends Admin{
    private $model_assetmgmt;
    protected function _initialize(){
        parent::_initialize();
        $this->model_assetmgmt = model('common/ShopAssetmgmt');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $post_data = request()->param();
            if(isset($post_data['kw'])){
                $map['ass_name|ass_model|ass_remarks'] = array('like', '%'.$post_data['kw'].'%');
            }
            if($post_data['income_type'] != ''){
                $map['acc_direction'] = $post_data['income_type'] ? $post_data['income_type'] : 0;;
            }
            $map['shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_assetmgmt->where($map)->count();
            $as = $this->model_assetmgmt->where($map)->order('ass_buydate DESC,ass_id DESC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $as = $as ? $as : array();
            $flows = collection($as)->toArray();
            foreach($as as &$a){
                $a['ass_shop_user_id'] = get_model_data('ShopUser', $a['ass_shop_user_id'], 'urealname', 'shop_user_id');
                $a['ass_shop_user_id'] = $a['ass_shop_user_id']['urealname'];
                $a['ass_buydate'] = date('Y-m-d', $a['ass_buydate']);
                $a['ass_takedate'] = date('Y-m-d', $a['ass_takedate']);
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $as;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            if($data['ass_buydate']){
                $data['ass_buydate'] = strtotime($data['ass_buydate']);
            }
            if($data['ass_takedate']){
                $data['ass_takedate'] = strtotime($data['ass_takedate']);
            }
            $data['shop_id'] = $this->shop_id;
            $data['shop_group_id'] = $this->shop_group_id;
            $data['ass_shop_user_id'] = $this->uid;
            $result = $this->model_assetmgmt->validate(true)->allowField(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_assetmgmt->getError()));
            }
        }else{
            $this->get_users();
            $info = array();
            $this->assign('info', $info);
            $this->setMeta('添加资产');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['ass_id']);
            $result = $this->model_assetmgmt->validate(true)->allowField(true)->save($data, array('ass_id' => $data['ass_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_assetmgmt->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_assetmgmt->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->get_users();
            $this->assign('info', $info);
            $this->setMeta('编辑资产');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
        $result = $this->model_assetmgmt->where(array('ass_id' => $id))->delete();
//        $result =  $this->model_assetmgmt->where(array('ass_id' => $id))->setField('is_status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_assetmgmt->field('ass_id,shop_id')->find($id);
        if ($info === false) {
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'信息不存在！'));
            }
            exit;
        }
        if($this->shop_id != $info->shop_id){
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'你只能管理您店铺的信息！'));
            }
            exit;
        }
    }

    private function get_users(){
        $model_group = model('common/ShopUser');
        $map['shop_id'] = $this->shop_id;
        $map['status'] = 1;
        $groups = $model_group->where($map)->field('shop_user_id,urealname')->order('urealname ASC')->select();
        $this->assign('users', $groups);
    }
}