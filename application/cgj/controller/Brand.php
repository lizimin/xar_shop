<?php
namespace app\cgj\controller;
use EasyWeChat\Foundation\Application;
use think\Cache;
use app\common\controller\BaseApi;
class Brand extends BaseApi
{

	public function getBrand(){
		$result = $list = $list1 = array();
		$brand_list = db('car_brand')->where(['is_status'=>1])->select();

		foreach(range('A', 'Z') as $k=> $v) {
			$list[$v] = array();
		}
		foreach ($brand_list as $key => $value) {
			$charter = $this->getFirstCharter($value['brand_name']);
			if($charter){
				$list[$charter][] = $value;
			}
		}
		$result['hot'] = db('car_brand')->where(['is_hot'=>1, 'is_status'=>1])->select();
		$list1[] = ['alphabet'=>'hot', 'datas'=> $result['hot']];
		foreach ($list as $key => $value) {
			if($value) $list1[] = ['alphabet'=>$key, 'datas'=> $value];
		}

		$result['list'] = $list1;
		return $this->sendResult($result);
	}

	public function getBrandCar(){
		$result = array();
		$brand_id = input('brand_id');
		if($brand_id){
			$map = [
				'brand_id' => $brand_id,
				'is_status' => 1
			];
			// $result = db('car_brand')->where($map)->field('brand_id, brand_name, brand_logo')->find();
			$result = db('car_brand_category')->where($map)->field('cate_id, cate_name, brand_id')->select();
			$db_car_info = db('car_info');
			foreach ($result as $key => $value) {
				$result[$key]['car_info'] = $db_car_info->where(['cate_id'=>$value['cate_id']])->field('car_id,brand_id,cate_id,car_name,maxprice,minprice,seriesPicUrl')->select();
			}
		}
		return $this->sendResult($result);
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



	public function saveBrandImg(){
		$brand_list = db('car_brand')->where(['is_status'=>1])->select();
		$base_path = ROOT_PATH . 'public' . DS . 'uploads';
		foreach ($brand_list as $key => $value) {
			$this->saveImage('http://x.autoimg.cn/m/news/brand/'.$value['autohome_id'].'.jpg', $base_path . DS . 'brand' . DS . $value['autohome_id'] .'.jpg');
			$brand_logo = 'uploads/brand/'. $value['autohome_id'] .'.jpg';
			db('car_brand')->where(['brand_id'=>$value['brand_id']])->update(['brand_logo'=>$brand_logo]);
		}
	}
	public function saveImage($path = '', $image_name = '') {

		$ch = curl_init ($path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$img = curl_exec ($ch);
		curl_close ($ch);
		$fp = fopen($image_name,'w');
		fwrite($fp, $img);
		fclose($fp);
	}
	public function saveAllCarCat(){
		set_time_limit(1200);
		$brand_list = db('car_brand')->where(['is_status'=>1])->select();
		foreach ($brand_list as $key => $value) {
			$this->saveCarCat($value['brand_id'], $value['autohome_id']);
		}
		
	}

	public function test(){
		$this->saveCarCat($value['brand_id'], $value['autohome_id']);
	}

	public function saveCarCat($brand_id, $autohome_id){
		$url = 'https://car.m.autohome.com.cn/ashx/GetSeriesByBrandId.ashx?r=3&b='.intval($autohome_id);
		$res = json_decode(file_get_contents($url), true);
		$db_brand_category = db('car_brand_category');
		$db_car_info = db('car_info');
		$base_path = ROOT_PATH . 'public' . DS . 'uploads';
		foreach ($res['result']['allSellSeries'] as $key => $value) {
			$save_arr = array(
				'brand_id'=> $brand_id,
				'cate_name'=> $value['name'],
				'autohome_id'=> $value['Id'],
				'is_hot'=> 0,
				'is_status'=> 1,
			);
			if(!$db_brand_category->where(['autohome_id'=>$value['Id']])->find()) $db_brand_category->insert($save_arr);
			foreach ($value['SeriesItems'] as $k => $v) {
				if(!$db_car_info->where(['autohome_id'=>$v['id']])->find()){
					$this->saveImage(str_replace('//', 'http://', $v['seriesPicUrl']), $base_path . DS . 'car' . DS . $v['id'] .'.jpg');
					$seriesPicUrl = 'uploads/car/'. $v['id'] .'.jpg';
					$save = [
						'brand_id' => $brand_id,
						'cate_id' => $value['Id'],
						'car_name' => $v['name'],
						'is_status' => 1,
						'add_time' => time(),
						'autohome_id' => $v['id'],
						'maxprice' => $v['maxprice'],
						'minprice' => $v['minprice'],
						'seriesPicUrl' => $seriesPicUrl,
					];
					$db_car_info->insert($save);
				}
			}

		}
	}

}