<?php
namespace app\cgj\controller;
use think\Cache;
use app\common\controller\BaseApi;
class miniProgram extends BaseApi
{
	public $app;
	public $miniProgram;
	public function __construct(){
		$this->app = new Application(config('wechat_config'));
		$this->miniProgram = $this->app->mini_program;
	}
    public function index()
    {
    	$code = input('code', '');
		$res_data = $this->miniProgram->sns->getSessionKey($code);
		$res = $res_data->all();
		$str = md5($res['openid'].$res['session_key']);
		$user_info = array(); //存储至用户数据库之后的用户信息。
        Cache::set($str, array_merge((array)$res,$user_info), 7200);
        $result = array('reqkey'=>$str,'expires_in'=>time()+7200);
        return $this->sendResult($result);
    }
}