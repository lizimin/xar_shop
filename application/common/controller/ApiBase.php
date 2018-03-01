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
// | @Description 文件描述： API继承基类

namespace app\common\controller;

//入口限定
if (!defined("IN_API")) {
    exit("Access Denied");
}

use think\Controller;
use think\Config;
use think\Response;
use think\response\Redirect;
use think\exception\HttpResponseException;
use app\api\error\CodeBase;
use think\Cache;
class ApiBase extends Controller
{
    // 请求参数
    protected $param;
    //token字符串
    protected $token = '';

    protected $restDefaultType = 'json';

    public $errCodeList = [
        0     => 'success',
        1     => 'error',
    ];

    /**
	 * 基类初始化
	 *
	 * @return void
	 */
	protected function _initialize(){
        parent::_initialize();
        
        // 初始化请求信息
        $this->initRequestInfo();

        //错误信息代码和信息处理
        $codeBase = CodeBase::$errorArr;
        $this->errCodeList = $this->errCodeList + $codeBase;
        //获取access_token
        $access_token = input('access_token','','string');
        $reqkey = input('reqkey','','string');    //小程序请求所带的key
        //方法排除验证
        if(!in_array(ACTION_NAME,['getAccessToken'])){
            if(empty($access_token)){
                //$this->sendError($data=[], $error = 1000001, $message = '非法请求');
            }else{
                $this->token = $access_token;
                $this->user_info = $this->get_userinfo_token($this->token);
                $this->customer_id = $this->user_info['customer_id'] ? $this->user_info['customer_id'] : 0;
                $this->openid = $this->user_info['openid'] ? $this->user_info['openid'] : '';
                $this->shop_user_info = $this->get_userinfo_token($this->token);
                $this->shop_user_id = isset($this->shop_user_info['user']) && isset($this->shop_user_info['user']['shop_user_id']) ? $this->shop_user_info['user']['shop_user_id'] : 0;
            }
            if($reqkey){
                $xcx_client_info = $this->get_userinfo_reqkey($reqkey);
                $this->xcx_client_info = $xcx_client_info ? $xcx_client_info : null;
            }
        }
        $this->pagesize = 10;
        $this->page = input('page', 0, 'intval');
        $this->message = '';
        $this->errcode = 0;
    }
    //通过用户的access_token 来获取用户的店铺及店员信息
    public function getShopUser(){
        $shop_user = [];
        if($this->user_info['unionid']){
            $shop_user = db('shop_user')->where(['unionid' => $this->user_info['unionid']])->find();
        }
        return $shop_user;
    }

    //设置模型的状态
    public function setModelStatus(){
        $result = [];
        $error = 0;
        $model = input('model', '', 'string');
        $pk_name = input('pk_name', '', 'string');
        $pk_id = input('pk_id', 0, 'intval');
        $status_name = input('status_name', 'status', 'string');
        $status = input('status', 1, 'intval');

        if($model && $pk_name && $pk_id && $status_name){
            $result = db($model)->where([$pk_name => $pk_id])->update([$status_name => $status]);
        }else{
            $error = 101;
        }
        return $this->sendResult($result, $error);
        
    }

    public function getPageData($total = 0, $page = 0, $pagesize = 0){
        $pagesize = $pagesize ? $pagesize : $this->pagesize;
        $page = $page ? $page : $this->page;
        $page_data = [
            'total' => $total,
            'current_page' => $page,
            'last_page' => $page,
            'pagesize' => $pagesize
        ];
        if($total){
            $page_data['last_page'] = (int) ceil($total / $this->pagesize);
        }
        return $page_data;
    }

    //根据token获取当前请求用户的用户信息
    public function get_userinfo_token($token = ''){
        return Cache::get($token);
    }
    //根据小程序的reqkey获取当前请求用户的用户信息
    public function get_userinfo_reqkey($reqkey = ''){
        return Cache::get($reqkey);
    }
    

    /**
     * 设置响应类型
     * @param null $type
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = (string)(!empty($type)) ? $type : $this->restDefaultType;
        return $this;
    }
    
    /**
     * 响应结果
     * @param int $error
     * @param string $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     */
    public function sendResult($data = [], $error = 0, $message = '', $code = 200, $headers = [], $options = [])
    {
        $responseData['error'] = (int)$error;
        $responseData['message'] = (string)($message ? $message : $this->errCodeList[$error]);
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers,$options);
    }
    
  
    /**
     * 错误终止程序
     *
     * @param array $data
     * @param integer $error
     * @param string $message
     * @param integer $code
     * @param array $headers
     * @param array $options
     * @return void
     */
    public function sendError($data = [], $error = 1, $message = '', $code = 200, $headers = [], $options = [])
    {
        $responseData['error'] = $error;
        $responseData['message'] = (string)($message ? $message : $this->errCodeList[$error]);
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        $response = $this->response($responseData, $code, $headers,$options);
        
        throw new HttpResponseException($response);
    }

    /**
     * 重定向
     * @param $url
     * @param array $params
     * @param int $code
     * @param array $with
     * @return Redirect
     */
    public function sendRedirect($url, $params = [], $code = 302, $with = [])
    {
        $response = new Redirect($url);
        if (is_integer($params)) {
            $code = $params;
            $params = [];
        }
        $response->code($code)->params($params)->with($with);
        return $response;
    }

    /**
     * 响应
     * @param $responseData
     * @param $code
     * @param $headers
     * @param $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\View|\think\response\Xml
     */
    public function response($responseData, $code, $headers,$options)
    {
        if (!isset($this->type) || empty($this->type)) $this->setType();
        return Response::create($responseData,$this->type, $code, $headers,$options);
    }

    /**
     * 初始化请求信息
     */
    final private function initRequestInfo()
    {
        
        defined('IS_POST')          or define('IS_POST',         $this->request->isPost());
        defined('IS_GET')           or define('IS_GET',          $this->request->isGet());
        defined('IS_AJAX')          or define('IS_AJAX',         $this->request->isAjax());
        defined('IS_PJAX')          or define('IS_PJAX',         $this->request->isPjax());
        defined('MODULE_NAME')      or define('MODULE_NAME',     $this->request->module());
        defined('CONTROLLER_NAME')  or define('CONTROLLER_NAME', $this->request->controller());
        defined('ACTION_NAME')      or define('ACTION_NAME',     $this->request->action());
        defined('URL')              or define('URL',             strtolower($this->request->controller() . SYS_DS_PROS . $this->request->action()));
        defined('URL_MODULE')       or define('URL_MODULE',      strtolower($this->request->module()) . SYS_DS_PROS . URL);
        defined('URL_TRUE')         or define('URL_TRUE',        $this->request->url(true));
        defined('DOMAIN')           or define('DOMAIN',          $this->request->domain());
        
        $this->param = $this->request->param();
    }


}