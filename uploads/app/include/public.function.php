<?php
/*
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 *
 ***功能性通用函数库
 */
    //检查手机格式
	function CheckMoblie($moblie){
	    if(!preg_match("/1[3456789]{1}\d{9}$/",trim($moblie))){
	        return false;
	    }else{
	        return true;
	    }
	}
	function CheckRegUser($str){
		if(!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9\-@#.\$_!]+$/u",$str)){
			return false;
		}else{
			return true;
		}
	}
	function CheckTell($idcard){
	    //if(preg_match("/0\d{2,3}-\d{7,8}/",$idcard)==0){
		if(preg_match("/^[0-9-]+?$/",$idcard)==0){
			return false;
		}else{
			return true;
		}
	}
    function CheckRegEmail($email){
		if(!preg_match('/^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/',$email)) {
			return false;
		}else{
			return true;
		}
	}
    //用户名注册复杂度判断
    function regUserNameComplex($name){

        $msg    =   '';

        if($name){
            
            global $config;

            $reg_namemaxlen =   $config['sy_reg_namemaxlen'];//用户名长度最大值
            $reg_nameminlen =   $config['sy_reg_nameminlen'];//用户名长度最小值
            $reg_name_sp    =   $config['reg_name_sp'];//是否必须包含其它字符@!#.$-_
            $reg_name_zm    =   $config['reg_name_zm'];//是否必须包含字母
            $reg_name_num   =   $config['reg_name_num'];//是否必须包含数字
            
            $namelen = mb_strlen($name);
            //长度
            if($namelen <$reg_nameminlen||$namelen >$reg_namemaxlen){

                $msg            =       '用户名应在'.$reg_nameminlen.'-'.$reg_namemaxlen.'位字符之间！';
            }else{
                $smsg   =$zmsg   =$nmsg   =$douhao =   '';

                //数字
                if($reg_name_num==1) {

                    if(!preg_match("/[0-9]+/u",$name)){
                        $nmsg            =       '数字';
                        $douhao          =       '，';
                    }
                }
                //字母
                if($reg_name_zm==1) {
                    if(!preg_match('/[a-zA-Z]+/u',$name)){
                        $zmsg            =       $douhao.'字母';
                        $douhao          =       '，';
                    }
                }
                //其它字符
                if($reg_name_sp==1) {
                    if(!preg_match('/[-@#.$_!]+/u',$name)){
                        $smsg            =       $douhao.'其它字符@!#.$-_';
                    }
                }

                if($nmsg || $zmsg || $smsg){

                    $msg   =   '用户名必须包含'.$nmsg.$zmsg.$smsg;

                }
            }

            
            
        }
        return $msg;
    }
    //验证密码复杂度
    function regPassWordComplex($name){

        $msg    =   '';

        if($name){
            
            global $config;

            $reg_pw_sp    =   $config['reg_pw_sp'];//是否必须包含其它字符@!#.$-_
            $reg_pw_zm    =   $config['reg_pw_zm'];//是否必须包含字母
            $reg_pw_num   =   $config['reg_pw_num'];//是否必须包含数字
            
            $smsg   =$zmsg   =$nmsg   =$douhao =   '';
            //数字
            if($reg_pw_num==1) {

                if(!preg_match("/[0-9]+/u",$name)){
                    $nmsg            =       '数字';
                    $douhao          =       '，';
                }
            }
            //字母
            if($reg_pw_zm==1) {
                if(!preg_match('/[a-zA-Z]+/u',$name)){
                    $zmsg            =       $douhao.'字母';
                    $douhao          =       '，';
                }
            }
            //其它字符
            if($reg_pw_sp==1) {
                if(!preg_match('/[-@#.$_!]+/u',$name)){
                    $smsg            =       $douhao.'其它字符@!#.$-_';
                }
            }
            if($nmsg || $zmsg || $smsg){

                $msg   =   '密码必须包含'.$nmsg.$zmsg.$smsg;
            }
            
        }
        return $msg;
    }
    //把数组生成字符串
    function ArrayToString($obj,$withKey=true,$two=false){
    	if(empty($obj))	return array();
    	$objType=gettype($obj);
    	if ($objType=='array') {
    		$objstring = "array(";
    	    foreach ($obj as $objkey=>$objv) {
    			if($withKey)$objstring .="\"$objkey\"=>";
    			$vtype =gettype($objv) ;
    			if ($vtype=='integer') {
                    $objstring .="$objv,";
    			}else if ($vtype=='double'){
                    $objstring .="$objv,";
    			}else if ($vtype=='string'){
                    $objv= str_replace('"',"\\\"",$objv);
                    $objstring .="\"".$objv."\",";
    			}else if ($vtype=='array'){
                    $objstring .="".ArrayToString($objv,$withKey,$two).",";
    			}else if ($vtype=='object'){
                    $objstring .="".ArrayToString($objv,$withKey,$two).",";
    			}else {
                    $objstring .="\"".$objv."\",";
    			}
    	    }
    		$objstring = substr($objstring,0,-1)."";
    		return $objstring.")\n";
    	}
    }
    
    /*获取真实IP地址否则返回Unknown*/
    function fun_ip_get()
    {
        
        
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            
            return is_ip($_SERVER['HTTP_CLIENT_IP']);
            
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            
            return is_ip($_SERVER['HTTP_X_FORWARDED_FOR']);
            
        }else{
            
            return is_ip($_SERVER['REMOTE_ADDR']);
        }
    }
    
    function is_ip($str)
    {
        if (stripos($str, ',') !== false){
            
            $strArr  =  explode(',', $str);
            $ip      =  $strArr[0];
            
        }else{
            
            $ip  =  $str;
        }
        
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
            
            return $ip;
            
        }elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
            
            return $ip;
        }else{
			 return '未知IP';
		}
       
    }


    
    /*返回当前城市*/
    function getLocalCity(){
    	$ip = fun_ip_get();
    	$cityInfo = array();
    	$url = "http://user.58.com/userdata/getlocal/";
    	$curl = curl_init();
    	curl_setopt($curl, CURLOPT_URL, $url);
    	curl_setopt($curl, CURLOPT_HEADER, 0);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	if($ip != "127.0.0.1"){
    		curl_setopt($curl, CURLOPT_HTTPHEADER , array('X-FORWARDED-FOR:'.$ip, 'CLIENT-IP:'.$ip) );
    	}
    	
    	curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    	$ret = curl_exec($curl);
    		
    	if(false !== $ret) {
    		$ret = str_replace('list', '"list"', $ret);
    		$ret = str_replace('local', '"local"', $ret);
    		$ret = str_replace('ishome', '"ishome"', $ret);
    		$ret = str_replace("'", '"', $ret);
    		$output = json_decode($ret,true);
    		curl_close($curl);
    		$local = $output['local'];
    		include(PLUS_PATH."domain_cache.php");	
    		$i=0;
    		foreach($site_domain as $key=>$value ){
    			if (strpos($value['cityname'],$local) !== false){
    				$cityInfo = $value;
    				break;
    			}
    		}
    	}
    	return $cityInfo;
    }
   
    function go_to_city($config){
    	
    	$city=getLocalCity();
    	
    	SetCookie("gotocity",'1',time() + 3600, "/");//一个小时内不再判断
    	if(!empty($city)){
			if($city['mode'] == 'domain'){
				header('Location: http://'.$city['domain']);
			}else{
				header('Location: '.$config['sy_weburl'].'/'.$city['indexdir'].'/');
			}
			
    		exit();//停止执行
		}
    }
    //从内容中分出图片
    function getUploadPic($content,$count=0){
    	$content=str_replace('"','',$content);
    	$content=str_replace('\'','',$content);
    	$content=str_replace('>',' width="">',$content);
    	$pattern=preg_match_all('/<img[^>]+src=(.*?)\s[^>]+>/im' ,$content,$match);
    	if($match[1]){
    		if($count>0){
    			$i=0;
    			foreach($match[1] as $v){
    				if(!empty($v)){
    					$pic[]=$v;
    					$i++;
    					if($i>=$count){
    						break;
    					}
    				}
    			}
    			return $pic;
    		}
    		return $match[1];
    	}
    	return array();
    }
    //判断来路
    function dreferer($default = '') {
        $referer=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
        if(strpos('a'.$referer,Url('user','login'))) {
            $referer = $default;
        }else{
            $referer = substr($referer, -1) == '?' ? substr($referer, 0, -1) : $referer;
        }
        return $referer;
    }
   
   
    //判断是否是手机或PC客户端来路
    function UserAgent(){    
        $user_agent = ( !isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];    
    	if ((preg_match("/(iphone|ipod|android)/i", strtolower($user_agent))) AND strstr(strtolower($user_agent), 'webkit')){    
        	return true;    
    	}else if(trim($user_agent) == '' OR preg_match("/(nokia|sony|ericsson|mot|htc|samsung|sgh|lg|philips|lenovo|ucweb|opera mobi|windows mobile|blackberry)/i", strtolower($user_agent))){   
    		return true;   
    	}else{//PC   
    		return true;  
    	}    
    }
    //获取顶级域名
    function get_domain($host) {
        $host=strtolower($host);
        if(strpos($host,'/')!==false){
            $parse = @parse_url($host);
            $host = $parse['host'];
        }
        $topleveldomaindb=array('com','edu','gov','int','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','mobi','cc','me'); $str='';
        foreach($topleveldomaindb as $v){
            $str.=($str ? '|' : '').$v;
        }
        $matchstr="[^\.]+\.(?:(".$str.")|\w{2}|((".$str.")\.\w{2}))$";
        if(preg_match("/".$matchstr."/ies",$host,$matchs)){
            $domain=$matchs['0'];
        } else{
            $domain=$host;
        }
        return $domain;
    }
     
    //配置文件生成方法可定义数组名
    function made_web($dir,$array,$config){
        $content="<?php \n";
        $content.="\$$config=".$array.";";
        $content.=" \n";
        $content.="?>";
        $fpindex=@fopen($dir,"w+");
        @fwrite($fpindex,$content);
        @fclose($fpindex);
    }
    //配置文件生成方法，数组遍历打印
    function made_web_array($dir,$array){
        $content="<?php \n";
        if(is_array($array)){
            foreach($array as $key=>$v){
                if(is_array($v))
                {
                    $content.="\$$key=array(";
                    $content.=made_string($v);
                    $content.=");";
                }else{
                    $v = str_replace("'","\\'",$v);
                    $v = str_replace("\"","'",$v);
                    $v = str_replace("\$","",$v);
                    $content.="\$$key=".$v.";";
                }
                $content.=" \n";
            }
        }
        $content.="?>";
        $fpindex=@fopen($dir,"w+");
        $fw=@fwrite($fpindex,$content);
        @fclose($fpindex);
        return $fw;
    }
    //字符串生成方法，可拼接
    function made_string($array,$string=''){
    	if(is_array($array) && !empty($array)){
    	 	$i = 0;
    		foreach($array as $key=>$value){
    			if($i>0){$string.=',';}
    			if(is_array($value)){
    				$string.="'".$key."'=>array(".made_string($value).")";
    			}else{
    				$string.="'".$key."'=>'".str_replace('\$','',$value)."'";
    			}
    			$i++;
    		}
    	}
      return $string;
    }
    //删除文件方法
    function delfiledir($delfiles){
        $delfiles = stripslashes($delfiles);
        $delfiles = str_replace("../","",$delfiles);
        $delfiles = str_replace("./","",$delfiles);
        $delfiles = "../".$delfiles;
        $p_delfiles = path_tidy($delfiles);
        if($p_delfiles!=$delfiles){die;}
        if(is_file($delfiles)){
            @unlink($delfiles);
        }else{
            $dh=@opendir($delfiles);
            while($file=@readdir($dh)){
                if($file!="."&&$file!=".."){
                    $fullpath=$delfiles."/".$file;
                    if(@is_dir($fullpath)){
                        delfiledir($fullpath);
                    }else{
                        @unlink($fullpath);
                    }
                }
            }
            @closedir($dh);
            if(@rmdir($delfiles)){
                return  true;
            }else{
                return false;
            }
        }
    }
    //路径'//'处理成'/'
    function path_tidy($path) {
        $tidy = array();
        $path = strtr($path, '\\', '/');
        foreach(explode('/', $path) as $i => $item) {
            if($item == '' || $item == '.' ) {
                continue;
            }
            if($item == '..' && end($tidy) != '..' && $i > 0) {
                array_pop($tidy);
                continue;
            }
            $tidy[] = $item;
        }
        return ($path[0]=='/'?'/':'').implode('/', $tidy);
    }
    //图片删除
    //索引 2 给出的是图像的类型，返回的是数字，其中1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，
    //	8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
    function unlink_pic($pic){
        $pictype=getimagesize($pic);
        if($pictype[2]=='1' || $pictype[2]=='2' || $pictype[2]=='3'){
            @unlink($pic);
        }
    }
    //批量操作中的ID检查，只能是数字字母与系统内置的分隔符
    function pylode($string,$array){
		if(is_array($array)){
			$str = @implode($string,$array);
		}else{
			$str = $array;
		}
		if(!preg_match("/^[0-9a-zA-Z".$string."]+$/",$str)){
			$str = 0;
		}
		return $str;
    }
    //获取微信 TOKEN
    function getToken($config=array()){
		$config = '';
		
		include(PLUS_PATH.'configcache.php');
		$Token = $configcache['token'];
		$TokenTime = $configcache['token_time'];
		$NowTime = time();
		
		if(($NowTime-$TokenTime)>7000 || !$Token){

			include(PLUS_PATH.'config.php');

			$Appid       = $config['wx_appid'];
			$Appsecert   = $config['wx_appsecret'];
			$Url         = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$Appid.'&secret='.$Appsecert;
			$CurlReturn  = CurlPost($Url);
			
			
			$Token = json_decode($CurlReturn);
			
			$configcache['token']      = $Token->access_token;
			$configcache['token_time'] = time();
			if($configcache['token']){
				made_web(PLUS_PATH."configcache.php",ArrayToString($configcache),"configcache");
			}
			
			return $configcache['token'];
		}else{
			return $Token;
		}
    }
    //获取微信 JS TOKEN
    function getWxTicket(){
    	
		include(PLUS_PATH.'configcache.php');
    	$Ticket = $configcache['ticket'];
    	$TicketTime = $configcache['ticket_time'];
    	$NowTime = time();
    	if(($NowTime-$TicketTime)>7000 || !$Ticket){

    		$Url         = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.getToken().'&type=jsapi';
    		$CurlReturn  = CurlPost($Url);
    		$Ticket       = json_decode($CurlReturn);
    		$configcache['ticket']      = $Ticket->ticket;
    		$configcache['ticket_time'] = time();
    		if($configcache['ticket']){
    			made_web(PLUS_PATH."configcache.php",ArrayToString($configcache),"configcache");
			}
    		return $configcache['ticket'];
    	}else{
    		return $Ticket;
    	}
    }
    //初始化微信SDK参数
    function getWxJsSdk($url='') {
    	include(PLUS_PATH.'config.php');
    	$Ticket = getWxTicket();
    	if(empty($url)){
    	     
    	    if (!empty($config['sy_wapdomain'])){
    	        if($config['sy_wapssl']=='1' ){
    	            $protocol = 'https://';
    	        }else{
    	            $protocol = 'http://';
    	        }
    	    }else{
    	        $protocol = getprotocol($config['sy_weburl']);
    	    }

    		$url = $protocol.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
    	}
    	$timestamp = time();
    	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    	$nonceStr = "";
    	for ($i = 0; $i < 16; $i++) {
    	  $nonceStr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    	}
    	// 这里参数的顺序要按照 key 值 ASCII 码升序排序
    	$string = "jsapi_ticket=".$Ticket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
    	$signature = sha1($string);
    	$signPackage = array(
    	  "appId"     => $config['wx_appid'],
    	  "nonceStr"  => $nonceStr,
    	  "timestamp" => $timestamp,
    	  "url"       => $url,
    	  "signature" => $signature,
    	  "rawString" => $string
    	);
    	return $signPackage; 
     }
    //CURL POST提交
    function CurlPost($url,$data='',$multiple = 1,$headers=''){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL,$url);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
    	if ($headers) {
    	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	}
    	if($data!=''){
    		curl_setopt($ch, CURLOPT_POST, 1);
    		if ($multiple == 1){
    		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    		}elseif ($multiple == 2){
    		    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    		}
    	}
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	$Return=curl_exec ($ch);
    	if (curl_errno($ch)) {
    	    //echo 'Errno'.curl_error($ch);
    	}
    	curl_close($ch);
    	return $Return;
    }
    //CURL GET提交
    function CurlGet($url){
        $ch = curl_init();
        $timeout = 20;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $Return = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Errno'.curl_error($ch);
        }
        curl_close($ch);
        return $Return;
    }
    //跳转手机页面
    function wapJump($config){
		
    	global $ModuleName;
    	$Loaction = '';
    	$mArray   = array('qqconnect','sinaconnect','call');
    	$cArray   = array('clickhits','wjump');

    	if($config['sy_wap_jump']=='1' && !in_array($ModuleName,$mArray) && !in_array($_GET['c'],$cArray)){

    		if(isMobileUser($config) && !strpos(strtolower($_SERVER['REQUEST_URI']), 'wap') && $_SERVER['HTTP_HOST'] != $config['sy_wapdomain']){
 
				include(PLUS_PATH."jump.cache.php");
				
    			if($_GET['c']){
    				$_GET['a'] = $_GET['c'];
    			}
    			if($ModuleName && $ModuleName!='index'){
    				$_GET['c'] = $ModuleName;
    				if($wapA[$ModuleName][$_GET['a']]){

    					$_GET['a'] = $wapA[$ModuleName][$_GET['a']];
    				}
    			}
    			if($_GET['c']){
                  	$jumpGet['c'] = $_GET['c'];
                    unset($_GET['c']);
                 }
                if($_GET['a']){
                  	$jumpGet['a'] = $_GET['a'];
                    unset($_GET['a']);
                }
				
                if(!empty($_GET)){

					foreach($_GET as $key=>$value){

						if($key == 'keyword'){
							$jumpGet[$key]	=	$value;
						}

						if($key == 'code'){
							$jumpGet[$key]	=	$value;
						}

						if($value != 0 && !empty($value)){

							$jumpGet[$key] = $value;

						}

					}
                }

				$Loaction =  Url('wap',$jumpGet);
				
    			
    			// 处理分站目录跳转
    			if(isset($_GET['indexdir'])){
    			    
    			    $indexDir	=	$_GET['indexdir'];
    			    unset($_GET['indexdir']);
    			    
    			    $Loaction  =  str_replace('/indexdir_'.$indexDir.'.html','/'.$indexDir.'/',$Loaction);
    			}
    		}
    	}
    	return $Loaction;
    }
    
    //使用终端是否手机判断方法
    function isMobileUser($config = array()){
        
        $uachar =   '/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile|phone|iphone|ipad|ipod|android|symbian|smartphone)/i';

        $ua     =   strtolower($_SERVER['HTTP_USER_AGENT']);
        
    	if(preg_match($uachar, $ua)){
    		
    		return true;
    	
    	}else{
    	    
    		return false;
    		
    	}
    }
    
    //获取随机数
    function gt_Generate_code($length = 6) {
      return rand(pow(10,($length-1)), pow(10,$length)-1);
    }
	//
	function verifytoken($config){
	
		if($config['code_kind'] == '3'){
			
			$check = gtGeetest($config);

		}elseif($config['code_kind'] == '4'){
			$check = dxauthcode($config);

		}elseif($config['code_kind'] == '5'){

			$check = vaptchacode($config);

		}
		if($check){
			return array('code' => '200');
		}else{
			switch($config['code_kind']){
				
				case '3' : $msg = '请滑动滑块进行验证！';
				break;
				case '4' : $msg = '请拖动滑块进行验证';
				break;
				case '5' : $msg = '请绘制图中手势按钮进行验证';
				break;

			}

			return array('code'=>'0','msg' => $msg);
		}
	
	}
    //检验验证码
    function vaptchacode($config=array()){
		
		if(empty($config)){
			include(PLUS_PATH.'config.php');
		}

		$url = 'http://0.vaptcha.com/verify';
		$data['id']			=	$config['sy_vaptcha_vid'];
		$data['secretkey']	=	$config['sy_vaptcha_key'];
		$data['scene']		=	0;
		$data['token']		=	$_POST['verify_token'];
		$data['ip']			=	fun_ip_get();
		
		
		$CurlReturn		=	CurlPost($url,$data);
		
		$vaptchaReturn	=	json_decode($CurlReturn);
		if($vaptchaReturn -> success == '1'){
			
			return true;
		}else{
			return false;
		}

    }
    //获取极验验码
    function gtGeetest($config = array()){

		if($_POST['verify_token']){
			
			$token	=	@explode('*',$_POST['verify_token']);
			
			$_POST['geetest_challenge']	=	$token[0];

			$_POST['geetest_validate']	=	$token[1];
				
			$_POST['geetest_seccode']	=	$token[2];
            
		}

    	if($_POST['geetest_challenge'] && $_POST['geetest_validate'] && $_POST['geetest_seccode']){
    		if(!isset($_SESSION)){
    			session_start();
    		}
    		require_once LIB_PATH . '/class.geetestlib.php';
    		if(!$config){
    			include(PLUS_PATH.'config.php');
    		}
    		$GtSdk = new GeetestLib($config['sy_geetestid'], $config['sy_geetestkey']);
    // 		if($type=='mobile'){
    // 			$data = array(
    // 				"user_id" => $user_id, # 网站用户id
    // 				"client_type" => "h5", #web:电脑上的浏览器；h5:手机上的浏览器
    // 				"ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
    // 			);
    // 		}else{
    			$data = array(
    				"user_id" => $user_id, # 网站用户id
    				"client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器
    				"ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
    			);
    		//}
    		$user_id = $_SESSION['user_id'];
    		if ($_SESSION['gtserver'] == 1) {   //服务器正常
    			$result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
    			if ($result) {
    				return true;
    			} else{
    				return false;
    			}
    		}else{  //服务器宕机,走failback模式
    			if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
    				return true;
    			}else{
    				return false;
    			}
    		}
    	}else{
    		return false;
    	}
    }
	//检验顶象验证码
    function dxauthcode($config=array()){
    	include (LIB_PATH."dxCaptchaClient.class.php");
		/**构造入参为appId和appSecret
		 * appId和前端验证码的appId保持一致，appId可公开
		 * appSecret为秘钥，请勿公开
		 * token在前端完成验证后可以获取到，随业务请求发送到后台，token有效期为两分钟**/
		$appId = $config['sy_dxappid'];
		$appSecret = $config['sy_dxappsecret'];
		$client = new CaptchaClient($appId,$appSecret);
		$client->setTimeOut(2);      //设置超时时间，默认2秒
		# $client->setCaptchaUrl("http://cap.dingxiang-inc.com/api/tokenVerify");   
		//特殊情况可以额外指定服务器，默认情况下不需要设置
		$response = $client->verifyToken($_POST['verify_token']);
		
		//确保验证状态是SERVER_SUCCESS，SDK中有容错机制，在网络出现异常的情况会返回通过
		if($response->result){
			return true;
			/**token验证通过，继续其他流程**/
		}else{
			return false;
			/**token验证失败**/
		}
    }

    //获取数字验码
    function gtverify(){
    	if(md5(strtolower($_POST['authcode']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
          unset($_SESSION['authcode']);
          return false;
      }
      return true;
    }
    
    function is_weixin(){ 
    	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
    		return true;
    	}
    	return false;
    }
   
    function setcookies($parseDate=array(),$time,$domain){
    	
    	$domain = get_domain($domain);
    	if(is_array($parseDate)){
    		foreach($parseDate as $key=>$value){
    			SetCookie($key,$value,$time,"/",$domain);
    		}
    	}
    }

    // 上面方法得到的密文太长，不适合放入承载信息有限的二维码中
    // 加密
    function yunEncrypt($str, $key)
    {
        $key = md5($key);
        $k = md5(rand(0, 100)); // 相当于动态密钥
        $k = substr($k, 0, 3);
        $tmp = "";
        for ($i = 0; $i < strlen($str); $i ++) {
            $tmp .= substr($str, $i, 1) ^ substr($key, $i, 1);
        }
        return base64_encode($k . $tmp);
    }
    
    // 解密
    function yunDecrypt($str, $key)
    {
        $len = strlen($str);
        $key = md5($key);
        $str = base64_decode($str);
        $str = substr($str, 3, $len - 3);
        $tmp = "";
        for ($i = 0; $i < strlen($str); $i ++) {
            $tmp .= substr($str, $i, 1) ^ substr($key, $i, 1);
        }
        return $tmp;
    }
    //数组排序
    function my_sort($prev, $next){
    	if ($prev['value'] == $next['value']) return 0;
    	return ($prev['value'] < $next['value']) ? 1 : -1;
    }
    function t_sort($prev, $next){
    	$p = strtotime($prev);
    	$n = strtotime($next);
    	if ($p == $n) return 0;
    	return ($p > $n) ? 1 : -1;
    }
    
   
    
    //判断当前服务器是windows系统还是其他系统
    function isServerOsWindows(){
      return stristr(php_uname('s'), 'window') ? true : false;
    }
    
    function mb_unserialize($serial_str) {
    	$serial_str = str_replace("\r", "", $serial_str);
    	$serial_str = preg_replace_callback('/s:\d+:"(.+?)";/s','checkunserialize', $serial_str);
    	return unserialize($serial_str);
    }
    
    function checkunserialize($r){
    	$n = strlen($r[1]);
    	return "s:$n:\"$r[1]\";";
    }
    
    //隐藏部分用户名 手机号段 
    function sub_string($str){
    	
    
    	$length = mb_strlen($str);
    
    	if($length > 5){
    		
    		$str = mb_substr($str, 0, 4).'****'.mb_substr($str, $length-4, 4);
    	}
    	return $str;
    }
	
		/**
	 * 检查图片路径，该路径是否有图片，没有图片替换指定图片
	 * @param string $post 默认图片路径
	 * @param string $url  现有路径
	 * @return string
	 */
	function checkpic($url='', $post=''){
		
		global $config;

		if(isset($config['sy_oss']) && $config['sy_oss'] == 1){
            $curl =  $config['sy_ossurl'];
        }else{
            $curl =  $config['sy_weburl'];
        }

		$picurl		=	'';
		
		if($url!=''){
		    
		    if(strstr($url, 'http') !== false || strstr($url, 'https') !== false){
		        
		        $picurl	 = 	$url;
		        
		    }else{
		        
		        if(strstr($url, '../data') !== false){
		            
		            $picurl	 =  str_replace('../data', $curl.'/data', $url);
		            
		        }elseif(strstr($url, './data') !== false){
		            
		            $picurl	 =  str_replace('./data', $curl.'/data', $url);
		            
		        }elseif(strstr($url, '/data') !== false){
		            
		            $picurl	 =  str_replace('/data', $curl.'/data', $url);
		        }elseif(strstr($url, '.data') !== false){
		            
		            $picurl	 =  str_replace('.data', $curl.'/data', $url);
		                
		        }else{
		            
		            $picurl	 =  $curl.'/'.$url;
		        }
		    }
		}else{
			
			if($post!=''){
					
				if(strstr($post, 'http') !== false || strstr($url, 'https') !== false){
					
					$picurl = $post;
				}else{
					$picurl	=	$curl.'/'.$post;
					
				}
			}
		}
	    return $picurl;
	}

    // 全局密码生成函数
    function passCheck($pass, $salt = '', $oldpass = '')
    {
        include_once(LIB_PATH."pwdtype/phpyunpass.php");	

		return passwordCheck($pass, $salt , $oldpass );
    }

	function getprotocol($weburl = ''){
		
		if($weburl)
		{
			
			if(strpos($weburl,'https://')!==false)
			{
				$protocol = 'https://';
			}else{
				$protocol = 'http://';
			}

		}else{
			
			$protocol  =  ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		
		}
	
	
		return $protocol;
	}
	
	/**
	 * @desc 时间日期格式化为多少天前/后
	 * @param sting/int $date_time
	 * @param int $type 1、'Y-m-d H:i:s' 2、时间戳
	 * @param int $before 1、前 2、后
	 * @return string
	 */
	function format_datetime($date_time, $type = 1, $before = 1, $format = '')
	{
	    if ($type == 1) {
	        
	        $timestamp  =   strtotime($date_time);
	    } elseif ($type == 2) {
	        
	        $timestamp  =   $date_time;
	        $date_time  =   date('Y-m-d H:i:s', $date_time);
	    }
	    
	    if (!empty($format)) {
	        return date($format, $timestamp);
	    }
	    
	    if($before == 1 ){
	    
    	    $difference    =   time() - $timestamp;
    	    
    	    $today         =   time() - strtotime('today');
    	    
    	     
    	    
    	    if($difference < $today){
    	        return '今天';
    	    }elseif ($difference <= ($today + 86400)) {
    	        return '昨天';
    	    }elseif ($difference <= 2592000) {
    	        return ceil($difference / 86400) . '天前';
    	    }elseif ($difference <= 31536000) {
    	        return ceil($difference / 2592000) . '个月前';
    	    } else {
    	        return ceil($difference / 31536000) . '年前';
    	    }
    	    
	    }else if ($before == 2){
	        
	        $difference    =   $timestamp - time();
	        
	        $today         =   strtotime('tomorrow') - time();
	        
	        if ($difference <= $today) {
	            return '今天';
	        } elseif ($difference <= 86400) {
	            return '明天';
	        } elseif ($difference <= 172800) {
	            return '后天';
	        } elseif ($difference <= 604800) {
	            return '一周后';
	        } elseif ($difference <= 2592000) {
	            return '一月后';
	        } elseif ($difference <= 31536000) {
	            return ceil($difference / 2592000) . '个月后';
	        } else {
	            return round($difference / 31536000) . '年后';
	        }
	        
	    }
	}
	/**
	 * 处理掉二维数组中值为空的参数
	 */
	function removeEmpty($arr){
	    
	    foreach ($arr as $k=>$v){
	        foreach ($v as $mk=>$mv){
	            if (empty($mv)){
	                unset($arr[$k][$mk]);
	            }
	        }
	    }
	    return $arr;
	}
	/**
	 * 判断会员是否到期
	 * @param number $endtime
	 * @return boolean
	 */
	function isVip($endtime = 0){
	    
	    if ($endtime >= strtotime('today') || $endtime == 0){
	        
	        return true;
	        
	    }else{
	        
	        return false;
	    }
	}

    function changeSalary($salary){
        global $config;
        if($salary>0){
            if($config['resume_salarytype']==2){
                $result = floor($salary / 1000 * 10) / 10 . '千';
            }elseif($config['resume_salarytype']==3){
                $result = floor($salary / 1000 * 10) / 10 . 'K';
            }elseif($config['resume_salarytype']==4){
                $result = floor($salary / 1000 * 10) / 10 . 'k';
            }
        }
        
        return $result;
    }
    /**
     * 产生随机字符串
     */ 
    function createstr($length = 10) {
        
        $chars = "abfghijklmprtvwyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

	function checkMsgOpen($config){
		
		 if($config["sy_msg_appkey"]=="" || $config["sy_msg_appsecret"]=="" ||$config['sy_msg_isopen']!='1'){
		
			return false;
		 }else{
			
			return true;
		 }
	
	
	}
?>