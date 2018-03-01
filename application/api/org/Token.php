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
// | @Last Modified time: 2017-12-08 14:42:20 
// +----------------------------------------------------------------------
// | @Description 文件描述：Token类

namespace app\api\org;

use \Firebase\JWT\JWT;

class Token
{
    //JWT加密KEY
    protected $key = 'l2V|DSFXXXgfZp{8`;FjzR~6Y1_';
    //参数
    protected $config = array(
        "iss"   => "LiShaoen JWT",    // 签发者
        "iat"   => '',            // 签发时间
        "exp"   => '',   // 过期时间
        "aud"   => 'LiShaoen',        // 接收方
        "sub"   => 'LiShaoen',        // 面向的用户
        "nbf"   => '',            //如果当前时间在nbf里的时间之前，则Token不被接受；一般都会留一些余地，比如几分钟。
        "data"  => [],
    );

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->key           = config('token_key')?config('token_key'):"l2V|DSFXXXgfZp{8`;FjzR~6Y1_";
        $this->config['iss'] = config('iss')?config('iss'):"LiShaoen JWT";
        $this->config['iat'] = time();
        $this->config['exp'] = (int)config('token_time') ? (int)config('token_time') * 86400 + time() : 86400 + time(); //默认一天
        $this->config['aud'] = config('aud')?config('aud'):"LiShaoen";
        //$this->config['sub'] = config('sub')?config('sub'):"LiShaoen";
        //$this->config['nbf'] = time();
    }

    /**
     * token key 设置
     * @param string $value 设置值
     */
    public function setKey($value)
    {
        $this->key = $value;
    }

    /**
     * 参数设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * token 加密
     *
     * @param array $data
     * @return void
     */
    public function tokenEncode($data = [])
    {
        $jwt_data = [];
        if(!empty($this->config) && !empty($this->key)){
            //数据处理
            if($data){
                $this->config['data'] = $data;
            }
            //调用JWT::encode
            $jwt = JWT::encode($this->config, $this->key,$alg = 'HS256');
            //加密数据access_token
            $jwt_data = $jwt;
        }

        return $jwt_data;
    }

    /**
     * 解密token
     *
     * @param [type] $tokenStr
     * @return void
     */
    public function tokenDecode($tokenStr)
    {
        $jwt_data = [];
        if(!empty($this->config) && !empty($this->key)){
            $decoded = JWT::decode($tokenStr, $this->key, array('HS256'));
            if($decoded){
                is_object($decoded) && $decoded = (array)$decoded;
                //加密数据access_token
                $jwt_data = $decoded;
            }
        }
        
        return $jwt_data;
    }


    // 获取解密信息中的data
    public function getTokenData($token = '')
    {
        $return = $result = [];
        if($token){
            $result = $this->tokenDecode($token);
            //获取data值
            $return = $result['data'];
        }

        return $return;
    }

}
