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
// | @Last Modified time: 2017-12-01 10:28:38 
// +----------------------------------------------------------------------
// | @Description 文件描述：附件上传模型

namespace app\common\model;

use app\common\model\Model;

class Attachment extends Model{
    //设置数据表（不含前缀)
    protected $name = 'attachment';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'aid';

	//自动写入时间戳
	protected $autoWriteTimestamp = true;
	 
	// 定义时间戳字段名
	protected $createTime = 'create_time';
	protected $updateTime = 'update_time';

    //字段定义
    protected $ossClient;
    //上传错误信息
    private $error_msg = '';

    //上传配置
    protected $config;

    protected function initialize(){
        parent::initialize();
        $this->config = config('OSS_CONFIG');
    }

    /**
	 * 文件上传接口
	 *
	 * @param string $name
	 * @return void
	 */
	public function ossUpload($input = 'file',$type=''){
        //参数初始化
        $result  = array();
		$error = 0;
		$message = 'success';
        
		//获取文件
		$file = request()->file($input);
		
		//获取文件是否为空
		if(empty($file)){
			$error = 1;
			$message = '未找到上传的文件(原因：表单名可能错误，默认表单名“file”)！';

			return $this->result($result, $message, $error);
		}

		//非法php\html文件上传检查
		/*if ($file->getMime() == 'text/x-php' || $file->getMime() == 'text/html') {
			$error = 1;
			$message = '禁止上传php,html文件)！';

			return $this->result($result, $message, $error);
		}*/

		// 格式、大小校验
		if ($file->checkExt(config('upload.upload_image_ext'))) {
			//图片类型
			$type = !empty($type) ? $type : 'image';

			//图片类型大小限制
            if (config('upload.upload_image_size') > 0 && !$file->checkSize(config('upload.upload_image_size')*1024)) {
				$error = 1;
				$message = '上传的图片大小超过系统限制['.config('upload.upload_image_size').'KB]！';

                return $this->result($result, $message, $error);
            }
        } else if ($file->checkExt(config('upload.upload_file_ext'))) {
			//文件类型
			$type = !empty($type) ? $type : 'file';

			//文件类型大小限制
            if (config('upload.upload_file_size') > 0 && !$file->checkSize(config('upload.upload_file_size')*1024)) {
				$error = 1;
				$message = '上传的文件大小超过系统限制['.config('upload.upload_file_size').'KB]！';

                return $this->result($result, $message, $error);
            }
        } else if ($file->checkExt('mp4,avi,mkv')) {
			//媒体类型
			$type = !empty($type) ? $type : 'media';
			
			//文件类型大小限制
            if (config('upload.upload_media_size') > 0 && !$file->checkSize(config('upload.upload_media_size')*1024)) {
				$error = 1;
				$message = '上传的媒体大小超过系统限制['.config('upload.upload_media_size').'KB]！';

                return $this->result($result, $message, $error);
			}
			
        } else {
			$error = 1;
			$message = '非系统允许的上传格式！';

			return $this->result($result, $message, $error);
        }
		
		//如果文件已经存在，直接返回数据
        $res = $this->where('hash', $file->hash())->order('aid DESC')->find();
        if ($res) {
			return $this->result($result=$res, $message, $error);
        }




        $files  =   $_FILES;
        $oss_object = '';
        try {
            $this->ossClient = new \OSS\OssClient($this->config['accessKeyId'], $this->config['accessKeySecret'], $this->config['endpoint']);

            $files = current($files);
            /* 保存文件 并记录保存成功的文件 */
            $file_tmp = $files['tmp_name'];
            $oss_object = $this->getFileUrl($files);
            try{
                $oss_return = $this->ossClient->uploadFile($this->config['bucket'], $oss_object, $file_tmp);
                if(!$oss_return){
                    $error = 1;
                    return $this->result(array(), $message, $error);
                }
            } catch(OssException $e) {
                $this->error_msg = $e->getMessage();
                $error = 1;
                return $this->result(array(), $this->error_msg, $error);
            }
        } catch (OssException $e) {
            $this->error_msg = $e->getMessage();
            $error = 1;
            return $this->result(array(), $this->error_msg, $error);
        }

        $info = $file;
		$file_count = 1;
		//文件大小
		//$file_size = round($info->getInfo('size')/1024, 2);
		
		//返回或入库附件信息
        $data = [
			'name'     => $info->getFilename(),
			'oldname'  => $info->getInfo('name'),
			'path'     => $oss_object,
			'url'      => $oss_object,
			'rootpath' => $this->config['oss_domain'].$oss_object,
			'domain'   => $this->config['oss_domain'],
			'mime'	   => '',
			'ext'	   => $info->getExtension(),
			'type'     => $type,
			'size'     => $info->getInfo('size'),
			'md5'      => $info->md5(),
            'hash'     => $info->hash(),
            'location' => 'location',
            'status'   => '1',
			'create_time'   => request()->time(),
			'update_time'   => request()->time(),
			'ip'            => request()->ip(),
			'full_path'     => $this->config['oss_domain'].$oss_object,
			'pathmd5'       => md5($this->config['oss_domain'].$oss_object),
			
		];
		
		//记录入库
		if($data){
			$id = $this->insertGetId($data);

			$data['aid'] = $id;
		}
		
		return $this->result($result=$data, $message, $errcode);
	}

