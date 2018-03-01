<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com 
//+----------------------------------------------------------------------
// | @Author: lishaoenbh@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: lishaoen
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 10:57:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： API接口

namespace app\api\controller;

use app\common\controller\ApiBase;

class Plate extends ApiBase
{
	//定义参数
	protected $model_plate_ocr;
	protected $model_attachment;
	
	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();

		//模型
        try {
			$this->model_attachment = model('common/Attachment');
        } catch (\Exception $e) {
            return $this->sendError($data, $error = 400,$message = $e->getMessage());
		}
	}

	/**
	 * 通过招聘绝对地址MD5值进行车牌识别
	 *
	 * @param string $pathmd5
	 * @return array
	 */
	public function ocrPlate($pathmd5=''){
		//初始化
		$return = $result = $plate_number_arr = $data = $map = array();
		$message = 'success';
		$errcode = 0;
		$plate_number = '';

		//获取参数
		$pathmd5 = input('pathmd5', $pathmd5, 'string');
		if ($pathmd5) {
			//调取模型获取附件信息
			$res =  $this->model_attachment->get_attachment_info($where=['pathmd5'=>$pathmd5]);
			
			if($res){
				if($res['result'] && $res['plate_number'] && $res['car_plate']){
					//车牌三段处理
					$plate_number_arr = json_decode( $res['car_plate'],true);
					
					$return = $plate_number_arr;
				}elseif($res['full_path']){
					$data = $this->plate_vehicle($res['full_path']);
					if($data){
						//车牌三段处理
						$plate_number_string = $data['outputs'][0]['outputValue']['dataValue'];
						$plate_number_data   = json_decode($plate_number_string, true);
						$plate_number        = $plate_number_data['plates'][0]['txt'];

						if($plate_number){
							$plate_number_arr['plate_number'] = $plate_number;

							$plate_number_arr['car_plate']['car_plate_prefix1']   = mb_substr($plate_number,0,1,'utf-8');
							$plate_number_arr['car_plate']['car_plate_prefix2']   = mb_substr($plate_number,1,1,'utf-8');
							$plate_number_arr['car_plate']['car_plate_no']        = mb_substr($plate_number,2,5,'utf-8');

							//把记录插入到车牌识别表
							$update_data = array(
								'result'           => $plate_number_string,
								'plate_number'     => $plate_number,
								'car_plate'        => json_encode($plate_number_arr)
							);
							//调用模型更新
							$res_data = $this->model_attachment->update_attachment_info($where=['pathmd5'=>$pathmd5],$update_data);
						}
				    	$return = $plate_number_arr;
					}else{
						$errcode = 1;
						$message = '车牌识别失败';
					}
				}else{
					$errcode = 1;
					$message = '识别车牌附件路径错误';
				}
			}else{
				$errcode = 1;
				$message = '识别车牌附件不存在';
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}


	/**
	 * 通过车牌识别是否为老用户
	 *
	 * @param string $plate_number
	 * @return void
	 */
	public function getCustomerByPlate($plate_number=''){
		//初始化
		$return = $result = $data = $map = array();
		$message = 'success';
		$errcode = 0;
		
		//获取参数
		$plate_number = input('plate_number', $plate_number, 'string');
		if($plate_number){
			$data = db('shop_car')->where(['car_plateno'=>$plate_number])->field('*')->find();
			if($data){
				$result['plate_number'] = $data['car_plateno'];
				//车辆信息
				$result['car_info']  = $data;
				//获取车辆所属品牌
				$car_brand_arr = [];
				if($data['car_brand']){
					$brand_ids = array($data['car_brand']);
					$car_brand_pid = db('car_brand')->where(['brand_id'=>$data['car_brand']])->column('pid');
					if($car_brand_pid){
						$brand_ids = array_merge($car_brand_pid,$brand_ids);
					}
					$map_brand['brand_id'] = ['in',$brand_ids];
					$car_brand  = db('car_brand')->where($map_brand)->field('*')->select();
					if($car_brand && $data['car_model']){
						foreach($car_brand as $key=>&$val){
							$val['cate_list'] = db('car_category')->where(['brand_id'=>$val['brand_id'],'cate_id'=>$data['car_model']])->field('*')->find();
						}
					}

					$result['car_brand'] = $car_brand;
				}
				//获取车辆品牌下的所属分类
				if($data['car_model']){
					//$cat_ids = get_allfcate_ids($id=$data['car_model'],$model='car_category',$id_field='cate_id',$pid_field='pid',$where=['brand_id'=>$data['car_brand'],'status'=>1],$field='cate_id,pid,brand_id');
				}

				//获取客户信息
				$map_custumer['car_plateno'] = $plate_number;
				$map_custumer['order_status'] = array('gt',0);
				$result['costumer_info'] = db('shop_order_recp')->where($map_custumer)->field('customer_name,customer_sex,customer_tel,customer_id,shop_id,shop_group_id')->group('customer_tel')->limit(5)->order('carorder_id DESC')->select();
				
				$return = $result;
			}
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}
		
		return $this->sendResult($return,$errcode,$message);
	}


	/**
	 * 通过车牌图片全路径进行识别
	 *
	 * @param string $full_path
	 * @return void
	 */
	public function plate_vehicle($full_path=''){
		//初始化
		$result =  $data = $map = array();
		$message = 'success';
		$errcode = 0;

		if($full_path){
			$img_data = file_get_contents($full_path);
			$host = "http://ocrcp.market.alicloudapi.com";
			$path = "/rest/160601/ocr/ocr_vehicle_plate.json";
			$method = "POST";
			$appcode = "08ec5aec985c44b3a1dcaae4f29c483e";
			$headers = array();
			array_push($headers, "Authorization:APPCODE " . $appcode);
			//根据API的要求，定义相对应的Content-Type
			array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
			$querys = "";
			$bodys = "{
				\"inputs\": [
				{
					\"image\": {
						\"dataType\": 50,                     
						\"dataValue\": \"".base64_encode($img_data)."\"
					},
					\"configure\": {
						\"dataType\": 50,
						\"dataValue\": \"{\\\"multi_crop\\\":false}\"
					}
				}]
			}";
			$url = $host . $path;
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_FAILONERROR, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			if (1 == strpos("$".$host, "https://"))
			{
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			}
			curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
			$response = curl_exec($curl);
			if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
				$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $headerSize);
				$body = substr($response, $headerSize);
				// dump($header);
				$result = json_decode($body, true);
			}
			curl_close($curl);
		}
		return $result;
	}

}