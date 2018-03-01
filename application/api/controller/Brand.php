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

use think\Cache;
use app\common\controller\ApiBase;

class Brand extends ApiBase
{
	/**
	 * 初始化
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();
		
	}

	 /**
	  * 获取品牌
	  *
	  * @return void
	  */
	public function getBrand(){
		//初始化
		$return = $list = $list1 = [];
		$message = 'success';
		$errcode = 0;
		$brand_list = db('car_brand')->where(['pid'=>0,'status'=>1])->select();
		if($brand_list){
			foreach(range('A', 'Z') as $k=> $v) {
				$list[$v] = array();
			}
			
			foreach ($brand_list as $key => $value) {
				$charter = $this->getFirstCharter($value['brand_name']);
				if($charter){
					$list[$charter][] = $value;
				}
			}
	
			$return['hot'] = db('car_brand')->where(['pid'=>0,'is_hot'=>1, 'status'=>1])->select();
			$list1[] = ['alphabet'=>'hot', 'datas'=> $return['hot']];
			foreach ($list as $key => $value) {
				if($value) $list1[] = ['alphabet'=>$key, 'datas'=> $value];
			}
	
			$return['list'] = $list1;
		}else{
			$errcode = 1;
			$message = '品牌信息为空';
		}
		
		return $this->sendResult($return,$errcode,$message);
	}

	/**
	 * 获取品牌下的车型
	 *
	 * @param integer $brand_id
	 * @return void
	 */
	public function getBrandCar($brand_id=0){
		//初始化
		$return  =  [];
		$message = 'success';
		$errcode = 0;

		$brand_id = input('brand_id',$brand_id,'intval');
		if($brand_id){
			$map = [
				'pid' => $brand_id,
				'status' => 1
			];
			//获取大品牌下的子品牌
			$list = db('car_brand')->where($map)->field('*')->select();
			$tree = [];
			if($list){
				foreach($list as $key=>&$val){
					$val['car_category'] = db('car_category')->where(['brand_id'=>$val['brand_id'],'status'=>1])->field('*')->select();
					/*
					if($val['car_category']){
						foreach($val['car_category'] as $k=>&$v){
							$v['car_info'] = db('car_info')->where(['pr_id'=>$val['cate_id'],'status'=>1])->field('*')->select();
						}
					}*/
				}
				//$tree = list_to_tree($list, $pk='cate_id', $pid='pid', $child='children', $root=0);
			}
			$return = $list;
		}else{
			$errcode = 1;
			$message = '请求操作不合法';
		}

		return $this->sendResult($return,$errcode,$message);
	}

