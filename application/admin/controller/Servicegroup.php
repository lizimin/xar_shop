<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 服务类型控制器
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 16:28
namespace app\admin\controller;

class Servicegroup extends Admin{

    private $model_group;
    protected function _initialize(){
        parent::_initialize();
        $this->model_group = model('common/ShopOrderServiceGroup');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_id'] = $this->shop_id;
            $field = '*';
//            $groups = $this->model_group->where($map)->order('m_order ASC')->field($field)->select();
            $groups = $this->get_all_group();
            $groups = collection($groups)->toArray();
            foreach($groups as &$group){
                $group['is_status'] = get_common_status($group['is_status']);
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = count($groups);
            $return_arr['data'] = $groups;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->group_exist(array('sg_name'=>$data['sg_name'], 'pid' => $data['pid']));
            $data['shop_id'] = $this->shop_id;
            $data['add_user_id'] = $this->uid;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_group->validate(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_group->getError()));
            }
        }else{
            $info = array();
            $this->assign('groups', $this->get_all_group());
            $this->assign('info', $info);
            $this->setMeta('添加组');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['sg_id']);
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            $result = $this->model_group->validate(true)->save($data, array('sg_id' => $data['sg_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_group->getError()));
            }
        }else{
            $info = array();
            /* 获取数据 */
            $info = $this->model_group->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('groups', $this->get_all_group());
            $this->assign('info', $info);
            $this->setMeta('编辑组');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_group->where(array('shopgroup_id' => $id))->delete();
        $result =  $this->model_group->where(array('sg_id' => $id))->setField('is_status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function group_exist($map = array()){
        $map['shop_id'] = $this->shop_id;
        $map['is_status'] = 1;
        $info = $this->model_group->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'组已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_group->field('sg_id,shop_id')->find($id);
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

    private function get_all_group($pid=0, $level=0){
        $list = array();
        $groups = $this->model_group->where(array('pid'=>$pid,'is_status'=>1,'shop_id'=>$this->shop_id))->field('*')->order('create_time ASC')->select();
        foreach($groups as &$group){
            $group = $group->toArray();
            $group['sg_name'] = str_repeat('&nbsp;', $level*10).$group['sg_name'];
            $list[] = $group;
            $child_groups = $this->model_group->where(array('pid'=>$group['sg_id'],'is_status'=>1,'shop_id'=>$this->shop_id))->count();
            if($child_groups){
                $list = array_merge($list, $this->get_all_group($group['sg_id'], $level+1));
            }
        }
        return $list;
    }
}