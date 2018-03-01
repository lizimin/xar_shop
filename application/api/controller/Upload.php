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
// | @Description 文件描述： API文件上传接口

namespace app\api\controller;

use EasyWeChat\Foundation\Application;
use think\Cache;
use think\Db;
use app\common\controller\ApiBase;

class Upload extends ApiBase
{
	//定义参数
	protected $model_attachment;

	/**
	 * 初始化
	 *
	 * @return void
	 */
	protected function _initialize(){
		parent::_initialize();
		
        $this->model_attachment = model('common/Attachment');
		
	}
	
	/**
	 * 文件上传接口
	 *
	 * @param string $name
	 * @return void
	 */
	public function upload($input = 'file',$type=''){
		//初始化
		$return = $data = [];
		$errcode = 0;
		$message = 'success';
		
		//获取参数
		$input = input('input',$input,'string');
		$type  = input('type',$type,'string');

		//调用模型上传方法，原来使用upload方法保存到本地，现在切换到oss
		$data = $this->model_attachment->ossUpload($input,$type);
		
		if($data){
			$return   = $data['data'];
			$errcode  = $data['error'];
			$message  = $data['message'];
		}
		
		return $this->sendResult($return,$errcode,$message);
	}

	/**
	 * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
	 * oss上传
	 */
    public function ossUpload($input = 'file',$type=''){
//        $rerurn_arr = controller('common/OssUpload')->oss_upload();

        //初始化
        $return = $data = [];
        $errcode = 0;
        $message = 'success';

        //获取参数
        $input = input('input',$input,'string');
        $type  = input('type',$type,'string');

        //调用模型上传方法
        $data = $this->model_attachment->ossUpload($input,$type);

        if($data){
            $return   = $data['data'];
            $errcode  = $data['error'];
            $message  = $data['message'];
        }

        return $this->sendResult($return,$errcode,$message);
    }
    /**
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     * oss上传
     */
	public function ossUploadUeditor($input = 'upfile',$type=''){
//		$rerurn_arr = controller('common/OssUpload')->oss_upload_ueditor();
        //初始化
        $return = $data = [];
        $errcode = 0;
        $message = 'success';

        //获取参数
        $input = input('input',$input,'string');
        $type  = input('type',$type,'string');

        //调用模型上传方法
        $data = $this->model_attachment->ossUploadUeditor($input,$type);

        if($data){
            $return   = $data['data'];
            $errcode  = $data['error'];
            $message  = $data['message'];
        }

        return $this->sendResult($return,$errcode,$message);
		return $this->sendResult($rerurn_arr);
	}

	/**
	 * 附件删除
	 *
	 * @return void
	 */
	public function delete() {
		$data = array(
			'status' => 1,
		);
		echo json_encode($data);
	}
}