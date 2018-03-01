<?php
// +----------------------------------------------------------------------
// | 彩龙平台 [基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.lishaoen.com
// +----------------------------------------------------------------------
// | Author: lishaoen <lishaoenbh@qq.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]

header('Content-Type:text/html;charset=utf-8');
header('Access-Control-Allow-Origin:*');
//错误等级定义
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//定义是前台
define('IN_HOME', true);
//定义是前台
define('IN_API', true);
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.5.0','<'))  die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');
// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
define('SITE_PATH', __DIR__);

//项目定义
define('BASE_PATH', substr($_SERVER['SCRIPT_NAME'], 0, -10));

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