    public function ossUploadUeditor($input = 'upfile',$type=''){
        //参数初始化
        $result  = array();
        $error = 0;
        $message = 'success';

        //获取文件
        $file = request()->file($input);

        //获取文件是否为空
        if(empty($file)){
            $error = 1;
            $message = '未找到上传的文件(原因：表单名可能错误，默认表单名“file”)！';

            return $this->result($result, $message, $error);
        }

        //非法php\html文件上传检查
        /*if ($file->getMime() == 'text/x-php' || $file->getMime() == 'text/html') {
            $error = 1;
            $message = '禁止上传php,html文件)！';

            return $this->result($result, $message, $error);
        }*/

        // 格式、大小校验
        if ($file->checkExt(config('upload.upload_image_ext'))) {
            //图片类型
            $type = !empty($type) ? $type : 'image';

            //图片类型大小限制
            if (config('upload.upload_image_size') > 0 && !$file->checkSize(config('upload.upload_image_size')*1024)) {
                $error = 1;
                $message = '上传的图片大小超过系统限制['.config('upload.upload_image_size').'KB]！';

                return $this->result($result, $message, $error);
            }
        } else if ($file->checkExt(config('upload.upload_file_ext'))) {
            //文件类型
            $type = !empty($type) ? $type : 'file';

            //文件类型大小限制
            if (config('upload.upload_file_size') > 0 && !$file->checkSize(config('upload.upload_file_size')*1024)) {
                $error = 1;
                $message = '上传的文件大小超过系统限制['.config('upload.upload_file_size').'KB]！';

                return $this->result($result, $message, $error);
            }
        } else if ($file->checkExt('mp4,avi,mkv')) {
            //媒体类型
            $type = !empty($type) ? $type : 'media';

            //文件类型大小限制
            if (config('upload.upload_media_size') > 0 && !$file->checkSize(config('upload.upload_media_size')*1024)) {
                $error = 1;
                $message = '上传的媒体大小超过系统限制['.config('upload.upload_media_size').'KB]！';

                return $this->result($result, $message, $error);
            }

        } else {
            $error = 1;
            $message = '非系统允许的上传格式！';

            return $this->result($result, $message, $error);
        }

        //如果文件已经存在，直接返回数据
        $res = $this->where('hash', $file->hash())->order('aid DESC')->find();
        if ($res) {
            $res = array('name'=>$res['oldname'], 'original'=>$res['oldname'], 'size'=>strval($res['size']), 'state'=>'SUCCESS', 'type'=>'.'.$res['ext'], 'url'=>$res['domain'].$res['url']);
            echo json_encode($res);
            exit;
        }




        $files  =   $_FILES;
        $oss_object = '';
        try {
            $this->ossClient = new \OSS\OssClient($this->config['accessKeyId'], $this->config['accessKeySecret'], $this->config['endpoint']);

            $files = current($files);
            /* 保存文件 并记录保存成功的文件 */
            $file_tmp = $files['tmp_name'];
            $oss_object = $this->getFileUrl($files);
            try{
                $oss_return = $this->ossClient->uploadFile($this->config['bucket'], $oss_object, $file_tmp);
                if(!$oss_return){
                    $error = 1;
                    return $this->result(array(), $message, $error);
                }
            } catch(OssException $e) {
                $this->error_msg = $e->getMessage();
                $error = 1;
                return $this->result(array(), $this->error_msg, $error);
            }
        } catch (OssException $e) {
            $this->error_msg = $e->getMessage();
            $error = 1;
            return $this->result(array(), $this->error_msg, $error);
        }

        $info = $file;
        $file_count = 1;
        //文件大小
        //$file_size = round($info->getInfo('size')/1024, 2);

        //返回或入库附件信息
        $data = [
            'name'     => $info->getFilename(),
            'oldname'  => $info->getInfo('name'),
            'path'     => $oss_object,
            'url'      => $oss_object,
            'rootpath' => $this->config['oss_domain'].$oss_object,
            'domain'   => $this->config['oss_domain'],
            'mime'	   => '',
            'ext'	   => $info->getExtension(),
            'type'     => $type,
            'size'     => $info->getInfo('size'),
            'md5'      => $info->md5(),
            'hash'     => $info->hash(),
            'location' => 'location',
            'status'   => '1',
            'create_time'   => request()->time(),
            'update_time'   => request()->time(),
            'ip'            => request()->ip(),
            'full_path'     => $this->config['oss_domain'].$oss_object,
            'pathmd5'       => md5($this->config['oss_domain'].$oss_object),

        ];

        //记录入库
        if($data){
            $id = $this->insertGetId($data);

            $data['aid'] = $id;
        }
        $data = array('name'=>$data['oldname'], 'original'=>$data['oldname'], 'size'=>strval($data['size']), 'state'=>'SUCCESS', 'type'=>'.'.$data['ext'], 'url'=>$data['domain'].$data['url']);
        echo json_encode($data);exit;
        return $this->result($result=$data, $message, $errcode);
    }

