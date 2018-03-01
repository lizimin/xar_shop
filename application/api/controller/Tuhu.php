<?php
/**
 * 途虎养车数据爬取文件
 */

namespace app\api\controller;
use app\common\controller\ApiBase;
use Util\HttpCurl;
class Tuhu extends ApiBase
{
	//保存途虎汽车品牌数据
	public function getTuhuBrand(){
		$url = 'https://item.tuhu.cn/Car/GetCarBrands2?callback=&_=1516412512805';
		$res = HttpCurl::get($url);
		$brand_arr = json_decode($res, true);
		$save_arr = [];
		foreach ($brand_arr as $key => $value) {
			foreach ($value as $k => $v) {
				$save_arr[] = [
					'brand' => $v['Brand'],
					'first_char' => $key,
					'url' => $v['Url'],
				];
			}
		}
		db('tuhu_car_brand')->insertAll($save_arr);
		dump($save_arr);
	}
	//保存途虎一个品牌的车系数据
	public function saveTuhuSeries($brand = 'A+-+奥迪', $brand_id = 0){
		$url = 'https://item.tuhu.cn/Car/SelOneBrand?callback=&Brand='.str_replace(' ', '+', $brand).'&_=1516412512807';
		dump($url);
		$res = HttpCurl::get($url);
		$series_arr = json_decode($res, true);
		$save_arr = [];
		if($series_arr['OneBrand']){
			foreach ($series_arr['OneBrand'] as $key => $value) {
				$save_arr[] = [
			      "Vehicle" 	=> $value['Vehicle'],
			      "ProductID" 	=> $value['ProductID'],
			      "BrandType" 	=> $value['BrandType'],
			      "Brand" 		=> $value['Brand'],
			      "Url" 		=> $value['Url'],
			      "Src" 		=> $value['Src'],
			      "IsBaoYang" 	=> $value['IsBaoYang'],
			      "CarName" 	=> $value['CarName'],
			      "appSrc" 		=> $value['appSrc'],
			      "Image" 		=> $value['Image'],
			      "ImageUrl" 	=> $value['ImageUrl'],
			      "Tires" 		=> $value['Tires'],
			      "SpecialTireSize"   => $value['SpecialTireSize'],
			      "OriginalIsBaoyang" => $value['OriginalIsBaoyang'],
			      "Priority2" 		  => $value['Priority2'],
			      "Priority3" 		  => $value['Priority3'],
			      "VehicleSeries" 	  => $value['VehicleSeries'],
			      "brand_id"		  => $brand_id
				];
			}
			// dump($save_arr);
			db('tuhu_car_series')->insertAll($save_arr);
		}
	}
	//通过查询所有的品牌数据、插入所有的车系数据
	public function saveAllSeries(){
		$car_brand = db('tuhu_car_brand')->select();
		foreach ($car_brand as $key => $value) {
			$this->saveTuhuSeries($value['brand'], $value['brand_id']);
		}
	}

	//保存某个车系的排量数据
	public function saveTuhuVolume($VehicleID = 'VE-AUDF96AE', $brand_id = 0, $series_id = 0){
		$url = 'https://item.tuhu.cn/Car/SelectVehicle?callback=&VehicleID='.$VehicleID.'&_=1516412512809';
		dump($url);
		$res = HttpCurl::get($url);
		$volume_arr = json_decode($res, true);
		$save_arr = [];
		foreach ($volume_arr['PaiLiang'] as $key => $value) {
			$save_arr[] = [
				'brand_id' => $brand_id,
				'series_id' => $series_id,
				'key' => $value['Key'],
				'volume' => $value['Value'],
			];
		}
		db('tuhu_car_volume')->insertAll($save_arr);
	}
	//保存所有车系的排量数据
	public function saveAllVolume(){
		$series = db('tuhu_car_series')->where('series_id > 2531')->field('ProductID, brand_id, series_id')->select();
		foreach ($series as $key => $value) {
			$this->saveTuhuVolume($value['ProductID'], $value['brand_id'], $value['series_id']);
		}
	}	
	//保存某个车系某排量的年份数据
	public function saveTuhuYear($VehicleID = 'VE-AUDF96AE', $Volume = '2.0L', $brand_id = 0, $series_id = 0){
		$url = 'https://item.tuhu.cn/Car/SelectVehicle?callback=&VehicleID='.$VehicleID.'&PaiLiang='.$Volume.'&_=1516412512810';
		dump($url);
		$res = HttpCurl::get($url);
		$volume_arr = json_decode($res, true);
		$save_arr = [];
		if($volume_arr['Nian']){
			foreach ($volume_arr['Nian'] as $key => $value) {
				$save_arr[] = [
					'brand_id' => $brand_id,
					'series_id' => $series_id,
					'key' => $value['Key'],
					'volume' => $Volume,
					'year' => $value['Value'],
				];
			}
			db('tuhu_car_year')->insertAll($save_arr);
		}
	}
	//保存所有车系的排量数据
	public function saveAllYear(){
		$series = db('tuhu_car_series')->where('series_id > 3926')->field('ProductID, brand_id, series_id')->select();
		foreach ($series as $key => $value) {
			$volume_arr = db('tuhu_car_volume')->where(['series_id'=>$value['series_id']])->select();
			foreach ($volume_arr as $k => $v) {
				$this->saveTuhuYear($value['ProductID'], $v['volume'], $value['brand_id'], $value['series_id']);
			}
		}
	}
	//保存途虎  保养汽车的数据  
	public function baoyangData(){
		// $tuhu_year = db('tuhu_car_year')->where(['id'=>1])->select();
		$id = input('id');
		$tuhu_year = db('tuhu_car_year')->select();
		foreach ($tuhu_year as $key => $value) {
			$url = 'https://by.tuhu.cn/change/GetBaoYangPackages.html?vehicle=%7B%22Brand%22%3A%22A%22%2C%22VehicleId%22%3A%22'.$value['key'].'%22%2C%22PaiLiang%22%3A%22'.$value['volume'].'%22%2C%22Nian%22%3A%22'.$value['year'].'%22%2C%22Tid%22%3A%22%22%2C%22Properties%22%3A%5B%7B%22Property%22%3A%22%E5%8F%91%E5%8A%A8%E6%9C%BA%22%2C%22PropertyValue%22%3A%22DBR%22%7D%5D%7D';
			dump($url);
			$res = HttpCurl::get($url);

			$data = json_decode($res, true);
			// dump($data);
			if($data){
				$save_arr = [
					'brand_id' => $value['brand_id'],
					'series_id' => $value['series_id'],
					'year' => $value['year'],
					'volume' => $value['volume'],
					'by_data' => $res,
					'by_volume' => $data[0]['Items'][0]['Items'][0]['DataTip'],
				];
				db('tuhu_baoyang')->insert($save_arr);
				dump($save_arr);
			}
		}
	}

}