	/**
	 * 获取第一个字母
	 *
	 * @param string $str
	 * @return void
	 */
	public function getFirstCharter($str=''){
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


	/**
	 * 保存品牌图片
	 *
	 * @return void
	 */
	public function saveBrandImg(){
		$brand_list = db('car_brand')->where(['is_status'=>1])->select();
		$base_path = ROOT_PATH . 'public' . DS . 'uploads';
		foreach ($brand_list as $key => $value) {
			$this->saveImage('http://x.autoimg.cn/m/news/brand/'.$value['autohome_id'].'.jpg', $base_path . DS . 'brand' . DS . $value['autohome_id'] .'.jpg');
			$brand_logo = 'uploads/brand/'. $value['autohome_id'] .'.jpg';
			db('car_brand')->where(['brand_id'=>$value['brand_id']])->update(['brand_logo'=>$brand_logo]);
		}
	}

	/**
	 * 保存图片方法
	 *
	 * @param string $path
	 * @param string $image_name
	 * @return void
	 */
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

	/**
	 * 保存品牌下车型
	 *
	 * @return void
	 */
	public function saveAllCarCat(){
		set_time_limit(1200);
		$brand_list = db('car_brand')->where(['status'=>1, 'pid'=>0])->select();
		foreach ($brand_list as $key => $value) {
			$this->saveCarCat($value['brand_id'], $value['autohome_id']);
		}
		
	}

	/**
	 * 保存汽车分类
	 *
	 * @param integer $brand_id
	 * @param integer $autohome_id
	 * @return void
	 */
	public function saveCarCat($brand_id=0, $autohome_id=0){
		$url = 'https://car.m.autohome.com.cn/ashx/GetSeriesByBrandId.ashx?r=3&b='.intval($autohome_id);
		$res = json_decode(file_get_contents($url), true);
		$db_brand = db('car_brand');
		$car_category = db('car_category');
		// dump($res);
		$base_path = ROOT_PATH . 'public' . DS . 'uploads';
		foreach ($res['result']['allSellSeries'] as $key => $value) {
			$save_arr = array(
				'pid'=> $brand_id,
				'brand_name'=> $value['name'],
				'autohome_id'=> $value['Id'],
				'is_hot'=> 0,
				'status'=> 1,
			);
			$category = $db_brand->where(['brand_name'=>$value['name']])->find();
			$tmp_brand_id = $category['brand_id'];
			if(!$category){
				$db_brand->insert($save_arr);
				$tmp_brand_id = $db_brand->getLastInsID();
			}
			foreach ($value['SeriesItems'] as $k => $v) {
				if(!$car_category->where(['autohome_id'=>$v['id']])->find()){
					$this->saveImage(str_replace('//', 'http://', $v['seriesPicUrl']), $base_path . DS . 'car' . DS . $v['id'] .'.jpg');
					$seriesPicUrl = 'uploads/car/'. $v['id'] .'.jpg';
					$save = [
						'brand_id' => $tmp_brand_id,
						'car_name' => $v['name'],
						'status' => 1,
						'add_time' => time(),
						'autohome_id' => $v['id'],
						'maxprice' => $v['maxprice'],
						'minprice' => $v['minprice'],
						'seriesPicUrl' => $seriesPicUrl,
					];
					$car_category->insert($save);
				}
			}

		}
	}


	public function test(){
		$this->saveCarCat(1, 33);
	}


	public function savebrand(){
		$url = 'https://cars.app.autohome.com.cn/cars_v8.5.5/cars/brands-pm2.json';
		$brand_json = file_get_contents($url);
		

		$brand_json = '{"result":{"timestamp":636474657955280098,"brandlist":[{"letter":"A","list":[{"id":33,"name":"奥迪","imgurl":"https://x.autoimg.cn/app/image/brands/33.png","sort":6.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":34,"name":"阿尔法·罗密欧","imgurl":"https://x.autoimg.cn/app/image/brands/34.png","sort":66.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":272,"name":"ARCFOX","imgurl":"https://x.autoimg.cn/app/image/brands/272.png","sort":79.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":35,"name":"阿斯顿·马丁","imgurl":"https://x.autoimg.cn/app/image/brands/35.png","sort":84.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":276,"name":"ALPINA","imgurl":"https://x.autoimg.cn/app/image/brands/276.png","sort":116.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":117,"name":"AC Schnitzer","imgurl":"https://x.autoimg.cn/app/image/brands/117.png","sort":124.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":221,"name":"安凯客车","imgurl":"https://x.autoimg.cn/app/image/brands/221.png","sort":182.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"B","list":[{"id":14,"name":"本田","imgurl":"https://x.autoimg.cn/app/image/brands/14.png","sort":2.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":38,"name":"别克","imgurl":"https://x.autoimg.cn/app/image/brands/38.png?r\u003d38","sort":5.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":36,"name":"奔驰","imgurl":"https://x.autoimg.cn/app/image/brands/36.png","sort":7.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":15,"name":"宝马","imgurl":"https://x.autoimg.cn/app/image/brands/15.png","sort":12.0,"tipinfo":"","brandmusem":"品牌展馆","musemurl":"autohome://insidebrowser?fromtype\u003d102\u0026title\u003d%e5%93%81%e7%89%8c%e5%b1%95%e9%a6%86\u0026url\u003dhttps%3A%2F%2Fvr.autohome.com.cn%2Fbrand%2F10000%3Fpvareaid%3D3267158%26_ahrotate%3D1\u0026navigationbarstyle\u003d2"},{"id":120,"name":"宝骏","imgurl":"https://x.autoimg.cn/app/image/brands/120.png","sort":16.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":75,"name":"比亚迪","imgurl":"https://x.autoimg.cn/app/image/brands/75.png?r\u003d20150701?r\u003d20150702","sort":17.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":13,"name":"标致","imgurl":"https://x.autoimg.cn/app/image/brands/13.png","sort":31.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":40,"name":"保时捷","imgurl":"https://x.autoimg.cn/app/image/brands/40.png","sort":35.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":27,"name":"北京","imgurl":"https://x.autoimg.cn/app/image/brands/27.png","sort":48.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":203,"name":"北汽幻速","imgurl":"https://x.autoimg.cn/app/image/brands/203.png","sort":50.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":95,"name":"奔腾","imgurl":"https://x.autoimg.cn/app/image/brands/95.png","sort":54.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":231,"name":"宝沃","imgurl":"https://x.autoimg.cn/app/image/brands/231.png","sort":65.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":173,"name":"北汽绅宝","imgurl":"https://x.autoimg.cn/app/image/brands/27.png","sort":68.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":39,"name":"宾利","imgurl":"https://x.autoimg.cn/app/image/brands/39.png","sort":70.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":271,"name":"比速汽车","imgurl":"https://x.autoimg.cn/app/image/brands/271.png","sort":71.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":208,"name":"北汽新能源","imgurl":"https://x.autoimg.cn/app/image/brands/208.png","sort":73.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":154,"name":"北汽制造","imgurl":"https://x.autoimg.cn/app/image/brands/154.png","sort":89.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":143,"name":"北汽威旺","imgurl":"https://x.autoimg.cn/app/image/brands/143.png","sort":90.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":140,"name":"巴博斯","imgurl":"https://x.autoimg.cn/app/image/brands/140.png","sort":113.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":37,"name":"布加迪","imgurl":"https://x.autoimg.cn/app/image/brands/37.png","sort":127.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":301,"name":"北汽道达","imgurl":"https://x.autoimg.cn/app/image/brands/301.png","sort":167.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"C","list":[{"id":76,"name":"长安","imgurl":"https://x.autoimg.cn/app/image/brands/76.png?r\u003d20150701?r\u003d20150702","sort":14.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":163,"name":"长安欧尚","imgurl":"https://x.autoimg.cn/app/image/brands/163.png","sort":39.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":77,"name":"长城","imgurl":"https://x.autoimg.cn/app/image/brands/77.png?r\u003d20150701?r\u003d20150702","sort":64.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":79,"name":"昌河","imgurl":"https://x.autoimg.cn/app/image/brands/79.png?r\u003d20150701?r\u003d20150702","sort":76.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":294,"name":"长安轻型车","imgurl":"https://x.autoimg.cn/app/image/brands/294.png","sort":92.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":299,"name":"长安跨越","imgurl":"https://x.autoimg.cn/app/image/brands/299.png","sort":125.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":196,"name":"成功汽车","imgurl":"https://x.autoimg.cn/app/image/brands/196.png","sort":142.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"D","list":[{"id":1,"name":"大众","imgurl":"https://x.autoimg.cn/app/image/brands/1.png","sort":1.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":81,"name":"东南","imgurl":"https://x.autoimg.cn/app/image/brands/81.png?r\u003d20150701?r\u003d20150702","sort":43.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":259,"name":"东风风光","imgurl":"https://x.autoimg.cn/app/image/brands/259.png","sort":44.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":165,"name":"东风风行","imgurl":"https://x.autoimg.cn/app/image/brands/165.png","sort":49.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":113,"name":"东风风神","imgurl":"https://x.autoimg.cn/app/image/brands/113.png","sort":56.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":169,"name":"DS","imgurl":"https://x.autoimg.cn/app/image/brands/169.png?r\u003d20150515","sort":83.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":41,"name":"道奇","imgurl":"https://x.autoimg.cn/app/image/brands/41.png","sort":93.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":32,"name":"东风","imgurl":"https://x.autoimg.cn/app/image/brands/32.png","sort":94.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":142,"name":"东风小康","imgurl":"https://x.autoimg.cn/app/image/brands/142.png?r\u003d20150515","sort":95.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":187,"name":"东风风度","imgurl":"https://x.autoimg.cn/app/image/brands/187.png","sort":103.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":280,"name":"电咖","imgurl":"https://x.autoimg.cn/app/image/brands/280.png","sort":109.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"F","list":[{"id":3,"name":"丰田","imgurl":"https://x.autoimg.cn/app/image/brands/3.png","sort":3.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":8,"name":"福特","imgurl":"https://x.autoimg.cn/app/image/brands/8.png","sort":9.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":42,"name":"法拉利","imgurl":"https://x.autoimg.cn/app/image/brands/42.png","sort":81.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":11,"name":"菲亚特","imgurl":"https://x.autoimg.cn/app/image/brands/11.png","sort":99.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":96,"name":"福田","imgurl":"https://x.autoimg.cn/app/image/brands/96.png","sort":102.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":282,"name":"福田乘用车","imgurl":"https://x.autoimg.cn/app/image/brands/282.png","sort":106.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":141,"name":"福迪","imgurl":"https://x.autoimg.cn/app/image/brands/141.png","sort":112.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":197,"name":"福汽启腾","imgurl":"https://x.autoimg.cn/app/image/brands/197.png","sort":154.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"G","list":[{"id":82,"name":"广汽传祺","imgurl":"https://x.autoimg.cn/app/image/brands/82.png?r\u003d20150701?r\u003d20150702","sort":21.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":152,"name":"观致","imgurl":"https://x.autoimg.cn/app/image/brands/152.png","sort":69.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":112,"name":"GMC","imgurl":"https://x.autoimg.cn/app/image/brands/112.png","sort":108.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":304,"name":"国金汽车","imgurl":"https://x.autoimg.cn/app/image/brands/304.png","sort":121.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":108,"name":"广汽吉奥","imgurl":"https://x.autoimg.cn/app/image/brands/108.png","sort":128.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":116,"name":"光冈","imgurl":"https://x.autoimg.cn/app/image/brands/116.png","sort":150.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"H","list":[{"id":181,"name":"哈弗","imgurl":"https://x.autoimg.cn/app/image/brands/181.png","sort":11.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":86,"name":"海马","imgurl":"https://x.autoimg.cn/app/image/brands/86.png","sort":53.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":267,"name":"汉腾汽车","imgurl":"https://x.autoimg.cn/app/image/brands/267.png","sort":59.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":91,"name":"红旗","imgurl":"https://x.autoimg.cn/app/image/brands/91.png","sort":75.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":87,"name":"华泰","imgurl":"https://x.autoimg.cn/app/image/brands/87.png?r\u003d20150519","sort":101.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":97,"name":"黄海","imgurl":"https://x.autoimg.cn/app/image/brands/97.png","sort":104.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":43,"name":"悍马","imgurl":"https://x.autoimg.cn/app/image/brands/43.png","sort":110.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":260,"name":"华泰新能源","imgurl":"https://x.autoimg.cn/app/image/brands/260.png","sort":132.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":24,"name":"哈飞","imgurl":"https://x.autoimg.cn/app/image/brands/24.png","sort":144.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":220,"name":"华颂","imgurl":"https://x.autoimg.cn/app/image/brands/220_1.png","sort":164.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":150,"name":"海格","imgurl":"https://x.autoimg.cn/app/image/brands/150.png","sort":169.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":85,"name":"华普","imgurl":"https://x.autoimg.cn/app/image/brands/85.png","sort":183.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":164,"name":"恒天","imgurl":"https://x.autoimg.cn/app/image/brands/164.png","sort":184.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":184,"name":"华骐","imgurl":"https://x.autoimg.cn/app/image/brands/184.png","sort":187.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":237,"name":"华利","imgurl":"https://x.autoimg.cn/app/image/brands/237.png","sort":190.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":245,"name":"华凯","imgurl":"https://x.autoimg.cn/app/image/brands/245.png","sort":191.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"J","list":[{"id":25,"name":"吉利汽车","imgurl":"https://x.autoimg.cn/app/image/brands/25.png","sort":4.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":46,"name":"Jeep","imgurl":"https://x.autoimg.cn/app/image/brands/46.png","sort":19.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":44,"name":"捷豹","imgurl":"https://x.autoimg.cn/app/image/brands/44.png","sort":38.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":84,"name":"江淮","imgurl":"https://x.autoimg.cn/app/image/brands/84.png?r\u003d20160919","sort":41.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":83,"name":"金杯","imgurl":"https://x.autoimg.cn/app/image/brands/83.png?r\u003d20150701?r\u003d20150702","sort":78.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":119,"name":"江铃","imgurl":"https://x.autoimg.cn/app/image/brands/119.png","sort":86.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":210,"name":"江铃集团轻汽","imgurl":"https://x.autoimg.cn/app/image/brands/210.png","sort":131.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":151,"name":"九龙","imgurl":"https://x.autoimg.cn/app/image/brands/151.png","sort":143.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":270,"name":"江铃集团新能源","imgurl":"https://x.autoimg.cn/app/image/brands/270.png","sort":145.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":145,"name":"金龙","imgurl":"https://x.autoimg.cn/app/image/brands/145.png","sort":146.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":175,"name":"金旅","imgurl":"https://x.autoimg.cn/app/image/brands/175.png","sort":160.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"K","list":[{"id":47,"name":"凯迪拉克","imgurl":"https://x.autoimg.cn/app/image/brands/47.png","sort":23.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":214,"name":"凯翼","imgurl":"https://x.autoimg.cn/app/image/brands/214_1.png","sort":91.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":101,"name":"开瑞","imgurl":"https://x.autoimg.cn/app/image/brands/101.png","sort":96.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":9,"name":"克莱斯勒","imgurl":"https://x.autoimg.cn/app/image/brands/9.png","sort":97.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":199,"name":"卡威","imgurl":"https://x.autoimg.cn/app/image/brands/199_1.png","sort":117.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":100,"name":"科尼赛克","imgurl":"https://x.autoimg.cn/app/image/brands/100.png","sort":134.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":219,"name":"康迪全球鹰","imgurl":"https://x.autoimg.cn/app/image/brands/219.png","sort":156.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":109,"name":"KTM","imgurl":"https://x.autoimg.cn/app/image/brands/109.png","sort":157.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":224,"name":"卡升","imgurl":"https://x.autoimg.cn/app/image/brands/224.png","sort":158.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":156,"name":"卡尔森","imgurl":"https://x.autoimg.cn/app/image/brands/156.png","sort":173.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"L","list":[{"id":279,"name":"领克","imgurl":"https://x.autoimg.cn/app/image/brands/279.png","sort":22.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":49,"name":"路虎","imgurl":"https://x.autoimg.cn/app/image/brands/49.png","sort":27.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":52,"name":"雷克萨斯","imgurl":"https://x.autoimg.cn/app/image/brands/52.png","sort":32.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":53,"name":"铃木","imgurl":"https://x.autoimg.cn/app/image/brands/53.png","sort":40.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":78,"name":"猎豹汽车","imgurl":"https://x.autoimg.cn/app/image/brands/78.png","sort":45.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":51,"name":"林肯","imgurl":"https://x.autoimg.cn/app/image/brands/51.png","sort":46.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":10,"name":"雷诺","imgurl":"https://x.autoimg.cn/app/image/brands/10.png?r\u003d20150406","sort":47.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":88,"name":"陆风","imgurl":"https://x.autoimg.cn/app/image/brands/88.png?r\u003d20150701?r\u003d20150702","sort":57.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":48,"name":"兰博基尼","imgurl":"https://x.autoimg.cn/app/image/brands/48.png","sort":61.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":80,"name":"力帆汽车","imgurl":"https://x.autoimg.cn/app/image/brands/80.png?r\u003d20150701?r\u003d20150702","sort":74.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":54,"name":"劳斯莱斯","imgurl":"https://x.autoimg.cn/app/image/brands/54.png","sort":85.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":50,"name":"路特斯","imgurl":"https://x.autoimg.cn/app/image/brands/50.png","sort":123.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":118,"name":"Lorinser","imgurl":"https://x.autoimg.cn/app/image/brands/118.png","sort":135.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":89,"name":"莲花汽车","imgurl":"https://x.autoimg.cn/app/image/brands/89.png","sort":141.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":124,"name":"理念","imgurl":"https://x.autoimg.cn/app/image/brands/124.png","sort":147.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":241,"name":"LOCAL MOTORS","imgurl":"https://x.autoimg.cn/app/image/brands/241.png","sort":152.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":204,"name":"陆地方舟","imgurl":"https://x.autoimg.cn/app/image/brands/204.png","sort":168.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"M","list":[{"id":58,"name":"马自达","imgurl":"https://x.autoimg.cn/app/image/brands/58.png","sort":13.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":20,"name":"名爵","imgurl":"https://x.autoimg.cn/app/image/brands/20.png","sort":28.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":57,"name":"玛莎拉蒂","imgurl":"https://x.autoimg.cn/app/image/brands/57.png","sort":55.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":56,"name":"MINI","imgurl":"https://x.autoimg.cn/app/image/brands/56.png","sort":67.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":129,"name":"迈凯伦","imgurl":"https://x.autoimg.cn/app/image/brands/129.png","sort":98.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":55,"name":"迈巴赫","imgurl":"https://x.autoimg.cn/app/image/brands/55.png","sort":122.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":168,"name":"摩根","imgurl":"https://x.autoimg.cn/app/image/brands/168.png","sort":133.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"N","list":[{"id":130,"name":"纳智捷","imgurl":"https://x.autoimg.cn/app/image/brands/130.png","sort":62.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":213,"name":"南京金龙","imgurl":"https://x.autoimg.cn/app/image/brands/213.png","sort":174.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"O","list":[{"id":60,"name":"讴歌","imgurl":"https://x.autoimg.cn/app/image/brands/60.png","sort":63.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":59,"name":"欧宝","imgurl":"https://x.autoimg.cn/app/image/brands/59.png","sort":163.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":146,"name":"欧朗","imgurl":"https://x.autoimg.cn/app/image/brands/146.png","sort":192.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"P","list":[{"id":61,"name":"帕加尼","imgurl":"https://x.autoimg.cn/app/image/brands/61.png","sort":111.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"Q","list":[{"id":62,"name":"起亚","imgurl":"https://x.autoimg.cn/app/image/brands/62.png","sort":24.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":26,"name":"奇瑞","imgurl":"https://x.autoimg.cn/app/image/brands/26.png","sort":30.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":122,"name":"启辰","imgurl":"https://x.autoimg.cn/app/image/brands/122.png","sort":34.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":235,"name":"前途","imgurl":"https://x.autoimg.cn/app/image/brands/235.png?r\u003d20150515","sort":155.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"R","list":[{"id":63,"name":"日产","imgurl":"https://x.autoimg.cn/app/image/brands/63.png","sort":10.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":19,"name":"荣威","imgurl":"https://x.autoimg.cn/app/image/brands/19.png","sort":20.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":296,"name":"瑞驰新能源","imgurl":"https://x.autoimg.cn/app/image/brands/296.png","sort":137.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":174,"name":"如虎","imgurl":"https://x.autoimg.cn/app/image/brands/174.png","sort":161.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":103,"name":"瑞麒","imgurl":"https://x.autoimg.cn/app/image/brands/103.png","sort":162.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"S","list":[{"id":67,"name":"斯柯达","imgurl":"https://x.autoimg.cn/app/image/brands/67.png","sort":26.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":68,"name":"三菱","imgurl":"https://x.autoimg.cn/app/image/brands/68.png","sort":36.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":65,"name":"斯巴鲁","imgurl":"https://x.autoimg.cn/app/image/brands/65.png","sort":52.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":155,"name":"上汽大通","imgurl":"https://x.autoimg.cn/app/image/brands/155.png","sort":60.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":45,"name":"smart","imgurl":"https://x.autoimg.cn/app/image/brands/45.png","sort":87.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":269,"name":"SWM斯威汽车","imgurl":"https://x.autoimg.cn/app/image/brands/269.png","sort":88.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":69,"name":"双龙","imgurl":"https://x.autoimg.cn/app/image/brands/69.png","sort":120.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":162,"name":"思铭","imgurl":"https://x.autoimg.cn/app/image/brands/162.png","sort":129.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":205,"name":"赛麟","imgurl":"https://x.autoimg.cn/app/image/brands/205.png","sort":138.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":238,"name":"斯达泰克","imgurl":"https://x.autoimg.cn/app/image/brands/238.png","sort":148.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":66,"name":"世爵","imgurl":"https://x.autoimg.cn/app/image/brands/66.png","sort":175.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":149,"name":"陕汽通家","imgurl":"https://x.autoimg.cn/app/image/brands/149.png","sort":176.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":90,"name":"双环","imgurl":"https://x.autoimg.cn/app/image/brands/90.png","sort":186.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"T","list":[{"id":133,"name":"特斯拉","imgurl":"https://x.autoimg.cn/app/image/brands/133.png","sort":80.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":161,"name":"腾势","imgurl":"https://x.autoimg.cn/app/image/brands/161.png","sort":114.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":202,"name":"泰卡特","imgurl":"https://x.autoimg.cn/app/image/brands/202.png","sort":151.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"W","list":[{"id":283,"name":"WEY","imgurl":"https://x.autoimg.cn/app/image/brands/283.png","sort":18.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":114,"name":"五菱汽车","imgurl":"https://x.autoimg.cn/app/image/brands/114.png","sort":29.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":70,"name":"沃尔沃","imgurl":"https://x.autoimg.cn/app/image/brands/70.png","sort":33.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":167,"name":"五十铃","imgurl":"https://x.autoimg.cn/app/image/brands/167.png","sort":77.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":284,"name":"蔚来","imgurl":"https://x.autoimg.cn/app/image/brands/284.png","sort":107.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":192,"name":"潍柴英致","imgurl":"https://x.autoimg.cn/app/image/brands/192.png","sort":115.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":99,"name":"威兹曼","imgurl":"https://x.autoimg.cn/app/image/brands/99.png","sort":153.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":102,"name":"威麟","imgurl":"https://x.autoimg.cn/app/image/brands/102.png","sort":185.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"X","list":[{"id":12,"name":"现代","imgurl":"https://x.autoimg.cn/app/image/brands/12.png","sort":8.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":71,"name":"雪佛兰","imgurl":"https://x.autoimg.cn/app/image/brands/71.png?2015100915","sort":15.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":72,"name":"雪铁龙","imgurl":"https://x.autoimg.cn/app/image/brands/72.png","sort":37.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":185,"name":"新凯","imgurl":"https://x.autoimg.cn/app/image/brands/185.png","sort":170.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":306,"name":"鑫源","imgurl":"https://x.autoimg.cn/app/image/brands/306.png","sort":172.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":98,"name":"西雅特","imgurl":"https://x.autoimg.cn/app/image/brands/98.png","sort":177.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"Y","list":[{"id":73,"name":"英菲尼迪","imgurl":"https://x.autoimg.cn/app/image/brands/73.png","sort":42.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":110,"name":"一汽","imgurl":"https://x.autoimg.cn/app/image/brands/110.png","sort":58.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":263,"name":"驭胜","imgurl":"https://x.autoimg.cn/app/image/brands/263.png","sort":72.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":111,"name":"野马汽车","imgurl":"https://x.autoimg.cn/app/image/brands/111_1.png","sort":82.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":286,"name":"云度","imgurl":"https://x.autoimg.cn/app/image/brands/286.png","sort":118.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":144,"name":"依维柯","imgurl":"https://x.autoimg.cn/app/image/brands/144.png","sort":119.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":232,"name":"御捷","imgurl":"https://x.autoimg.cn/app/image/brands/232.png","sort":149.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":298,"name":"宇通客车","imgurl":"https://x.autoimg.cn/app/image/brands/298.png","sort":159.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":307,"name":"裕路","imgurl":"https://x.autoimg.cn/app/image/brands/307.png","sort":165.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":93,"name":"永源","imgurl":"https://x.autoimg.cn/app/image/brands/93.png","sort":181.0,"tipinfo":"","brandmusem":"","musemurl":""}]},{"letter":"Z","list":[{"id":94,"name":"众泰","imgurl":"https://x.autoimg.cn/app/image/brands/94.png","sort":25.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":22,"name":"中华","imgurl":"https://x.autoimg.cn/app/image/brands/22.png","sort":51.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":74,"name":"中兴","imgurl":"https://x.autoimg.cn/app/image/brands/74.png?r\u003d20150701?r\u003d20150702","sort":100.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":206,"name":"知豆","imgurl":"https://x.autoimg.cn/app/image/brands/206.png","sort":105.0,"tipinfo":"","brandmusem":"","musemurl":""},{"id":182,"name":"之诺","imgurl":"https://x.autoimg.cn/app/image/brands/182.png","sort":140.0,"tipinfo":"","brandmusem":"","musemurl":""}]}]},"returncode":0,"message":"ok"}';

		$brand = json_decode($brand_json, true);
		$save_list = [];
		$db_car_brand = db('car_brand');
		foreach ($brand['result']['brandlist'] as $key => $value) {
			foreach ($value['list'] as $k => $v) {
				$save_arr = [
					'autohome_id' => $v['id'],
					'brand_name' => $v['name'],
					'brand_logo' => 'uploads/brand/'.$v['id'].'.jpg',
					'letter' => $value['letter']
				];
				dump($save_arr);
				if(!$db_car_brand->where(['autohome_id' => $v['id']])->find()) $db_car_brand->insert($save_arr);
			}
		}

	}

}