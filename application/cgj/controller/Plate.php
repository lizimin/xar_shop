<?php
namespace app\cgj\controller;
use think\Cache;
use think\Db;
use app\common\controller\BaseApi;
class Plate extends BaseApi
{
	public function upload(){
	    // 获取表单上传文件 例如上传了001.jpg
	    $file = request()->file('plate');
	    $message = 'success';
	    $result = array();
	    $errcode = 0;
	    // 移动到框架应用根目录/public/uploads/ 目录下
	    if($file){
	    	$base_path = ROOT_PATH . 'public' . DS . 'uploads';
	        $info = $file->move($base_path);
	        if($info){
	            // 成功上传后 获取上传信息
	            // 输出 jpg
	            // 
	            $result = [
	            	'plate_path' => $base_path. DS . $info->getSaveName(),
	            	'add_time' => time(),
	            	'pathmd5' => md5($base_path. DS . $info->getSaveName())
	            ];
	            db('plate_ocr')->insert($result);

	            // // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	            // echo $info->getSaveName();
	            // // 输出 42a79759f284b767dfcb2a0197904287.jpg
	            // echo $info->getFilename();
	            
	        }else{
	            // 上传失败获取错误信息
	            $errcode = 1; 
	            $message = $file->getError();
	        }
	    }
	    return $this->sendResult($result, $errcode, $message);
	}

	public function ocr_vehicle_plate(){
		$pathmd5 = input('pathmd5', '');
		$result = array();
		if ($pathmd5) {
			$res = db('plate_ocr')->where(['pathmd5'=>$pathmd5])->find();
			if ($res) {
				$img_data = file_get_contents($res['plate_path']);
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
				    $save = ['result'=>$result['outputs'][0]['outputValue']['dataValue']];
				    $res = db('plate_ocr')->where(['pathmd5'=>$pathmd5])->update($save);
				    $result = json_decode($result['outputs'][0]['outputValue']['dataValue'], true);

				}
				curl_close($curl);
			}
		}
		return $this->sendResult($result);
	}
}