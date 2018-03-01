<?php
// +----------------------------------------------------------------------
// | IT-PHP
// +----------------------------------------------------------------------
// | Copyright (c)  All rights reserved.
// +----------------------------------------------------------------------
// | OSS上传
// +----------------------------------------------------------------------
// | @author: lishaoen <lishaoen@gmail.com>
// +----------------------------------------------------------------------
// | @version: 1.0
// +----------------------------------------------------------------------

namespace app\common\controller;
use think\Controller;

class OssUpload extends Controller {
    //字段定义
    protected $ossClient;
    //上传错误信息
    private $error_msg = '';
    
    //上传配置
    protected $config;
    
    
	/**
     * 初始化方法
     */
	protected function _initialize() {
        $this->config = config('OSS_CONFIG');
    }
    
    
    /**
     * oss上传文件
     *
     * @param array  $Files     文件信息
     * @param string $path      上传的路径目录
     * @param array  $config    oss上传配置 
     * @param string $x_oss_process  样式访问方式 如：$x_oss_process = "?x-oss-process=image/resize,h_300,w_300" 或者 $x_oss_process ="?x-oss-process=style/"
     * 
     * @return array
     * @throws Exception
     */
    public function oss_upload($files=''){
        $xstatus = 0;
        $xstatus_msg = '';
        $x_arr_return = $xstatus_arr = $info = array();

        if('' === $files){
            $files  =   $_FILES;
        }
        $return_arr = array();
        if(!empty($files)){
            try {
                $this->ossClient = new \OSS\OssClient($this->config['accessKeyId'], $this->config['accessKeySecret'], $this->config['endpoint']);

                /* 逐个检测并上传文件 */
                $info    =  array();
                if(function_exists('finfo_open')){
                    $finfo   =  finfo_open ( FILEINFO_MIME_TYPE );
                }
                $files = current($files);
                /* 保存文件 并记录保存成功的文件 */
                $file_tmp = $files['tmp_name'];
                $object = $this->getFileUrl($files);
                try{
                    $oss_return = $this->ossClient->uploadFile($this->config['bucket'], $object, $file_tmp);
                    if($oss_return){
                        $this->error_msg = '上传成功！';
                        $return_arr = array('status_code'=>0, 'msg'=>$this->error_msg, 'file_data'=>$this->config['oss_domain'].$object);
                    }else{
                        $this->error_msg = 'OSS上传失败！';
                        $return_arr = array('status_code'=>-1, 'msg'=>$this->error_msg, 'file_data'=>'');
                    }
                } catch(OssException $e) {
                    $this->error_msg = $e->getMessage();
                    $return_arr = array('status_code'=>-1, 'msg'=>$this->error_msg, 'file_data'=>'');
                }
            } catch (OssException $e) {
                $this->error_msg = $e->getMessage();
                $return_arr = array('status_code'=>-1, 'msg'=>$this->error_msg, 'file_data'=>'');
            }
        }else{
            $this->error_msg = '没有上传的文件！';
            $return_arr = array('status_code'=>-1, 'msg'=>$this->error_msg, 'file_data'=>'');
        }

        return $return_arr;
    }

