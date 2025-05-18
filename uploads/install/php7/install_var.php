<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
define('KEKE_OFF', FALSE );
define('ENV_ERROR', 2);
$func_dir = array('config_dir' => array('type' => 'dir', 'path' => './config'),'uploads' => array('type' => 'dir', 'path' => './data/uploads'));
$gdarr=gd_info();
$gddata=explode(" ",$gdarr["GD Version"]);
$gddata=str_replace("(","",$gddata[1]);

$func_items = array('mysqli_connect', 'fsockopen', 'gethostbyname', 'file_get_contents', 'xml_parser_create');
$env_items = array(
'os' => array('c' => 'PHP_OS', 'r' =>PHP_OS, 'b' => 'unix'),
'php' => array('c' => 'PHP_VERSION', 'r' => PHP_VERSION, 'b' => '5.4 以上'),
'attachmentupload' => array('r' =>get_cfg_var ("upload_max_filesize")?get_cfg_var ("upload_max_filesize"):"不允许上传", 'b' => '2M'),
'gdversion' => array('r' =>$gddata, 'b' => '2.0'),
);

$dirfile_items = array (
	'config' => array (
		'type' => 'dir',
		'path' => '../config'
	),
	'data_dir' => array (
		'type' => 'dir',
		'path' => '../data'
	),
	'news_dir' => array (
		'type' => 'dir',
		'path' => '../news'
	),
	'about_dir' => array (
		'type' => 'dir',
		'path' => '../about'
	)
);