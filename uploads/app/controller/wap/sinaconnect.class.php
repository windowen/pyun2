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
class sinaconnect_controller extends common
{
	function actMsg($msgUrl,$msg,$status=8){
		
		if($loginType==1){
			$this->ACT_wapMsg($msgUrl, $msg,$status);
		}else{
			$this->ACT_msg_wap($msgUrl, $msg,$status,2,5);
		}
	}
	function getMsgUrl(){
		if(isMobileUser()){//当前为WAP登录
			if($config['sy_wapdomain']!=""){
				if($this->config['sy_wapssl']=='1'){
					$protocol = 'https://';
				}else{
					$protocol = 'http://';
				}
				if(strpos($config['sy_wapdomain'],'http://')===false && strpos($config['sy_wapdomain'],'https://')===false)
				{
					$msgUrl = $protocol.$config['sy_wapdomain'];
				}else{
					$msgUrl = $config['sy_wapdomain'];
				}

			}else{
				$msgUrl = $config['sy_weburl'].'/wap/';
			}
		}else{
			$msgUrl = $config['sy_weburl'];
		}
		return $msgUrl;
	}
	function index_action()
	{    
		$msgUrl = $this->getMsgUrl();
		if($this->config['sy_sinalogin']!="1")
		{
			if((int)$_GET['login']=="1")
			{
				$this->actMsg($msgUrl,"对不起，新浪登录已关闭！");
			}
		}
		include_once( APP_PATH.'api/weibo/saetv2.ex.class.php' );
		define("WB_AKEY" ,$this->config['sy_sinaappid']);
		define("WB_SKEY" , $this->config['sy_sinaappkey']);
		define("WB_CALLBACK_URL" , Url('wap',array('c'=>'sinaconnect')) );
		$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
		if(isset($_GET['code']))
		{
			$keys = array();
			$keys['code'] = $_GET['code'];
			$keys['redirect_uri'] = WB_CALLBACK_URL;
			$token = $o->getAccessToken('code',$keys);
			if($token['access_token'])
			{
				$tokens = 	$token['access_token'];
				$tokenuid =  $token['uid'];
				if($tokenuid>0)
				{
					$UserinfoM	=	$this->MODEL('userinfo');
					
					if($this->uid!=""&&$this->username!="")
					{
						//已登录状态下 重新绑定账户
						$UserinfoM->upInfo(array('sinaid'=>$tokenuid),array('sinaid'=>''));
						$UserinfoM->upInfo(array('uid'=>$this->uid),array('sinaid'=>$tokenuid));
						
						header("location:".Url("wap").'/member/index.php?c=binding');
					}
					$userinfo	=	$UserinfoM->getInfo(array('sinaid'=>$tokenuid));
					
					if(is_array($userinfo))
					{
						
						$UserinfoM->upInfo(array('uid'=>$userinfo['uid']),array('login_date'=>time()));
						
						$logtime=date("Ymd",$userinfo['login_date']);
						$nowtime=date("Ymd",time());
						if($logtime!=$nowtime){
							$this->MODEL('integral')->invtalCheck($userinfo['uid'],$userinfo['usertype'],"integral_login","会员登录",22);
						}
						if($this->config['sy_uc_type']=="uc_center")
						{
						    $this->obj->uc_open();
							$user 		= 	uc_get_user($userinfo['username']);
							$ucsynlogin	=	uc_user_synlogin($user[0]);
							header("location:".Url("wap").'/member/');

						}else{
							$this->cookie->unset_cookie();
							$this->cookie->add_cookie($userinfo['uid'],$userinfo['username'],$userinfo['salt'],$userinfo['email'],$userinfo['password'],$userinfo['usertype'],$this->config['sy_logintime'],$userinfo['did']);
							header("location:".Url("wap").'/member/');
						}
					}else{
						session_start();
						
						$_SESSION['sina']["openid"] 	= $tokenuid;
						$_SESSION['sina']["tooken"] 	= $token['access_token'];
						$_SESSION['sina']["logininfo"] 	= "您已登录新浪微博，请绑定您的帐户！";
						
	
						$GetUrl = "https://api.weibo.com/2/users/show.json?uid=".$tokenuid."&access_token=".$token['access_token'];
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,$GetUrl);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
						$str=curl_exec ($ch);
						curl_close ($ch);
						$user = json_decode($str,true);
												 
						if($user['name']){
							$_SESSION['sina']['nickname'] 	= $user['name'];
							$_SESSION['sina']['pic'] 		= $user['avatar_hd'];
						}else{
							$this->actMsg($msgUrl,"用户信息获取失败，请重新登录新浪微博！");
						}	
						if ($this->config['reg_real_name_check'] != 1){
					        // 未设置实名注册，微信未绑定账号的，直接注册账号
					        
					        header("location:".Url('wap',array('c'=>'sinaconnect','a'=>'sinabind','type'=>'ba')));
					        
					    }else{
					        
					        $urlarr  =  array('c'=>'sinaconnect','a'=>'sinabind');
					        
					        if (isset($_GET['wxoauth'])){
					            
					            $urlarr['wxoauth']  =  1;
					        }
					        header("location:".Url('wap',$urlarr));
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
		session_start();
		if($_POST){
			 
			if($_SESSION['sina']['openid']){
			    
			    $data  =  array(
			        'openid'       =>  $_SESSION['sina']['openid'],
			        'source'       =>  10,
			        'moblie'       =>  $_POST['moblie'],
			        'moblie_code'  =>  $_POST['moblie_code'],
			        'code'         =>  $_POST['authcode'],
			        'port'         =>  2
			    );
			    $userinfoM	=  $this->MODEL('userinfo');
			    $return	    =  $userinfoM->fastReg($data, '', 'sinaweibo');
			    
			    if($return['errcode']==9){
			        
			        $result['url']  =  Url("wap").'/member/';
			    }else{
			        $result['msg']  =  $return['msg'];
			    }
			}else{
			
			    $result['msg'] = '微博登录信息已失效，请重新登录！';
			}
			echo json_encode($result);
		}else{
		    
		    
			$this->yunset("headertitle","微博登录绑定");
			$this->yunset('sinalogin');
			$this->yuntpl(array('wap/sinabind'));
		}	
	}
}

