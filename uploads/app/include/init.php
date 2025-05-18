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

//伪静态操作start

if(isset($_GET['yunurl']) && $_GET['yunurl']){
	$var=@explode('-',str_replace('/','-',$_GET['yunurl']));
	foreach($var as $p){
	
		$param=@explode('_',$p);
		$_GET[$param[0]]=$param[1];
	}
	unset($_GET['yunurl']);
}


//伪静态操作end
if(isset($_GET['c']) && $_GET['c'] && !preg_match('/^[a-zA-Z0-9_]+$/',$_GET['c'])){
	$_GET['c'] = 'index';
}
if(isset($_GET['a']) && $_GET['a'] && !preg_match('/^[a-zA-Z0-9_]+$/',$_GET['c'])){
	$_GET['a'] = 'index';
}


//获取模块入口文件指定的控制器目录
global $ModuleName,$DirName;

$Loaction = wapJump($config);

if (!empty($Loaction)){

	header('Location: '.$Loaction);exit;
}

//页面缓存开启

include(PLUS_PATH.'cache.config.php');       //缓存配置文件

if($config['webcache']=='1'){
	//wap端缓存
	if(isMobileUser()){
		if($_GET['c']!='resume' && $_GET['a']!='show'){
			if($cache_config['sy_'.$_GET['c'].'_cache']=='1'){
				include_once(LIB_PATH.'web.cache.php');
				$cache=new Phpyun_Cache('./cache',DATA_PATH,$config['webcachetime']);
				$cache->read_cache();
			}
		}
	}else{
		//PC端缓存
		if($ModuleName!='resume' && $_GET['c']!='show'){

			if($ModuleName!='wap' && $_GET['c']!='resume'&&$_GET['a']!='show'){
			
				if($cache_config['sy_'.$ModuleName.'_cache']=='1' && $_GET['c']!='clickhits'){
					include_once(LIB_PATH.'web.cache.php');
					$cache=new Phpyun_Cache('./cache',DATA_PATH,$config['webcachetime']);
					$cache->read_cache();
				}
			}
		}
	}
	
}

//参数$_GET['c']为控制器名称
$ControllerName = isset($_GET['c']) ? $_GET['c'] : '';
//参数$_GET['a']为执行的操作函数名称
$ActionName = isset($_GET['a']) ? $_GET['a'] : '';
//默认情况下，调用控制器index.class.php
if($ControllerName=='')	$ControllerName='index';
//默认情况下，调用操作函数index_action
if($ActionName=='')	$ActionName = 'index';



//二级目录名称与模块的名称的对应列表，以便以后修改二级路径名称

//开启二级目录访问的情况下不允许通过$_GET['m']访问该模块
//未在后台做配置的模块，默认可以访问
if(isset($config['sy_'.$ModuleName.'_web']) && $config['sy_'.$ModuleName.'_web']==2){
    //echo '此模块未开启！';die;
    header('Location: '.Url("error"));exit;
}
//未在后台做配置的模块，默认使用入口文件中指定的模块

//当前模块的控制器目录
$ControllerPath=APP_PATH.'app/controller/'.$ModuleName.'/';
require(APP_PATH.'app/public/common.php');
//引用当前模块的控制器公共文件，当此模块不需要公共函数时，可不写公共控制器
//相应的各控制应当直接继承common类
if(in_array(strtolower($ModuleName) , array('wapadmin'))){	
    include(PLUS_PATH."/admindir.php");
    if($admindir){

        require(APP_PATH.$admindir.'/adminCommon.class.php');
    }else{
        require(APP_PATH.'admin/adminCommon.class.php');
    }
}

if(file_exists($ControllerPath.$ModuleName.'.controller.php')){	
	require($ControllerPath.$ModuleName.'.controller.php');
}

//判断$_GET['c']指向的控制器是否存在，如不存在,则引用index.class.php
if(file_exists($ControllerPath.$ControllerName.'.class.php')){
	
    require($ControllerPath.$ControllerName.'.class.php');
}else{
	//$_GET['c']指向的控制器不存在，则引用index.class.php
	//调用$_GET['c']指向的操作函数，即将$_GET['c']作为$_GET['a']来调用
    $ActionName=$ControllerName;$ControllerName='index';
    if(!file_exists($ControllerPath.$ControllerName.'.class.php')){
        //echo '此模块不存在！';die;
        header('Location: '.Url("error"));exit;
    }else{

        require($ControllerPath.'index.class.php');
    }
}

if($ModuleName=='wap'){$model='wap';}elseif($ModuleName=='wapadmin'){$model='wapadmin';}else{$model='index';}

//控制器名称
$conclass=$ControllerName.'_controller';
//执行的操作函数名称
$actfunc=$ActionName.'_action';

$views=new $conclass($phpyun,$db,$db_config['def'],$model,$ModuleName);
$views->m=$ModuleName;
if(!method_exists($views,$actfunc)){
	$views->DoException();
}
$views->$actfunc();

if(isset($cache)){

	$cache->CacheCreate();
}

?>