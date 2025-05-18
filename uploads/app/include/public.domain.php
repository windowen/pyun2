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

if($config['sy_web_site']=="1"){
    
    $protocol	=	getprotocol($config['sy_weburl']);
    
    $host		=	$protocol.$_SERVER['HTTP_HOST'];
    
	//本地路径不做验证
	if(!strpos($host,"localhost")&& !strpos($host,"127.0.0.1")){
		
		$indexDir = $_GET['indexdir'];
		//非目录形式 根据cookie信息验证
		if(!$indexDir){
			//
			if(isMobileUser()){
				if(($_COOKIE['cityid'] || $_COOKIE['hyclass'] || $_COOKIE['three_cityid'] || $_COOKIE['province']) && $_COOKIE['did']){
				
					$isSiteInfo = 1;
				}
			}else{
				
				if(isset($_COOKIE['sy_weburl']) && $_COOKIE['sy_weburl'] == $host){
					
					$isSiteInfo = 1;
				}
			}
		}
		
		if(!$isSiteInfo){
			include(PLUS_PATH."/domain_cache.php");
			if(is_array($site_domain)){
				foreach($site_domain as $key=>$value){
					if(($value['host']==$_SERVER['HTTP_HOST'] && $value['mode'] != '2') || ($indexDir!='' && $value['indexdir']==$indexDir && $value['mode'] == '2')){
						$value['did']			=	$value['id'];
						$value['sy_webname']	=	$value['webname'];
						$value['sy_webtitle']	=	$value['webtitle'];
						$value['sy_logo']		=	$value['weblogo'];
						$value['sy_webkeyword'] =	$value['webkeyword'];
						$value['sy_webmeta']	=	$value['webmeta'];
						
						$domainInfo = $value;

						break;
					}
				}
			}
			
			setcookies(
				array(
				'sy_weburl'=>'',
				'did'=>'',
				'fz_type'=>'',
				'province'=>'',
				'cityid'=>'',
				'hyclass'=>'',
				'three_cityid'=>'',
				'cityname'=>'',
				'sy_webkeyword'=>'',
				'sy_logo'=>'',
				'style'=>'',
				'sy_webtitle'=>'',
				'sy_webmeta'=>'',
				'sy_webname'=>''
			),time()-86400,$domainUrl);
		}else{
			
			$domainInfo = $_COOKIE;
		
		}
		if($indexDir && $domainInfo['indexdir']!=$indexDir){
			Header("HTTP/1.1 404 Not Found"); 
			exit();

		}
		if($domainInfo){

			
			include(PLUS_PATH."/city.cache.php");
			include(PLUS_PATH."/industry.cache.php");

			$parseDate['did']					=	(int)$domainInfo['did'];
			$parseDate['fz_type']				=	(int)$domainInfo['fz_type'];
			if($parseDate['fz_type']=='1'){

				if($domainInfo['three_cityid']>0){
					$parseDate['province']		=	(int)$domainInfo['province'];
					$parseDate['cityid']		=	(int)$domainInfo['cityid'];
					$parseDate['three_cityid']	=	(int)$domainInfo['three_cityid'];
					$parseDate['cityname']		=	$city_name[$domainInfo['three_cityid']];
				}elseif($domainInfo['cityid']>0){
					$parseDate['province']		=	(int)$domainInfo['province'];
					$parseDate['cityid']		=	(int)$domainInfo['cityid'];
					$parseDate['three_cityid']	=	0;
					$parseDate['cityname']		=	$city_name[$domainInfo['cityid']];
				}else{
					$parseDate['province']		=	(int)$domainInfo['province'];
					$parseDate['cityid']		=	0;
					$parseDate['three_cityid']	=	0;
					$parseDate['cityname']		=	$city_name[$domainInfo['province']];
				}
			}else if($parseDate['fz_type']=='2'&&$domainInfo['hy']){
				$parseDate['hyclass']			=	(int)$domainInfo['hy'];
				$parseDate['cityname']			=	$domainInfo['webname'];
				
			}
			
			if($domainInfo['sy_webname']){$parseDate['sy_webname']  =	$domainInfo['sy_webname'];}
			if($domainInfo['sy_webtitle']){$parseDate['sy_webtitle']  =	$domainInfo['sy_webtitle'];}
			if($domainInfo['sy_logo']){$parseDate['sy_logo']  =	$domainInfo['sy_logo'];}
			if($domainInfo['sy_webkeyword']){$parseDate['sy_webkeyword']  =	$domainInfo['sy_webkeyword'];}
			if($domainInfo['sy_webmeta']){$parseDate['sy_webmeta']  =	$domainInfo['sy_webmeta'];}
			if($domainInfo['style']){$parseDate['style']  =	$domainInfo['style'];}
			if(!$indexDir && strpos($host,$config['sy_wapdomain'])===false){

				$parseDate['sy_weburl']  =	$host;
			}
			
			$config = array_merge($config,$parseDate);
			
			if(!$indexDir){
				if(strpos($host,$config['sy_onedomain'])!==false){
					$domainUrl  = $config['sy_onedomain'];
				}else{
					$domainUrlAll  = parse_url($host);
					$domainUrl = get_domain($domainUrlAll['host']);
				}
			
				setcookies($parseDate,time()+86400,$domainUrl);
			}
		}
	}
}

?>