<?php
// +----------------------------------------------------------------------
// |  [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://blog.csdn.net/lz610756247
// +----------------------------------------------------------------------
// | Author: 曹瑞
// +----------------------------------------------------------------------
// [ 流水控制器 ]
//2017/11/30 14:03
namespace app\admin\controller;

class Tipflow extends Admin{
    private $model_account;
    protected function _initialize(){
        parent::_initialize();
        $this->model_account = model('common/ShopAccount');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $post_data = request()->param();
            if(isset($post_data['start_time']) && isset($post_data['end_time'])){
                $post_data['start_time'] = $post_data['start_time'] ? strtotime($post_data['start_time']) : time();
                $post_data['end_time'] = $post_data['end_time'] ? strtotime($post_data['end_time']) + 86400 : time()+86400;
                $map['acc_date'] = array('between', array($post_data['start_time'], $post_data['end_time']));
            }
            if(isset($post_data['kw'])){
                $map['recp_job_sn|shop_job_sn|acc_title|acc_oddnum|acc_customer_name|acc_remarks'] = array('like', '%'.$post_data['kw'].'%');
            }
            if($post_data['income_type'] != ''){
                $map['acc_direction'] = $post_data['income_type'] ? $post_data['income_type'] : 0;;
            }
            $map['shop_id'] = $this->shop_id;
            $field = '*';
            $count = $this->model_account->where($map)->count();
            $flows = $this->model_account->where($map)->order('acc_date DESC,acc_id DESC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $flows = $flows ? $flows : array();
            $flows = collection($flows)->toArray();
            foreach($flows as &$flow){
                $flow['acc_type'] = config('data_option')['acc_type'][$flow['acc_type']];
                $flow['acc_direction'] = config('data_option')['acc_direction'][$flow['acc_direction']];
                if($flow['acc_customer_id']){
                    $flow['acc_customer_id'] = get_model_data('ShopCustomer', $flow['acc_customer_id'], 'crealname', 'customer_id');
                }else if($flow['acc_customer_id'] == 0){
                    $flow['acc_customer_id'] = '';
                }
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $flows;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $data['shop_id'] = $this->shop_id;
            $data['shop_group_id'] = $this->shop_group_id;
            $data['acc_date'] = time();
            $data['add_shop_user_id'] = $this->uid;
            $data['acc_is_status'] = 1;
            $result = $this->model_account->validate(true)->allowField(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_account->getError()));
            }
        }else{
            $info = array();
            $this->assign('info', $info);
            $this->setMeta('添加流水');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['acc_id']);
            $result = $this->model_account->validate(true)->allowField(true)->save($data, array('acc_id' => $data['acc_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_account->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_account->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('info', $info);
            $this->setMeta('编辑流水');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('shopmenu_id' => $id))->delete();
        $result =  $this->model_account->where(array('acc_id' => $id))->setField('acc_is_status', -1);
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
        $info = $this->model_account->field('acc_id,shop_id')->find($id);
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
}