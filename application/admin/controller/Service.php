<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 服务控制器
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/21 16:28
namespace app\admin\controller;

class Service extends Admin{

    private $model_service, $model_group;
    private $service_type = array('0'=>'标准服务产品', 1=>'多个服务产品组合');

    protected function _initialize(){
        parent::_initialize();
        $this->model_service = model('common/ShopOrderService');
        $this->model_group = model('common/ShopOrderServiceGroup');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_group_id'] = $this->shop_group_id;
            $map['shop_id'] = $this->shop_id;
            $map['is_status'] = 1;
            $field = '*';
            $count = $this->model_service->where($map)->count();
            $services = $this->model_service->where($map)->order('create_time ASC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $services = collection($services)->toArray();
            foreach($services as &$service){
                if($service['service_type'] == 1){
                    $childs_service = !is_array($service['service_multi']) ? explode('||', $service['service_multi']) : array();
                    $service['service_multi'] = '';
                    foreach($childs_service as &$cv){
                        $cv = $this->query_service(array('service_id'=>$cv), 'service_name,service_price');
                        if(empty($cv)){
                            continue;
                        }
                        $service['service_multi'] .= $cv['service_name'].'（'.$cv['service_price'].'元）'.'<br>';
                    }
                }
                $service['service_price'] = number_format($service['service_price']);
                $service['service_type'] = $this->service_type[$service['service_type'] ];
                $service['is_status'] = get_common_status($service['is_status']);
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $services;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->service_exist(array('service_name'=>$data['service_name']));
            $data['shop_id'] = $this->shop_id;
            $data['shop_group_id'] = $this->shop_group_id;
            $data['add_user_id'] = $this->uid;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            if($data['service_type'] == 1 && $data['service_multi'] == ''){
                return json_encode(array('status'=> -888, 'msg'=>'联合产品必须包含子服务！'));
            }
            if($data['service_type'] == 1 && isset($data['service_multi'])){
                if($this->has_multi($data['service_multi'])){
                    return json_encode(array('status'=> -888, 'msg'=>'联合产品只能包含独立服务！'));
                }
                $data['service_multi'] = implode('||', array_unique(array_filter(explode(',', $data['service_multi']))));
            }
            $data_real = '';
            if($data['service_rela']){
                $data_real = $data['service_rela'];
            }
            unset($data['service_rela']);
            $result = $this->model_service->validate(true)->save($data);
            if ($result !== false) {
                if($data_real){
                    $real_arr = array_unique(array_filter(explode(',', $data_real)));
                    $relas = array();
                    foreach($real_arr as &$v){
                        $temp_ = array();
                        $temp_['group_id'] = $v;
                        $temp_['service_id'] = $this->model_service->service_id;
                        $temp_['shop_id'] = $this->shop_id;
                        $temp_['add_user_id'] = $this->uid;
                        $relas[] = $temp_;
                    }
                    model('common/ShopOrderServiceRelation')->saveAll($relas);
                }
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_service->getError()));
            }
        }else{
            $info = array();
            $this->assign('groups', $this->get_all_group());
            $this->assign('services', json_encode($this->get_all_service()));
            $this->assign('info', $info);
            $this->setMeta('添加服务');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['service_id']);
            $data['add_user_id'] = $this->uid;
            $data['shop_id'] = $this->shop_id;
            $data['is_status'] = ($data['is_status'] == 'on') ? 1 : 0;
            if($data['service_type'] == 1 && $data['service_multi'] == ''){
                return json_encode(array('status'=> -888, 'msg'=>'联合产品必须包含子服务！'));
            }
            if($data['service_type'] == 1 && isset($data['service_multi'])){
                if($this->has_multi($data['service_multi'])){
                    return json_encode(array('status'=> -888, 'msg'=>'联合产品只能包含独立服务！'));
                }
                $data['service_multi'] = implode('||', array_unique(array_filter(explode(',', $data['service_multi']))));
            }
            if($data['service_rela']){
                $real_arr = array_unique(array_filter(explode(',', $data['service_rela'])));
                $relas = array();
                foreach($real_arr as &$v){
                    $temp_ = array();
                    $temp_['group_id'] = $v;
                    $temp_['service_id'] = $data['service_id'];
                    $temp_['shop_id'] = $this->shop_id;
                    $temp_['add_user_id'] = $this->uid;
                    $relas[] = $temp_;
                }
                model('common/ShopOrderServiceRelation')->where(array('service_id' => $data['service_id']))->delete();
                model('common/ShopOrderServiceRelation')->saveAll($relas);
                unset($data['service_rela']);
            }
            $result = $this->model_service->validate(true)->save($data, array('service_id' => $data['service_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_service->getError()));
            }
        }else{
            $info = array();
            /* 获取数据 */
            $info = $this->model_service->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('services', json_encode($this->get_all_service()));
            $service_multi = is_array($info['service_multi']) ? $info['service_multi'] : explode('||', $info['service_multi']);
            $child_services = array();
            if($service_multi && !empty($service_multi)){
                $map = array();
                $map['service_id'] = array('in', $service_multi);
                $map['shop_id'] = $this->shop_id;
                $child_services = $this->query_services($map, 'service_id,service_name,service_price');
            }
            //差选组的关联关系
            $currrent_groups = model('common/ShopOrderServiceRelation')->where(array('service_id'=>$id))->column('group_id');
            $currrent_groups = $currrent_groups ? array_unique(array_filter($currrent_groups)) : array();
            $rela_groups = (is_array($currrent_groups) && !empty($currrent_groups)) ? $this->model_group->where(array('sg_id'=>array('in', $currrent_groups)))->field('sg_id,sg_name')->select() : array();
            $this->assign('child_services', $child_services);
            $this->assign('groups', $this->get_all_group());
            $this->assign('currrent_groups', implode(',', $currrent_groups));
            $this->assign('rela_groups', $rela_groups);
            $this->assign('info', $info);
            $this->setMeta('编辑服务');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_service->where(array('shopgroup_id' => $id))->delete();
        $result =  $this->model_service->where(array('service_id' => $id))->setField('is_status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function service_exist($map = array()){
        $map['shop_id'] = $this->shop_id;
        $map['is_status'] = 1;
        $info = $this->model_service->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'服务已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_service->field('service_id,shop_id')->find($id);
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

    private function query_service($map=array(), $field='*'){
        $service = $this->model_service->where($map)->field($field)->find();
        return $service ? $service : array();
    }

    private function query_services($map=array(), $field='*'){
        $services = $this->model_service->where($map)->field($field)->select();
        return $services ? $services : array();
    }

    private function get_all_service(){
        $services = $this->model_service->where(array('is_status'=>1,'shop_id'=>$this->shop_id,'service_type'=>0))->field('service_id,service_name,service_price')->order('create_time ASC')->select();
        return $services ? $services : array();
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

    public function has_multi($ids=array()){
        $map = array();
        $map['is_status'] = 1;
        $map['shop_id'] = $this->shop_id;
        $map['service_type'] = 1;
        $ids = is_array($ids) ? array_unique(array_filter($ids)) : array_unique(array_filter(explode(',', $ids)));
        if(!empty($ids)){
            $map['service_id'] = array('in', $ids);
        }else{
            return 1;
        }
        return $this->model_service->where($map)->count();
    }
}