    public function upload($input = 'file',$type=''){
        //参数初始化
        $result  = array();
        $error = 0;
        $message = 'success';

        //获取文件
        $file = request()->file($input);

        //获取文件是否为空
        if(empty($file)){
            $error = 1;
            $message = '未找到上传的文件(原因：表单名可能错误，默认表单名“file”)！';

            return $this->result($result, $message, $error);
        }

        //非法php\html文件上传检查
        if ($file->getMime() == 'text/x-php' || $file->getMime() == 'text/html') {
            $error = 1;
            $message = '禁止上传php,html文件)！';

            return $this->result($result, $message, $error);
        }

        // 格式、大小校验
        if ($file->checkExt(config('upload.upload_image_ext'))) {
            //图片类型
            $type = !empty($type) ? $type : 'image';

            //图片类型大小限制
            if (config('upload.upload_image_size') > 0 && !$file->checkSize(config('upload.upload_image_size')*1024)) {
                $error = 1;
                $message = '上传的图片大小超过系统限制['.config('upload.upload_image_size').'KB]！';

                return $this->result($result, $message, $error);
            }
        } else if ($file->checkExt(config('upload.upload_file_ext'))) {
            //文件类型
            $type = !empty($type) ? $type : 'file';

            //文件类型大小限制
            if (config('upload.upload_file_size') > 0 && !$file->checkSize(config('upload.upload_file_size')*1024)) {
                $error = 1;
                $message = '上传的文件大小超过系统限制['.config('upload.upload_file_size').'KB]！';

                return $this->result($result, $message, $error);
            }
        } else if ($file->checkExt('mp4,avi,mkv')) {
            //媒体类型
            $type = !empty($type) ? $type : 'media';

            //文件类型大小限制
            if (config('upload.upload_media_size') > 0 && !$file->checkSize(config('upload.upload_media_size')*1024)) {
                $error = 1;
                $message = '上传的媒体大小超过系统限制['.config('upload.upload_media_size').'KB]！';

                return $this->result($result, $message, $error);
            }

        } else {
            $error = 1;
            $message = '非系统允许的上传格式！';

            return $this->result($result, $message, $error);
        }

        // 上传附件路径
        $_upload_path = ROOT_PATH.'public'.DS.'uploads'.DS.$type.DS;
        // 附件访问路径
        $_file_path  = str_replace(ROOT_PATH,'',$_upload_path);
        $_file_path  = '/'.str_replace('\\', '/', $_file_path);

        //如果文件已经存在，直接返回数据
        $res = $this->where('hash', $file->hash())->find();
        if ($res) {
            return $this->result($result=$res, $message, $error);
        }

        // 移动到upload 目录下
        $info = $file->move($_upload_path);
        //上传失败
        if (!is_file($_upload_path.$info->getSaveName())) {
            $error = 1;
            $message = '文件上传失败:'.$file->getError();

            return $this->result($result, $message, $error);
        }

        $file_count = 1;
        //文件大小
        //$file_size = round($info->getInfo('size')/1024, 2);

        //返回或入库附件信息
        $data = [
            'name'     => $info->getFilename(),
            'oldname'  => $info->getInfo('name'),
            'path'     => $_file_path.str_replace('\\', '/', $info->getSaveName()),
            'url'      => $_file_path.str_replace('\\', '/', $info->getSaveName()),
            'rootpath' => ROOT_PATH,
            'domain'   => request()->domain(),
            'mime'	   => $info->getMime(),
            'ext'	   => $info->getExtension(),
            'type'     => $type,
            'size'     => $info->getInfo('size'),
            'md5'      => $info->md5(),
            'hash'     => $info->hash(),
            'location' => 'location',
            'status'   => '1',
            'create_time'   => request()->time(),
            'update_time'   => request()->time(),
            'ip'            => request()->ip(),
            'full_path'     => $_upload_path. DS . $info->getSaveName(),
            'pathmd5'       => md5($_upload_path. DS . $info->getSaveName()),

        ];

        //记录入库
        if($data){
            $id = $this->insertGetId($data);

            $data['aid'] = $id;
        }

        return $this->result($result=$data, $message, $errcode);
    }


