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
		
		if($loginType==1){

			$this->ACT_wapMsg($msgUrl, $msg,$status);
	
		}else{
			
			$this->ACT_msg($msgUrl, $msg,$status);
		}

	}
	function getMsgUrl(){

		if(isMobileUser()){//当前为WAP登录

			if($config['sy_wapdomain']!=""){
			
				if($this->config['sy_wapssl']=='1'){
			
					$protocol 			= 			'https://';
			
				}else{
			
					$protocol 			= 			'http://';
			
				}
				if(strpos($config['sy_wapdomain'],'http://')===false && strpos($config['sy_wapdomain'],'https://')===false){
					
					$msgUrl 			= 			$protocol.$config['sy_wapdomain'];
				
				}else{
					
					$msgUrl 			= 			$config['sy_wapdomain'];
				
				}

			}else{
				
				$msgUrl 				= 			$config['sy_weburl'].'/wap/';
			
			}
		
		}else{

			$msgUrl 					= 			$config['sy_weburl'];
		
		}

		return $msgUrl;

	}

	function index_action(){    

		$msgUrl 					  = 			$this->getMsgUrl();

		$UserinfoM					  =				$this->MODEL("userinfo");
		
		if($this->config['sy_sinalogin']!="1"){
			
			if((int)$_GET['login']=="1"){
				
				$this->actMsg($msgUrl,"对不起，新浪登录已关闭！");
			
			}
		
		}
		
		include_once( APP_PATH.'api/weibo/saetv2.ex.class.php' );
		
		define("WB_AKEY" ,$this->config['sy_sinaappid']);
		
		define("WB_SKEY" , $this->config['sy_sinaappkey']);
		
		define("WB_CALLBACK_URL" , $this->config['sy_weburl'].'/index.php?m=sinaconnect' );
		
		$o 							 	= 				new SaeTOAuthV2( WB_AKEY , WB_SKEY );
		
		if(isset($_GET['code'])){

			$keys 					 	= 				array();

			$keys['code']	 			= 				$_GET['code'];

			$keys['redirect_uri'] 		= 				WB_CALLBACK_URL;

			$token 						= 				$o->getAccessToken('code',$keys);

			if($token['access_token']){

				$tokens 				= 				$token['access_token'];

				$tokenuid 				=  				$token['uid'];

				if($tokenuid>0){

					if($this->uid!=""&&$this->username!=""){
						//已登录状态下 重新绑定账户
						
						$data1						=			array(

							'sinaid'				=>			''

						);

						$where1['sinaid']			=			$tokenuid;

						$UserinfoM->upInfo($where1,$data1);
						$data2						=			array(

							'sinaid'				=>			$tokenuid

						);

						$where2['uid']				=			$this->uid;

						$UserinfoM->upInfo($where2,$data2);
						
						$this->actMsg($msgUrl.'/member/index.php?c=binding',"新浪微博登录绑定成功！",9);
					
					}

					$userwhere['sinaid']			=			$tokenuid;

					$userinfo						=			$UserinfoM->getInfo($userwhere);
					
					if(is_array($userinfo)){

						$uwhere['uid']				=			$userinfo['uid'];

						$udata						=			array(

							'login_date'			=>			time()

						);

						$UserinfoM->upInfo($uwhere,$udata);

						$logtime									=		date("Ymd",$userinfo['login_date']);
						
						$nowtime									=		date("Ymd",time());
						
						if($logtime!=$nowtime){
							
							$this->MODEL('integral')->invtalCheck($userinfo['uid'],$userinfo['usertype'],"integral_login","会员登录",22);
						
						}
						if($this->config['sy_uc_type']=="uc_center"){
							
						    $this->obj->uc_open();
							
							$user 									= 		uc_get_user($userinfo['username']);
							
							$ucsynlogin								=		uc_user_synlogin($user[0]);
							
							$this->actMsg($msgUrl,"登录成功！",9);

						}else{

							$this->cookie->unset_cookie();
							
							$this->cookie->add_cookie($userinfo['uid'],$userinfo['username'],$userinfo['salt'],$userinfo['email'],$userinfo['password'],$userinfo['usertype'],$this->config['sy_logintime'],$userinfo['did']);
							
							$this->actMsg($msgUrl,"登录成功！",9);
						
						}

					}else{

						session_start();
						
						$_SESSION['sina']["openid"] 				= 			$tokenuid;
						
						$_SESSION['sina']["tooken"] 				= 			$token['access_token'];
						
						$_SESSION['sina']["logininfo"] 				= 			"您已登录新浪微博，请绑定您的帐户！";
						
						$GetUrl										= 			"https://api.weibo.com/2/users/show.json?uid=".$tokenuid."&access_token=".$token['access_token'];
						
						$ch 										= 			curl_init();
						
						curl_setopt($ch, CURLOPT_URL,$GetUrl);

						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);

						curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

						$str										=				curl_exec ($ch);

						curl_close ($ch);

						$user 										= 				json_decode($str,true);
						
						if($user['name']){
							
							$_SESSION['sina']['nickname'] 			= 				$user['name'];
							
							$_SESSION['sina']['pic'] 				= 				$user['avatar_hd'];
						
						}else{

							$this->actMsg($msgUrl,"用户信息获取失败，请重新登录新浪微博！");

						}
						if(isMobileUser()){

							header("location:".Url('wap',array("c"=>"sinabind")));

						}else{

							if ($this->config['reg_real_name_check'] == 1){
	                            
	                            header("location:" . Url("sinaconnect", array("c" => "sinabind")));
	                        }else{
	                            
	                            header("location:" . Url("sinaconnect", array("c" => "sinabind","type"=>'ba')));
	                        }

							
						}
						
					}
				}else{

					$this->actMsg($msgUrl,"新浪微博授权失败，请重新授权！");

				}

			}
		}else{

			$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );

			header("location:".$code_url);
			
		}
	}

	function sinabind_action(){

	    
		$this->yunset('sinalogin');
		
		$this->yun_tpl(array('index'));
	}
}