    public function oss_upload_ueditor($files=''){
        $xstatus = 0;
        $xstatus_msg = '';
        $x_arr_return = $xstatus_arr = $info = array();

        if('' === $files){
            $files  =   $_FILES;
        }
        $return_arr = array('name'=>'demo.jpg', 'original'=>'demo.jpg', 'size'=>'1234', 'state'=>'SUCCESS', 'type'=>'.jpg', 'url'=>'/server/ueditor/upload/image/demo.jpg');
        if(!empty($files)){
            try {
                $this->ossClient = new \OSS\OssClient($this->config['accessKeyId'], $this->config['accessKeySecret'], $this->config['endpoint']);

                /* 逐个检测并上传文件 */
                $info    =  array();
                if(function_exists('finfo_open')){
                    $finfo   =  finfo_open ( FILEINFO_MIME_TYPE );
                }
                $files = current($files);
                /* 保存文件 并记录保存成功的文件 */
                $file_tmp = $files['tmp_name'];
                $object = $this->getFileUrl($files);
                try{
                    $oss_return = $this->ossClient->uploadFile($this->config['bucket'], $object, $file_tmp);
                    if($oss_return){
                        $this->error_msg = '上传成功！';
                        $return_arr = array('name'=>$files['name'], 'original'=>$files['name'], 'size'=>strval($files['size']), 'state'=>'SUCCESS', 'type'=>'.'.pathinfo($files['name'], PATHINFO_EXTENSION), 'url'=>$this->config['oss_domain'].$object);
                    }else{
                        $this->error_msg = 'OSS上传失败！';
                        $return_arr = array('name'=>'', 'original'=>'', 'size'=>0, 'state'=>'ERROR', 'type'=>'.', 'url'=>'');
                    }
                } catch(OssException $e) {
                    $return_arr = array('name'=>'', 'original'=>'', 'size'=>0, 'state'=>'ERROR', 'type'=>'.', 'url'=>'');
                }
            } catch (OssException $e) {
                $return_arr = array('name'=>'', 'original'=>'', 'size'=>0, 'state'=>'ERROR', 'type'=>'.', 'url'=>'');
            }
        }else{
            $return_arr = array('name'=>'', 'original'=>'', 'size'=>0, 'state'=>'ERROR', 'type'=>'.', 'url'=>'');
        }
        echo json_encode($return_arr);
        exit;
    }
    
    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->error_msg;
    }
    
    
    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function dealFiles($files) {
        $temp_file_arr = $fileArray  = array();
        $n          = 0;
        if(!is_array($files)){
            $temp_file_arr[] = $files;
        }else{
            $temp_file_arr = $files;
        }
       return $temp_file_arr;
    }
    
    
    /**
     * 检查上传的文件
     * @param array $file 文件信息
     */
    private function check($file) {
        /* 文件上传失败，捕获错误代码 */
        if ($file['error']) {
            $this->error_no($file['error']);
            return false;
        }

        /* 无效上传 */
        if (empty($file['name'])){
            $this->error_msg = '未知上传错误！';
        }

        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error_msg = '非法上传文件！';
            return false;
        }

        /* 检查文件大小 */
        if (!$this->checkSize($file['size'])) {
            $this->error_msg = '上传文件大小不符！';
            return false;
        }

        /* 检查文件Mime类型 */
        //TODO:FLASH上传的文件获取到的mime类型都为application/octet-stream
        if (!$this->checkMime($file['type'])) {
            $this->error_msg = '上传文件MIME类型不允许！';
            return false;
        }

        /* 检查文件后缀 */
        if (!$this->checkExt($file['ext'])) {
            $this->error_msg = '上传文件后缀不允许';
            return false;
        }

        /* 通过检测 */
        return true;
    }
    
    /**
     * 检查文件大小是否合法
     * @param integer $size 数据
     */
    private function checkSize($size) {
        return !($size > $this->config['maxSize']) || (0 == $this->config['maxSize']);
    }
    
    /**
     * 检查上传的文件MIME类型是否合法
     * @param string $mime 数据
     */
    private function checkMime($mime) {
        return empty($this->config['mimes']) ? true : in_array(strtolower($mime), $this->config['mimes']);
    }
    
    /**
     * 检查上传的文件后缀是否合法
     * @param string $ext 后缀
     */
    private function checkExt($ext) {
        return empty($this->config['exts']) ? true : in_array(strtolower($ext), $this->config['exts']);
    }
    
    /**
     * 根据上传文件命名规则取得保存文件名
     * @param string $file 文件信息
     */
    private function getSaveName($file) {
        $rule = $this->config['saveName'];
        if (empty($rule)) { //保持文件名不变
            /* 解决pathinfo中文文件名BUG */
            $filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);
            $savename = $filename;
        } else {
            $savename = $this->getName($rule, $file['name']);
            if(empty($savename)){
                $this->error_msg = '文件命名规则错误！';
                return false;
            }
        }

        /* 文件保存后缀，支持强制更改文件后缀 */
        $ext = $file['ext'];

        return $savename . '.' . $ext;
    }
    
    /**
     * 获取子目录的名称
     * @param array $file  上传的文件信息
     */
    private function getSubPath($filename) {
        $subpath = '';
        $rule    = $this->config['subName'];
        if (!empty($rule)) {
            $subpath = $this->getName($rule, $filename) . '/';
        }
        return $subpath;
    }
    
    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule, $filename){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            foreach ($param as &$value) {
               $value = str_replace('__FILE__', $filename, $value);
            }
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }
    
     /**
     * 获取错误代码信息
     * @param string $errorNo  错误号
     */
    private function error_no($errorNo) {
        switch ($errorNo) {
            case 1:
                $this->error_msg = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！';
                break;
            case 2:
                $this->error_msg = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！';
                break;
            case 3:
                $this->error_msg = '文件只有部分被上传！';
                break;
            case 4:
                $this->error_msg = '没有文件被上传！';
                break;
            case 6:
                $this->error_msg = '找不到临时文件夹！';
                break;
            case 7:
                $this->error_msg = '文件写入失败！';
                break;
            default:
                $this->error_msg = '未知上传错误！';
        }
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

?>