<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class index_controller extends common
{
	function actMsg($msgUrl,$msg,$status=8){
		$this->ACT_msg($msgUrl, $msg,$status);
	}
	function getmsgurl(){

		if(ismobileuser()){//当前为wap登录
            
            if($this->config['sy_wapdomain']!=""){
                
                if($this->config['sy_wapssl']=='1'){
                    
                    $protocol	=	'https://';
                }else{
                    $protocol	=	'http://';
                }
                
                if(strpos($this->config['sy_wapdomain'],'http://')===false && strpos($this->config['sy_wapdomain'],'https://')===false){
                    
                    $msgurl	=	$protocol.$this->config['sy_wapdomain'];
                }else{
                    $msgurl	=	$this->config['sy_wapdomain'];
                }
			}else{
                $msgurl		=	$this->config['sy_weburl'].'/wap/';
            }
		}else{
            $msgurl			=	$this->config['sy_weburl'];
        }
        return $msgurl;
    }
	function index_action(){

        $UserinfoM	=	$this->MODEL('userinfo');

        $code		=	$_GET['code'];
        $msgUrl		=	$this->getMsgUrl();

        if ($_GET['login'] != '1') {
            
            if ($this->uid != '' && $this->username != '' && empty($code)) {
                
                $this->actMsg($msgUrl, '您已经登录了！');
            }     
        } 
        if ($this->config['sy_qqlogin'] != '1') {
            
            $this->actMsg($msgUrl, '对不起，QQ绑定已关闭！');  
        }
        $this -> seo('qqlogin');
        
        $app_id		=	$this -> config['sy_qqappid'];
        $app_secret	=	$this -> config['sy_qqappkey'];
        $my_url		=	$this -> config['sy_weburl'] . '/qqlogin.php';

        session_start();
        
        if (empty($code)) {
        
            $_SESSION['state']  =	md5(uniqid(rand(), TRUE));
            
            $dialog_url			=	'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id='.$app_id.'&redirect_uri='.urlencode($my_url).'&state='.$_SESSION['state'];
            
            echo ("<script> top.location.href='".$dialog_url."'</script>"); 
        }
        if ($_GET['state'] == $_SESSION['state']) {
            
            $token_url			=	"https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&" . "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url) . "&client_secret=" . $app_secret . "&code=" . $code;
            
            if (! function_exists('curl_init')) {

                echo "请开启CURL函数，否则将无法进行下一步操作！";

                die();

            }
            
            $response = CurlGet($token_url);

            if (strpos($response, "callback") !== false) {

                $lpos		=	strpos($response, "(");
                $rpos		=	strrpos($response, ")");
                $response	=	substr($response, $lpos + 1, $rpos - $lpos - 1);
                $msg		=	json_decode($response);

                if (isset($msg->error)) {

                    echo "<h3>error:</h3>" . $msg->error;
                    echo "<h3>msg  :</h3>" . $msg->error_description;
                    exit();
                }
            }
            $params		=	array();

            parse_str($response, $params);

            $graph_url	=	"https://graph.qq.com/oauth2.0/me?access_token=" . $params['access_token'];

            // QQ互联平台的移动应用和网站应用打通之后可以获取unionid
            if ($this->config['sy_qqdt'] == 1) {

                $graph_url	.=	"&unionid=1";
            }
            
            $str = CurlGet($graph_url);

            if (strpos($str, "callback") !== false) {

                $lpos	=	strpos($str, "(");
                $rpos	=	strrpos($str, ")");
                $str	=	substr($str, $lpos + 1, $rpos - $lpos - 1);
            }
            $user		=	json_decode($str);

            if (isset($user->error)) {

                echo "<h3>error:</h3>" . $user->error;

                echo "<h3>msg  :</h3>" . $user->error_description;

                exit();

            }
            if ($user->openid != "") {

                $qqwhere['qqid']				=	$user->openid;

                if (!empty($user->unionid)) {
                    $qqwhere['qqunionid']  =  array('=',$user->unionid,'OR');
                }
                $userinfo	=	$UserinfoM->getInfo($qqwhere);

                if (is_array($userinfo)) {

                    $qqdata		=	array(
                        'login_date'	=>	time()
                    );
                    if ($user->unionid) {
                        $qqdata['qqunionid']	=	$user->unionid;
                    }
                    $userwhere['uid']	=	$userinfo['uid'];

                    $UserinfoM->upInfo($userwhere,$qqdata);
                   
                    $logtime	=	date("Ymd", $userinfo['login_date']);
                    $nowtime	=	date("Ymd", time());

                    if ($logtime != $nowtime) {

                        $this->MODEL('integral')->invtalCheck($userinfo['uid'], $userinfo['usertype'],"integral_login", "会员登录",22);
                        
                    }
                    if ($this->config['sy_uc_type'] == "uc_center") {

                        $this->obj->uc_open();
                        
                        $user		=	uc_get_user($userinfo['username']);
                        $ucsynlogin	=	uc_user_synlogin($user[0]);
                        
                        $this->cookie->add_cookie($userinfo['uid'], $userinfo['username'], $userinfo['salt'], $userinfo['email'], $userinfo['password'], $userinfo['usertype'], $this->config['sy_logintime'], $userinfo['did']);
                        
                        $this->actMsg($msgUrl, "登录成功！" . $ucsynlogin);
                    
                    } else {
                        
                        $this->cookie->unset_cookie();
                        
                        $this->cookie->add_cookie($userinfo['uid'], $userinfo['username'], $userinfo['salt'], $userinfo['email'], $userinfo['password'], $userinfo['usertype'], $this->config['sy_logintime'], $userinfo['did']);
                        
                        $this->actMsg($msgUrl, "登录成功！");
                    }
                
                } else {
                    $_SESSION['qq']["openid"]       =	$user->openid;
                    $_SESSION['qq']["unionid"]      =	$user->unionid;
                    $_SESSION['qq']["tooken"]       =	$params['access_token'];
                    $_SESSION['qq']["logininfo"]    =	"您已登录QQ，请绑定您的帐户！";
                    
                    if ($this->uid) {
                          
                        $data1	=	array(
                            'qqid'	=>	''
                        );
                        $where1['qqid']				=	$_SESSION['qq']["openid"];
                        if(!empty($_SESSION['qq']["unionid"])){
                            $where1['qqunionid']		=	array('=',$_SESSION['qq']["unionid"],'OR');
                        }
                        
                        $UserinfoM->upInfo($where1,$data1);
                      
                        $data2	=	array(
                            'qqid'		=>	$_SESSION['qq']["openid"],
                            'qqunionid'	=>	$_SESSION['qq']["unionid"],
                        );

                        $where2['uid']	=	$this->uid;

                        $UserinfoM->upInfo($where2,$data2);

                        $this->actMsg($msgUrl . "/member/index.php?c=binding", "QQ 登录绑定成功！");
                    
                    } else {

                        $GetUrl	=	"https://graph.qq.com/user/get_user_info?oauth_consumer_key=" . $this->config['sy_qqappid'] . "&access_token=" . $_SESSION['qq']['tooken'] . "&openid=" . $_SESSION['qq']['openid'] . "&format=json";
                        
                        $ch		=	curl_init();
                        
                        curl_setopt($ch, CURLOPT_URL, $GetUrl);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	
                        $str	=	curl_exec($ch);
                        curl_close($ch);
                        $user	=	json_decode($str, true);

                        if ($user['nickname']) {
                            
                            $_SESSION['qq']['nickname']	=	$user['nickname'];
                            $_SESSION['qq']['pic']		=	$user['figureurl_qq_2'];
                            
                        } else {
                           
                            $this->actMsg($msgUrl, "用户信息获取失败，请重新登录QQ！");
                            
                        }

                        if ($this->config['reg_real_name_check'] == 1){
                            
                            header("location:" . Url("qqconnect", array("c" => "qqbind")));
                        }else{
                            
                            header("location:" . Url("qqconnect", array("c" => "qqbind","type"=>'ba')));
                        }
                        

                    }
                }
            }
        } else {
            
            echo ("The state does not match. You may be a victim of CSRF.");       
        }    
    }
	function qqbind_action(){
        
	    // if ($this->config['reg_real_name_check'] != 1){
	        
	    //     session_start();
	        
	    //     if($_SESSION['qq']['openid']){
	            
	    //         // 未设置实名注册，QQ未绑定账号的，直接注册账号
	    //         $wdata  =  array(
	    //             'openid'   =>  $_SESSION['qq']['openid'],
	    //             'unionid'  =>  $_SESSION['qq']['unionid'],
	    //             'source'   =>  8
	    //         );
	            
	    //         $userinfoM  =  $this->MODEL('userinfo');
	    //         $result     =  $userinfoM->fastReg($wdata, '', 'qq');
	            
	    //         if ($result['errcode'] == 9){
	                
	    //             header("location:" . Url("register", array("c" => "ident")));
	                
	    //         }else{
	                
	    //             $this->ACT_msg('',"账号注册失败！");
	    //         }
	    //     }
	    // }
        
        $this->yunset('qqlogin');
        $this->yun_tpl(array('index'));
	}
	
	/**
	 * @desc 邮箱认证
	 */
    function cert_action(){
        
    	$id		=	$_GET['id'];    	
    	$arr	=	@explode("|",base64_decode($id));    
    	$arr[0]	=	intval($arr[0]);//uid   	
    	$arr[1]	=	intval($arr[1]);//随机吗     
		$arr[3]	=	trim($arr[3]);//邮件账号
		
		if(!CheckRegEmail($arr[3])){
			
			$this->ACT_msg($_SERVER['HTTP_REFERER'],"非法操作！",8);

		}elseif($id && is_array($arr) && $arr[0] && $arr[2]==$this->config['coding']){
		    
		    $CompanyM	=	$this -> MODEL('company');
		    $UserinfoM	=	$this -> MODEL('userinfo');
		    
		    $user	=	$UserinfoM -> getInfo(array('uid'=> $arr[0]),array('field'=>'usertype'));
		    
		    $data	=	array(
		        'usertype'	=>	$user['usertype'],		        
		        'email'		=>	$arr[3]
		    );
		    
		    $err	=	$CompanyM -> upCertInfo(array('uid' => $arr[0], 'check2' => $arr[1], 'check' => $arr[3]), array('status' => '1'), $data);
		   
		    if ($err['errcode']=='9') {
		        
		        $UA	=	strtoupper($_SERVER['HTTP_USER_AGENT']);
		        
		        if(strpos($UA, 'WINDOWS NT') !== false){
		            
		            header('location:'.$this->config['sy_weburl'].'/index.php?m=register&c=ok&type=4');
		            
		        }else{
		            $this -> ACT_msg(Url('wap'), $err['msg'], 9);
		        }
		    }else {
		        $this->ACT_msg($_SERVER['HTTP_REFERER'], $err['msg'], 8);
		    }
		}else{
			$this->ACT_msg($_SERVER['HTTP_REFERER'],"非法操作！",8);
		}
    }

	function mcert_action(){

        $UserinfoM	=	$this->MODEL('userinfo');

        $id			=	$_GET['id'];
		$arr		=	@explode("|",base64_decode($id));
		$arr[0]		=	intval($arr[0]);

		if($id && is_array($arr) && $arr[0] && $arr[2]==$this->config['coding']){

            $where['uid']	=	$arr[0];

            $resumedata		=	array(
                'email_status'      =>	1
            );
            $memberdata		=	array(
                'email_status'	=>	1
            );
            $userinfoM	    =	$this -> MODEL('userinfo');
            $nid			=	$userinfoM->UpdateUserInfo(array('usertype'=>1,'post'=>$resumedata),$where);
		
            $userinfoM->upInfo($where,$memberdata);
        
            $nid?$this->ACT_msg(Url('login'),"激活成功，请登录！",9):$this->ACT_msg($this->config['sy_weburl'],"激活失败，联系管理员认证！",8);
        
        }else{
            
            $this->ACT_msg($_SERVER['HTTP_REFERER'],"非法操作！",8);
        
        }

    }
}
?>