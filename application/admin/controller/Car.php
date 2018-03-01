<?php
//
// +---------------------------------------------------------+
// | PHP version
// +---------------------------------------------------------+
// | Copyright (c) kunming.cn All rights reserved.
// +---------------------------------------------------------+
// | 文件描述 车辆信息
// +---------------------------------------------------------+
// | Author: 曹瑞 <610756247@qq.com>
// +———————————————————+
//2017/11/23 14:34
namespace app\admin\controller;

class Car extends Admin{

    private $model_car, $model_brand, $model_cat, $model_car_info;
    protected function _initialize(){
        parent::_initialize();
        $this->model_car = model('common/ShopCar');
        $this->model_brand = model('common/CarBrand');
        $this->model_cat = model('common/CarBrandCategory');
        $this->model_car_info = model('common/CarInfo');
    }

    public function index(){
        if(request()->isGet()){
            return $this->fetch();
        }else{
            $return_arr = $map = array();
            $map['shop_group_id'] = $this->shop_group_id;
            $map['status'] = array('gt', 0);
            $field = '*';
            $count = $this->model_car->where($map)->count();
            $cars = $this->model_car->where($map)->order('create_time DESC')->field($field)->limit(input('post.limit'))->page(input('post.page'))->select();
            $cars = $cars ? $cars : array();
            $cars = collection($cars)->toArray();
            foreach($cars as &$car){
                $car['status'] = get_common_status($car['status']);
                $car['car_type'] = get_car_type($car['car_type']);
                $car['car_character'] = get_car_character($car['car_character']);
                $car['car_regdate'] = date('Y-m-d', $car['car_regdate']);
                $car['car_issuedate'] = date('Y-m-d', $car['car_issuedate']);
            }
            $return_arr['code'] = 0;
            $return_arr['msg'] = '';
            $return_arr['count'] = $count;
            $return_arr['data'] = $cars;
            echo json_encode($return_arr);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $this->car_exist(array('car_plateno'=>$data['car_plateno']));
            $data['car_regdate'] = strtotime($data['car_regdate']);
            $data['car_issuedate'] = strtotime($data['car_issuedate']);
            $data['shop_user_id'] = $this->uid;
            $data['shop_group_id'] = $this->shop_group_id;
            $data['shop_id'] = $this->shop_id;
            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            if($data['car_brand']){
                $data['car_brand_str'] = get_model_data('CarBrand', $data['car_brand'], 'brand_name', 'brand_id');
            }
            if($data['car_model']){
                $data['car_modelstr'] = get_model_data('CarInfo', $data['car_model'], 'car_name', 'car_id');
            }
            $result = $this->model_car->validate(true)->save($data);
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_car->getError()));
            }
        }else{
            $info = array();
            $this->assign('brands', $this->get_brand());
            $this->assign('info', $info);
            $this->setMeta('添加车辆信息');
            return $this->fetch('edit');
        }
    }

    public function edit($id = 0){
        if(request()->isPost()){
            $data = input('post.');
            $this->is_your_infor($data['car_id']);
            $data['car_regdate'] = strtotime($data['car_regdate']);
            $data['car_issuedate'] = strtotime($data['car_issuedate']);
            $data['shop_user_id'] = $this->uid;
            $data['shop_group_id'] = $this->shop_group_id;
            $data['shop_id'] = $this->shop_id;
            $data['status'] = ($data['status'] == 'on') ? 1 : 0;
            if($data['car_brand']){
                $data['car_brand_str'] = get_model_data('CarBrand', $data['car_brand'], 'brand_name', 'brand_id');
            }
            if($data['car_model']){
                $data['car_modelstr'] = get_model_data('CarInfo', $data['car_model'], 'car_name', 'car_id');
            }
            $result = $this->model_car->validate(true)->save($data, array('customer_id' => $data['customer_id']));
            if ($result !== false) {
                return json_encode(array('status'=> 0));
            }else {
                return json_encode(array('status'=> -888, 'msg'=>$this->model_car->getError()));
            }
        }else{
            $this->is_your_infor($id);
            $info = array();
            /* 获取数据 */
            $info = $this->model_car->field('*')->find($id);
            if ($info === false) {
                $this->assign('show_error', true);
            }
            $info = $info->toArray();
            $this->assign('brands', $this->get_brand());
            $this->assign('info', $info);
            $this->setMeta('编辑车辆信息');
            return $this->fetch('edit');
        }
    }

    public function delete($id = 0){
        $this->is_your_infor($id);
//        $result = $this->model_menu->where(array('group_id' => $id))->delete();
        $result =  $this->model_car->where(array('car_id' => $id))->setField('status', -1);
        if ($result !== false) {
            return json_encode(array('status'=> 0));
        }else {
            return json_encode(array('status'=> -888, 'msg'=>'删除失败'));
        }
    }

    public function get_car_info($brand_id=0){
        if(!$brand_id){
            return json_encode(array('status'=> -888, 'msg'=>'获取车型信息失败！'));
        }
        $cats = $this->model_cat->where(array('brand_id'=>$brand_id))->field('cate_id,cate_name')->order('cate_id ASC')->select();
        $return_arr = array();
        foreach($cats as &$cat){
            $cars = $this->model_car_info->where(array('brand_id'=>$brand_id,'cate_id'=>$cat->cate_id))->field('car_id,car_name')->order('car_id ASC')->select();
            $return_arr[$cat['cate_name']] = $cars ? $cars : array();
        }
        echo json_encode(array('status'=> 0, 'data'=>$return_arr));
    }

    public function car_exist($map = array()){
        $map['shop_group_id'] = $this->shop_group_id;
        $map['status'] = 1;
        $info = $this->model_car->where($map)->count();
        if ($info) {
            echo json_encode(array('status'=> -888, 'msg'=>'车辆信息(车牌)已经存在，请不要重复添加！'));
            exit;
        }
    }

    /**
     * @param int $id
     * 判断该记录是否是该店铺的
     */
    private function is_your_infor($id = 0){
        $info = $this->model_car->field('shop_group_id,shop_id')->find($id);
        if ($info === false) {
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'信息不存在！'));
            }
            exit;
        }
        if($this->shop_group_id != $info->shop_group_id){
            if(request()->isGet()){
                $this->redirect("/index.php?s=admin/index/no_access");
            }else{
                echo json_encode(array('status'=> -888, 'msg'=>'你只能管理您相关的信息！'));
            }
            exit;
        }
    }

    public function get_brand(){
        $ret_arr = array();
        $brands = $this->model_brand->field('brand_id,brand_name,brand_logo')->select();
        foreach($brands as &$brand){
            $brand = $brand->toArray();
            $ret_arr[$this->getFirstCharter($brand['brand_name'])][] = $brand;
        }
        return $ret_arr;
    }

    public function getFirstCharter($str){
        $str = str_replace('·', '', $str);
        if(empty($str)){return '';}
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return null;
    }
}