<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
error_reporting(E_ERROR | E_WARNING | E_PARSE);

use think\Db;
use think\Response;
use think\exception\HttpResponseException;

/**
 * 获取访问access_token
 *
 * @param string $api_key
 * @return void
 */
function get_access_token($api_key=''){
    if(!$api_key && config('api_key')){
        $api_key = config('api_key');
    }elseif(!$api_key && empty(config('api_key'))){
        $api_key = 'l2V|gfZp{8`;jzR~6Y1_';
    }
    return md5('LiShaoen'.date("YmdH").$api_key);
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login() {
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['shop_user_id'] : 0;
    }
}

/**
 * 获取用户id
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function get_user_id(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['shop_user_id'] : 0;
    }
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 */
function is_administrator($uid = null)
{
    if(is_numeric(session('user_access_'))){
        return session('user_access_');
    }
    $uid = is_null($uid) ? is_login() : $uid;
    $user_info = get_user_info();
    //查询店铺负责人
    $model_shop = model('common/ShopInfo');
    $shop = $model_shop->where(array('shop_id' => $user_info['shop_id']))->field('shop_user_id,exp_time')->find();
    if($shop->shop_user_id == $uid){
        session('user_access_', 1);
        return 1;
    }
    session('user_access_', 0);
    return 0;
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data) {
    //数据类型检测
    if (!is_array($data)) {
        $data = (array) $data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 根据用户ID获取用户信息
 * @param  integer $id 用户ID
 * @param  string $field
 * @return array  用户信息
 */
function get_user_info() {
    $user = session('user_auth');
    if (empty($user)) {
        return array();
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user : array();
    }
}

/**
 * 获取相应模型的相关信息
 * @param type $model
 * @param type $id
 * @param type $field 返回的字段，默认返回全部，数组
 * @param type $newCache 是否强制刷新
 * @return boolean
 */
function get_model_data($model='',$id=0,$field='*',$field_where='id',$newCache = false) {
    if (empty($id) || empty($model)) {
        return false;
    }

    $key = 'get'.$model.'_' . $id;
    //强制刷新缓存
    if ($newCache) {
        cache($key, NULL);
    }
    $cache = cache($key);
    if ($cache === 'false') {
        return false;
    }
    if (empty($cache)) {
        //读取数据
        $cache = db($model)->where(array($field_where => $id))->field($field)->find();
        if (empty($cache)) {
            cache($key, 'false', 60);
            return false;
        } else {
            cache($key, $cache, 3600);
        }
    }
    
    if ($field) {
        //支持var.property，不过只支持一维数组
        if (false !== strpos($field, '.')) {
            $vars = explode('.', $field);
            return $cache[$vars[0]][$vars[1]];
        } else {
            return $cache[$field];
        }
    } else {
        return $cache;
    }
    
    return $cache;
}

/**
 * 获取相应模型的相关信息
 * @param type $model
 * @param type $id
 * @param type $field 返回的字段，默认返回全部，数组
 * @param type $newCache 是否强制刷新
 * @return boolean
 */
function get_db_where_data($dbname='',$where=[],$field='*', $cache_id='',$newCache = true) {
    if (empty($where) || empty($dbname)) {
        return false;
    }

    $key = 'get'.$dbname.'_' .$cache_id;
    //强制刷新缓存
    if ($newCache) {
        cache($key, NULL);
    }
    $cache = cache($key);
    if ($cache === 'false') {
        return false;
    }
    if (empty($cache)) {
        //读取数据
        $cache = db($dbname)->where($where)->field($field)->find();
        if (empty($cache)) {
            cache($key, 'false', 60);
            return false;
        } else {
            cache($key, $cache, 3600);
        }
    }
    return $cache;
}

/**
 * 获取文件
 * @param int $file_id
 * @param string $field
 * @return 完整的数据  或者  指定的$field字段值
 */
function get_file($file_id, $field = null) {
    if (empty($file_id)) {
        return '';
    }
    $file = db('attachment')->where(array('aid' => $file_id))->find();
    if ($field == 'path') {
        return $file['path'];
    } elseif ($field == 'time') {
        return date('Y-m-d H:i:s', $file['create_time']);
    }
    return empty($field) ? $file : $file[$field];
}

function get_common_status($status=1){
    if($status == 1){
        return '已启用';
    }else{
        return '禁用';
    }
}

function get_sex($sex=1){
    if($sex == 1){
        return '男';
    }else{
        return '女';
    }
}

function get_car_type($type=1){
    switch($type){
        case 0:
            return '小型轿车';
        case 1:
            return '小型越野客车';
    }
    return '';
}

function get_car_character($is=0){
    switch($is){
        case 0:
            return '非运营';
        case 1:
            return '运营';
    }
    return '';
}

function get_acc_type($type=-1){
    switch($type){
        case 0:
            return '现金';
        case 1:
            return '转账';
        case 2:
            return '转账支票';
        case 3:
            return '微信';
        case 4:
            return '支付宝';
    }
    return '';
}

function get_acc_direction($type = -1){
    switch($type){
        case 0:
            return '贷';
        case 1:
            return '借';
    }
    return '';
}

if (!function_exists('result')) {
    /**
     * 返回数组结果
     * 
     * @return array
     */
    function result($data = array(), $message = '',$errcode = 0){
        //初始化定义
        $return = array();

        //参数变量定义
        $return = array(
            'errcode' => $errcode,
            'message' => $message,
            'data'    => $data
        );
        
        return $return;
    }
}

if (!function_exists('random')) {
    /**
     * 随机字符串
     * @param int $length 长度
     * @param int $numeric 类型(0：混合；1：纯数字)
     * @return string
     */
    function random($length = 16, $numeric = 1) {
         $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
         $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
         if($numeric) {
              $hash = '';
         } else {
              $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
              $length--;
         }
         $max = strlen($seed) - 1;
         for($i = 0; $i < $length; $i++) {
              $hash .= $seed{mt_rand(0, $max)};
         }
         return $hash;
    }
}

if (!function_exists('create_sn_code')) {
    /**
     * 生成订单、工单等规则
     *
     * @param string $prefix
     * @param string $shopid
     * @param string $rule
     * @return void
     */
    function create_sn_code($prefix='Sn',$shop_id='',$rule='dateh',$end_str=''){
        $return = $rule_str = '';
        
        if($prefix && $shop_id){
            switch ($rule) {
                case 'date':
                    $rule_str = date('Ymd',request()->time());
                    break;
                case 'dateh':
                    $rule_str = date('YmdH',request()->time());
                    break;
                case 'datehi':
                    $rule_str = date('YmdHi',request()->time());
                    break;
                case 'datetime':
                    $rule_str = date('YmdHis',request()->time());
                    break;
                case 'time':
                    $rule_str = request()->time();
                    break;
                case 'md5':
                    $rule_str = md5(microtime(true));
                    break;
                default:
                    $rule_str = date('YmdHis',request()->time());
                    break;
            }
            if(is_empty($end_str)){
                $end_str = random(5);
            }
            $return = $prefix.$shop_id.$rule_str.$end_str;
        }
        return $return;
    }
}

if (!function_exists('get_domain')) {
    /**
     * 获取当前域名
     * @param bool $http true 返回http协议头,false 只返回域名
     * @return string
     */
    function get_domain($http = true) {
        if ($http) {
            if (input('server.https') && input('server.https') == 'on') {
                return 'https://'.input('server.http_host');
            }
            return 'http://'.input('server.http_host');
        }
        return input('server.http_host');
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     * @param int $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param bool $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function get_client_ip($type = 0, $adv = false) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

if (!function_exists('parse_attr')) {
    /**
     * 解析数组
     * @param string $value 配置值
     * @return array|string
     */
    function parse_attr($value = '') {
        if (is_array($value)) return $value;
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $value  = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k]   = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

if (!function_exists('xml2array')) {
    /**
     * XML转数组
     * @param string $arr
     * @param boolean $isnormal
     * @return array
     */
    function xml2array(&$xml, $isnormal = FALSE) {
        $xml_parser = new app\common\util\Xml($isnormal);
        $data = $xml_parser->parse($xml);
        $xml_parser->destruct();
        return $data;
    }
}

if (!function_exists('array2xml')) {
    /**
     * 数组转XML
     * @param array $arr
     * @param boolean $htmlon
     * @param boolean $isnormal
     * @param intval $level
     * @return type
     */
    function array2xml($arr, $htmlon = TRUE, $isnormal = FALSE, $level = 1) {
        $s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
        $space = str_repeat("\t", $level);
        foreach($arr as $k => $v) {
            if(!is_array($v)) {
                $s .= $space."<item id=\"$k\">".($htmlon ? '<![CDATA[' : '').$v.($htmlon ? ']]>' : '')."</item>\r\n";
            } else {
                $s .= $space."<item id=\"$k\">\r\n".array2xml($v, $htmlon, $isnormal, $level + 1).$space."</item>\r\n";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s."</root>" : $s;
    }
}

if (!function_exists('is_empty')) {
    /**
     * 判断是否为空值
     */
    function is_empty($value) {
        if (!isset($value)){
            return true;
        }
        if ($value === null){
            return true;
        }
        if (trim($value) === ''){
            return true;
        }
        return false;
    }
}

if (!function_exists('str2arr')) {
    /**
     * 字符串转换为数组，主要用于把分隔符调整到第二个参数
     * @param  string $str  要分割的字符串
     * @param  string $glue 分割符
     * @return array
     */
    function str2arr($str, $glue = ','){ 
        return explode($glue, $str);
    }
}

if (!function_exists('arr2str')) {
    /**
     * 数组转换为字符串，主要用于把分隔符调整到第二个参数
     * @param  array  $arr  要连接的数组
     * @param  string $glue 分割符
     * @return string
     */
    function arr2str($arr, $glue = ','){
        return implode($glue, $arr);
    }
}


/**
 * 抛出响应异常
 */
function throw_response_exception($data = [], $type = 'json'){
    
    $response = Response::create($data, $type);

    throw new HttpResponseException($response);
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0){
    // 创建Tree
    $tree = [];
    if (!is_array($list)):
    return false;
    endif;
    // 创建基于主键的数组引用
    $refer = [];
    foreach ($list as $key => $data) {
        $refer[$data[$pk]] =& $list[$key];
    }
    foreach ($list as $key => $data) {
        // 判断是否存在parent
        $parentId =  $data[$pid];
        if ($root == $parentId) {
            $tree[] =& $list[$key];
        } else if(isset($refer[$parentId])){
            is_object($refer[$parentId]) && $refer[$parentId] = $refer[$parentId]->toArray();  
            $parent =& $refer[$parentId];
            $parent[$child][] =& $list[$key];
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array()) {
	if (is_array($tree)) {
		foreach ($tree as $key => $value) {
			$reffer = $value;
			if (isset($reffer[$child])) {
				unset($reffer[$child]);
				tree_to_list($value[$child], $child, $order, $list);
			}
			$list[] = $reffer;
		}
		$list = list_sort_by($list, $order, $sortby = 'asc');
	}
	return $list;
}

/**
 * 分析枚举类型字段值 格式 a:名称1,b:名称2
 * 暂时和 parse_config_attr功能相同
 * @param string $string 
 * @return array
 */
function parse_field_attr($string) {
	if (0 === strpos($string, ':')) {
		// 采用函数定义
		return eval('return ' . substr($string, 1) . ';');
	} elseif (0 === strpos($string, '[')) {
		// 支持读取配置参数（必须是数组类型）
		return \think\Config::get(substr($string, 1, -1));
	}

	$array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
	if (strpos($string, ':')) {
		$value = array();
		foreach ($array as $val) {
			list($k, $v) = explode(':', $val);
			$value[$k]   = $v;
		}
	} else {
		$value = $array;
	}
	return $value;
}

/**
 * 获取栏目子ID
 * @param  integer $id    分类ID
 * @return array         
 */
function get_category_child($id=0,$model='',$id_field='id',$pid_field='pid',$where=[],$field='*') {
    $key_str = 'sys_'.$model.'_'.$id.'_list';
    
	$list = cache($key_str);
	/* 读取缓存数据 */
	if (empty($list)) {
		$list = db($model)->where($where)->field($field)->select();
		cache($key);
	}
	$ids[] = $id;
	foreach ($list as $key_str => $value) {
		if ($value[$pid_field] == $id) {
            $ids[] = $value[$id_field];
			$ids   = array_merge($ids, get_category_child($value[$id_field]),$model,$id_field,$pid_field,$where,$field);
		}
	}
	return array_unique($ids);
}

/**
 * 获取指定分类的所有子分类ID号
 *
 * @param integer $id
 * @param string $model
 * @param string $id_field
 * @param string $pid_field
 * @param array $where
 * @param string $field
 * @return void
 */
function get_allchild_ids($id=0,$model='',$id_field='id',$pid_field='pid',$where=[],$field='*')
{
    //初始化ID数组
    $array[] = $id;
    do
    {
        $ids = '';
        $where[$pid_field] = array('in',$id);
        $cate = db($model)->where($where)->field($field)->select();
        foreach ($cate as $k=>$v){
            $array[] = $v[$id_field];
            $ids .= ',' . $v[$id_field];
        }
        $ids = substr($ids, 1, strlen($ids));
        $id = $ids;
    }
    while (!empty($cate));
    $ids = implode(',', $array);
    return $ids;    //  返回字符串
    //return $array //返回数组
}

/**
 * 获取指定分类所有父ID号
 *
 * @param integer $id
 * @param string $model
 * @param string $id_field
 * @param string $pid_field
 * @param array $where
 * @param string $field
 * @return void
 */
function get_allfcate_ids($id=0,$model='',$id_field='id',$pid_field='pid',$where=[],$field='*')
{
    //初始化ID数组
    $array[] = $id;
    do
    {
        $ids = '';
        $where[$id_field] = array('in',$id);
        $cate = db($model)->where($where)->field($field)->select();
        foreach ($cate as $v){
            $array[] = $v[$pid_field];
            $ids .= ',' . $v[$pid_field];
        }
        $ids = substr($ids, 1, strlen($ids));
        $id = $ids;
    }
    while (!empty($cate));

    $ids = implode(',', $array);
     return $ids;   //  返回字符串
    //return $array //返回数组
}

/**
 * 将传入的字符串改为比如：****瑞
 */
function strSpcialForma($str, $pix='*'){
    if($str == ''){
        return '';
    }
    if(mb_strlen($str) < 2){
        return '*'.$str;
    }else{
        $str_len = mb_strlen($str, 'utf-8');
        //取得最后一个字
        $last_char = mb_substr($str, ($str_len-1), 1,'utf-8');
        $return_str = str_repeat($pix, ($str_len-1)).$last_char;
        return $return_str;
    }
}

//保存图片到本地
function download_img($url, $path = '/public/uploads/wechat/')
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
  $file = curl_exec($ch);
  curl_close($ch);

  $filename = 's'.time().mt_rand(1000,9999).'.jpg';

//   dump($filename);die;

  $path = SITE_PATH.$path. $filename;
  $resource = fopen($path, 'a');
  fwrite($resource, $file);
  fclose($resource);
  return $path;
}




