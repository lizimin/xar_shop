<?php
// +----------------------------------------------------------------------
// | 彩龙平台 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.lishaoen.com
// +----------------------------------------------------------------------
// | Author: lishaoen <lishaoenbh@qq.com>
// +----------------------------------------------------------------------
// [ RESTfulAPI——授权失败 ]

namespace org\restfulapi;

use think\Exception;

class UnauthorizedException extends Exception
{

    public $authenticate;


    public function __construct($challenge = 'Basic', $message = 'authentication Failed')
    {
        $this->authenticate = $challenge;
        $this->message = $message;
    }

    /**
     * 获取验证错误信息
     * @access public
     * @return array|string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * WWW-Authenticate challenge string
     * @return array
     */
    public function getHeaders()
    {
        return array('WWW-Authenticate' => $this->authenticate);
    }

}