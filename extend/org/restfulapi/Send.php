<?php
// +----------------------------------------------------------------------
// | 彩龙平台 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.lishaoen.com
// +----------------------------------------------------------------------
// | Author: lishaoen <lishaoenbh@qq.com>
// +----------------------------------------------------------------------
// [ RESTfulAPI——发送响应 ]

namespace org\restfulapi;

use think\Response;
use think\response\Redirect;
use TripleDES\TripleDES;
use think\Config;
trait Send
{
    protected $restDefaultType = 'json';
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
     * 失败响应
     * @param int $error
     * @param string $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Xml
     */
    public function sendError($error = 400, $message = 'error', $code = 200, $data = [], $headers = [], $options = [])
    {
        $responseData['error'] = (int)$error;
        $responseData['message'] = (string)$message;
        if (!empty($data)) {
            if (!Config::get('des3_output_on') || Config::get('des3_debug_on')) $responseData['data'] = $data;

            if (Config::get('des3_output_on')) {
                $xtimestamp = time();
                $des3 = new TripleDES($xtimestamp, Config::get('clzg_des3_auth_str'));
                $responseData['xdata'] = $des3->encrypt(json_encode($data));
                $responseData['xtimestamp'] = $xtimestamp;
            }
            
        }
        $responseData = array_merge($responseData, $options);

        

        return $this->response($responseData, $code, $headers,$options);
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
    public function sendResult($data = [], $message = 'success', $error = 0, $code = 200, $headers = [], $options = [])
    {
        $responseData['error'] = (int)$error;
        $responseData['message'] = (string)$message;
        if (!empty($data)) {
            if (!Config::get('des3_output_on') || Config::get('des3_debug_on')) $responseData['data'] = $data;

            if (Config::get('des3_output_on')) {
                $xtimestamp = time();
                $des3 = new TripleDES($xtimestamp, Config::get('clzg_des3_auth_str'));
                $responseData['xdata'] = $des3->encrypt(json_encode($data));
                $responseData['xtimestamp'] = $xtimestamp;
            }
            
        }
        $responseData = array_merge($responseData, $options);

        return $this->response($responseData, $code, $headers,$options);
    }

    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @param int $code
     * @param array $headers
     * @param array $options
     * @return Response|\think\response\Json|\think\response\Jsonp|Redirect|\think\response\Xml
     */
    public function sendSuccess($data = [], $message = 'success', $code = 200, $headers = [], $options = [])
    {
        $responseData['error'] = 0;
        $responseData['message'] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers,$options);
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



}