	/**
	 * 通过条件获取附件信息
	 *
	 * @param array $where
	 * @param boolean $field
	 * @return void
	 */
	public function get_attachment_info($where = [], $field = true){
		//初始化
        $return = $data = array();
        //条件查询
        if($where){
			$data = $this->where($where)->field( $field)->find();
			
			if($data){
				$return = $data->getData();
			}
		}

		return $return;
	}

	/**
	 * 更新附件表信息
	 *
	 * @param array $where
	 * @param array $data
	 * @return void
	 */
	public function update_attachment_info($where = [], $data = []){
		//初始化
		$return = array();
		
		if($where && $data){
			$return = $this->allowField(true)->where($where)->update($data);
		}
		return $return;
	}

    /**
     * @param $file
     * 传入文件
     */
    private function getFileUrl($file){
        $domain_dir = input('domain_dir', 'common');
        $oss_config = $this->config;
        $domain_dir = $oss_config['domain_dir'][$domain_dir] ? $oss_config['domain_dir'][$domain_dir] : 'common';
        $object = 'upload/'.$domain_dir.'/'.date('Y').'/'.date('m').'/'.date('d').'/'.time().'_'.$this->createRandomStr(6).'.'.pathinfo($file['name'], PATHINFO_EXTENSION);
        return $object;
    }

    /**
     * @param $length
     * @return string
     * 获取随机字符串
     */
    function createRandomStr($length){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $strlen = 62;
        while($length > $strlen){
            $str .= $str;
            $strlen += 62;
        }
        $str = str_shuffle($str);
        return substr($str,0,$length);
    }

}