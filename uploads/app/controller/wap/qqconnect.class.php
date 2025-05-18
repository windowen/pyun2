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
class qqconnect_controller extends common{
	
	function qqlogin_action(){

		$code 	= 	$_GET['code'];

		if((int)$_GET['login']!="1"){

			if($this->uid!="" && $this->username!="" && empty($code)){

				$this->ACT_msg_wap($this->config['sy_wapdomain'], $msg = "您已经登录了！");
			}
		}
		if($this->config['sy_qqlogin']!="1"){

			if((int)$_GET['login']=="1"){

				$this->ACT_msg_wap($this->config['sy_wapdomain'], $msg = "对不起，QQ绑定已关闭！",2,5);
			}else{

				$this->ACT_msg_wap($this->config['sy_wapdomain'], $msg = "对不起，QQ登录已关闭！",2,5);
			}
		}
		$this->seo('qqlogin');

	    $app_id 	= $this->config['sy_qqappid'];
	    $app_secret = $this->config['sy_qqappkey'];
	    $my_url 	= $this->config['sy_weburl']."/qqlogin.php";
		
		session_start();

	    if(empty($code)){

			 $_SESSION['state']	= md5(uniqid(rand(), TRUE));
			 
			 $dialog_url 		= "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&state=".$_SESSION['state'];
			 
		     echo("<script> top.location.href='".$dialog_url."'</script>");
	    }
	 	if($_GET['state'] == $_SESSION['state']){

			$token_url 		= "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"."client_id=" . $app_id . "&redirect_uri=".urlencode($my_url)."&client_secret=".$app_secret."&code=".$code;
			
			if(!function_exists('curl_init')) {

				echo "请开启CURL函数，否则将无法进行下一步操作！";die;
			}
			$response = CurlGet($token_url);

			if (strpos($response, "callback") !== false){

				$lpos 		= strpos($response, "(");
				$rpos 		= strrpos($response, ")");
				$response  	= substr($response, $lpos + 1, $rpos - $lpos -1);
				$msg 		= json_decode($response);
				
				if (isset($msg->error)){

					echo "<h3>error:</h3>" . $msg->error;
					echo "<h3>msg  :</h3>" . $msg->error_description;
					exit;
				}
			}
			$params 	= array();
			parse_str($response, $params);
			$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$params['access_token'];
			
			//QQ互联平台的移动应用和网站应用打通之后可以获取unionid
			if ($this->config['sy_qqdt']==1){
				$graph_url	.=	"&unionid=1";
			}
			
			$str = CurlGet($graph_url);

			if (strpos($str, "callback") !== false){

				$lpos 	= strpos($str, "(");
				$rpos 	= strrpos($str, ")");
				$str	= substr($str, $lpos + 1, $rpos - $lpos -1);
			}
			$user 		= json_decode($str);
			
			if (isset($user->error))
			{
				echo "<h3>error:</h3>" . $user->error;
				echo "<h3>msg  :</h3>" . $user->error_description;
				exit;
			}

	    	if($user->openid!=""){


				$qqwhere['qqid']  =  $user->openid;
				if($user->unionid){
				    $qqwhere['qqunionid']  =  array('=',$user->unionid,'OR');
				}
				$userinfoM  =  $this->MODEL('userinfo');
				$userinfo	= 	$userinfoM->getInfo($qqwhere);

				if(is_array($userinfo)){

				    $qqdata		=	array(
				        'login_date'	=>	time()
				    );
				    if ($user->unionid) {
				        $qqdata['qqunionid']	=	$user->unionid;
				    }
				    
				    $userinfoM->upInfo(array('uid'=>$userinfo['uid']),$qqdata);
					
					if($this->config['sy_uc_type']=="uc_center"){
					    $this->obj->uc_open();
						$user 		= 	uc_get_user($userinfo['username']);
						$ucsynlogin	=	uc_user_synlogin($user[0]);
						$msg 		=  '登录成功！';
						header("location:".Url("wap").'member/');
					}else{
						$this->cookie->unset_cookie();
						$this->cookie->add_cookie($userinfo['uid'],$userinfo['username'],$userinfo['salt'],$userinfo['email'],$userinfo['password'],$userinfo['usertype'],$this->config['sy_logintime'],$userinfo['did']);
						header("location:".Url("wap").'member/');
					}
				}else{
					$_SESSION['qq']["openid"] 		= $user->openid;
					$_SESSION['qq']["unionid"] 		= $user->unionid;
					$_SESSION['qq']["tooken"] 		= $params['access_token'];
					$_SESSION['qq']["logininfo"]	= "您已登录QQ，请绑定您的帐户！";

					if($this->uid){

						$uwhere['qqid']				=	$_SESSION['qq']["openid"];
						if(!empty($_SESSION['qq']["unionid"])){
						    $uwhere['qqunionid']		=	array('=',$_SESSION['qq']["unionid"],'OR');
						}

						$userinfoM->upInfo($uwhere,array('qqid'=>'','qqunionid'=>''));
						
						$userinfoM->upInfo(array('uid'=>$this->uid),array('qqid'=>$_SESSION['qq']["openid"],'qqunionid'=>$_SESSION['qq']["unionid"]));
						
						header("location:".$this->config['sy_wapdomain'].'/member/index.php?c=binding');

					}else{

						$GetUrl	= 	"https://graph.qq.com/user/get_user_info?oauth_consumer_key=".$this->config['sy_qqappid']."&access_token=".$_SESSION['qq']['tooken']."&openid=".$_SESSION['qq']['openid']."&format=json";

						$ch 	= 	curl_init();
						curl_setopt($ch, CURLOPT_URL,$GetUrl);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
						$str	=	curl_exec ($ch);
						curl_close ($ch);
						$user 	= json_decode($str,true);
						
						if($user['nickname']){
							$_SESSION['qq']['nickname'] = $user['nickname'];
							$_SESSION['qq']['pic'] 		= $user['figureurl_qq_2'];
						}else{
							$this->ACT_msg_wap(Url("wap"),"用户信息获取失败，请重新登录QQ！",2,5);
						}
						if ($this->config['reg_real_name_check'] != 1){
					        // 未设置实名注册，微信未绑定账号的，直接注册账号
					        
					        header("location:".Url('wap',array('c'=>'qqconnect','a'=>'qqbind','type'=>'ba')));
					        
					    }else{
					        
					        $urlarr  =  array('c'=>'qqconnect','a'=>'qqbind');
					        
					        if (isset($_GET['wxoauth'])){
					            
					            $urlarr['wxoauth']  =  1;
					        }
					        header("location:".Url('wap',$urlarr));
					    }
						
					}
				}
			}
	 	}else{
		  echo("The state does not match. You may be a victim of CSRF.");
	  	}
	}
	function qqbind_action(){
		session_start();
		if($_POST){
			if($_SESSION['qq']['openid']){
                
			    $data  =  array(
			        'openid'       =>  $_SESSION['qq']['openid'],
			        'unionid'      =>  $_SESSION['qq']['unionid'],
			        'source'       =>  8,
			        'moblie'       =>  $_POST['moblie'],
			        'moblie_code'  =>  $_POST['moblie_code'],
			        'code'         =>  $_POST['authcode'],
			        'port'         =>  2
			    );
			    $userinfoM	=  $this->MODEL('userinfo');
			    $return	    =  $userinfoM->fastReg($data, '', 'qq');
			    
			    if($return['errcode']==9){
			        
			        $result['url']  =  Url("wap").'/member/';
			    }else{
			        $result['msg']  =  $return['msg'];
			    }
			}else{
			    $result['msg'] = 'QQ登录信息已失效，请重新登录！';
			}
			echo json_encode($result);
		}else{
		    
		    
			$this->yunset("headertitle","QQ登录绑定");
			$this->yunset('qqlogin');
			$this->yuntpl(array('wap/qqbind'));
		}
	}
}
?>