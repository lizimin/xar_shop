<?php
 
	error_reporting(1);
 	$site_name = $_GET['site'] ? $_GET['site'] : 'XAR_shop';
	$target = '/data/www/'.$site_name; // 生产环境web目录
 
	$token = 'pulltoken';
	$wwwUser = 'nobody';
	$wwwGroup = 'nobody';
 
	$json = json_decode(file_get_contents('php://input'), true);
 	// var_dump($json);
	if (empty($json['token']) || $json['token'] !== $token) {
	    // exit('error request');
	}
 
	$repo = $json['repository']['name'];
 
	// $cmds = array(
	//     "cd $target && git pull",
	//     "chown -R {$wwwUser}:{$wwwGroup} $target/",
	// );
 
	// foreach ($cmds as $cmd) {
	//     shell_exec($cmd);
	// }
	
	// 感谢@墨迹凡指正，可以直接用www用户拉取代码而不用每次拉取后再修改用户组
	
	$cmd = "cd $target && sudo -u root git pull 2<&1";
	
	$res = exec($cmd);
	var_dump($res);
	var_dump($cmd);