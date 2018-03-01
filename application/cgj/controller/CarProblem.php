<?php
namespace app\cgj\controller;
use think\Cache;
use app\common\controller\BaseApi;
class CarProblem extends BaseApi
{
	//小程序提交汽车问题信息控制器
	public function upLoadImg(){
		// 获取表单上传文件 例如上传了001.jpg
	    $file = request()->file('problem');
	    $message = 'success';
	    $result = array();
	    $errcode = 0;
	    // 移动到框架应用根目录/public/uploads/ 目录下
	    if($file){
	    	$base_path = ROOT_PATH . 'public' . DS . 'uploads';
	        $info = $file->move($base_path);
	        if($info){
	            $result = [
	            	'plate_path' => $base_path. DS . $info->getSaveName(),
	            	'add_time' => time(),
	            	'pathmd5' => md5($base_path. DS . $info->getSaveName())
	            ];
	            db('plate_ocr')->insert($result);
	            
	        }else{
	            // 上传失败获取错误信息
	            $errcode = 1; 
	            $message = $file->getError();
	        }
	    }
	    return $this->sendResult($result, $errcode, $message);
	}
}