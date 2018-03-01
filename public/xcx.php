<?php

header('Content-Type:text/html;charset=utf-8');
//错误等级定义
error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('BASE_PATH', substr($_SERVER['SCRIPT_NAME'], 0, -10));


define('BIND_MODULE','cgj');
//定义是前台
define('IN_HOME', true);


// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';