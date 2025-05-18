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
class index_controller extends common{
	function index_action(){
		if($this->uid!=""&&$this->username!=""){
			if($_GET['type']=="out"){       
				
				$this->cookie->unset_cookie();
				
			}else{
				$this->ACT_msg($this->config['sy_weburl']."/member", "您已经登录了！");
			}
		}
		if($_GET['backurl']=='1'){
			$this->cookie->setCookie("backurl",$_SERVER['HTTP_REFERER'],time()+60);
		}
		if(!$_GET['usertype']){
			$_GET['usertype']		=		1;
		}
		$this->yunset("cookie", $_COOKIE['checkurl']);				
		$this->yunset("usertype",(int)$_GET['usertype']);
		$this->yunset("loginname",$_COOKIE['loginname']);
        $this->yunset("referurl",$_SERVER['HTTP_REFERER']);
		$this->seo("login");
		$this->yun_tpl(array('index'));
	}
	//登录验证
	function loginsave_action(){
		
	
		$Member					=			$this->MODEL('userinfo');

		$lData['username']		=			$_POST['username'];
		$lData['uid']			=			$this->uid;
		$lData['usertype']		=			$this->usertype;
		$lData['act_login']		=			$_POST['act_login'];
		$lData['num']			=			$_POST['num'];
		$lData['loginname']		=			$_POST['loginname'];
		$lData['password']		=			$_POST['password'];
		$lData['referurl']		=			$_POST['referurl'];
		$lData['authcode']		=			$_POST['authcode'];

		$return					=			$Member->userLogin($lData);
		
		if($return['uclogin']){
			$error	=	2;
			$return['msg'] = $return['uclogin'];
		}else{
			$error	=	1;
		}
		if($return['errcode']==2){
			$this->layer_msg($return['msg'],9,0,Url('register',array('c'=>'ident')),2,$error);
		}else if($return['url']){
			$this->layer_msg($return['msg'],9,0,$return['url'],2,$error);
		}else{
			$this->layer_msg($return['msg']);	
		}
		
	}
	//登录短信验证码发送
	function sendmsg_action()
	{
		$noticeM	=	$this->MODEL('notice');
		$result		=	$noticeM->jycheck($_POST['code'],'前台登录');
		if(!empty($result)){
			$this->layer_msg($result['msg'], 9, 0, '', 2, $result['error']);
		}
		$moblie		=	$_POST['moblie'];
		$UserinfoM	=	$this->MODEL('userinfo');
		$userinfo	=	$UserinfoM->getInfo(array("moblie" => $moblie),array('field'=>"`usertype`,`moblie_status`,`uid`"));
		if($userinfo['moblie_status']!='1'){
			$this->layer_msg('手机号码未绑定', 9, 0, '', 2, 108);
		}
		$user = array(
			'uid'		=> 		$userinfo['uid'],
			'usertype' 	=> 		$userinfo['usertype']
		);
		
		$result		=	$noticeM->sendCode($moblie, 'login', 1, $user, 6, 90, 'msg');
		echo json_encode($result);exit();
	}
	function rest_action(){
		$this->cookie->unset_cookie();
		$url = Url("login",array("usertype"=>"1"),"1");
		header("Location: ".$url);
	}
	function utype_action(){
		if($this->uid){
			header("Location:".$this->config['sy_weburl']."/member");
		}
		$this->seo("login");
		$this->yun_tpl(array('utype'));
	}

	function setutype_action(){   
		//验证前期保存的登录数据，是否在本系统有存在并且密码对应
		if($_COOKIE['username'] && $_COOKIE['password'] && (CheckRegUser($_COOKIE['username']) OR CheckRegEmail($_COOKIE['username'])==false)){
			//无usertype情况下 才予以激活 否则直接登录会员中心
			$Member=$this->MODEL("userinfo");
			$user = $Member->getInfo(array("username"=>$_COOKIE['username']),array("field"=>"`uid`,`username`,`password`,`salt`,`usertype,did`"));
			
			$userid = $user['uid'];
			if(!$user['usertype']){
				if(passCheck($_COOKIE['password'],$user['salt'],$user['password']) && $user['password']!=''){
					$usertype = (int)$_GET['usertype'];
					if($usertype=='1'){
						$table = "member_statis";
						$table2 = "resume";
						$data1=array("uid"=>$userid);
						$data2['uid']=$userid;
					}elseif($usertype=='2'){
					    
						$table         =  "company_statis";
						$table2        =  "company";
						$ratingM       =  $this->MODEL('rating');
						$data1         =  $ratingM->FetchRatingInfo();
						$data2['uid']  =  $userid;
						$data1['did']  =  $user['did'];
						
					}
					$Member->upInfo(array(array("uid"=>$userid),"usertype"=>$usertype));
					$Member->InsertReg($table,$data1);
					$Member->InsertReg($table2,$data2);
					$this->cookie->add_cookie($userid,$user['username'],$user['salt'],$user['email'],$user['password'],$usertype,$this->config['sy_logintime'],$user['did']);
					header("Location:".$this->config['sy_weburl'].'/member');
				}else{
					$this->cookie->unset_cookie();
					echo "激活失败";
				}
			}else{
				$this->cookie->unset_cookie();
				echo "激活失败";
			}
		}else{
			header("Location:".Url('index'));
		}
	}
	//微信登录
	function wxlogin_action(){
		$WxM=$this->MODEL('weixin');
		$qrcode = $WxM->applyWxQrcode($_COOKIE['wxloginid']);
		if(!$qrcode){
			echo 0;
		}else{
			echo $qrcode;
		}
	}
	
	function getwxloginstatus_action(){
		if($_COOKIE['wxloginid']){
			$WxM				=			$this->MODEL('weixin');
			$result 			= 			$WxM->getWxLoginStatus($_COOKIE['wxloginid'], $this->uid);
			if($result['status'] == 1){
			    
			    if (!empty($result['member'])){
			        
			        $user  =  $result['member'];
			        
			        if ($user['usertype'] == 0){
			            
			            $this->layer_msg('扫码成功',9,0, Url('register',array('c'=>'ident')));
			            
			        }else{
			            
			            if($user['wxbindtime']=="" || $user['wxbindtime']==0){
			                $where['uid']			=			$user['uid'];
			                
			                $data					=			array(
			                    
			                    'wxbindtime'		=>			time()
			                );
			                $UserinfoM  =  $this->MODEL('userinfo');
			                $UserinfoM->upInfo($where,$data);
			            }
			            if($user['status']=="2"){
			                $this->layer_msg('您的账号已被锁定',9,0,Url("register",array("c"=>"ok","type"=>2),"1"),2);
			            }
			            if($user['usertype']=="1" && $this->config['user_status']!="1" && $user['status']!="1"){
			                $this->layer_msg('您还没有通过审核!',9,0,Url("register",array("c"=>"ok","type"=>3),"1"),2,1);
			            }
			            if($user['usertype']=="2" && $this->config['com_status']!="1" && $user['status']!="1"){
			                $this->layer_msg('您还没有通过审核',9,0,Url("register",array("c"=>"ok","type"=>3),"1"),2,1);
			            }
			            if($user['usertype']=="3" && $this->config['lt_status']!="1" && $user['status']!="1"){
			                $this->layer_msg('您还没有通过审核!',9,0,Url("register",array("c"=>"ok","type"=>3),"1"),2,1);
			            }
			            if($this->config['user_status']=="1" && $user['usertype']=="1"&&$user['email_status']!="1"){
			                $this->layer_msg('您的账户还未激活，请先激活！',9,0,Url("activate",'',"1"),2);
			            }
			            $this->cookie->add_cookie($user['uid'],$user['username'],$user['salt'],$user['email'],$user['password'],$user['usertype'],$this->config['sy_logintime'],$user['did']);
			            $logtime		=		date("Ymd",$user['login_date']);
			            $nowtime		=		date("Ymd",time());
			            if($logtime!=$nowtime){
			                $this->MODEL('integral')->invtalCheck($user['uid'],$user['usertype'],"integral_login","会员登录",22);
			            }
			            $this->layer_msg('',9,0,Url("member"));
			        }
			    }else{
			        
			        if ($this->config['reg_real_name_check'] == 1){
			            
			            $this->layer_msg('扫码成功,请绑定手机号',9,0, Url('wxconnect',array('bind'=>1)));
			            
			        }else{
			            
			            $this->layer_msg('扫码成功,请绑定已有账号或直接创建新账号',9,0, Url('wxconnect',array('bind'=>1,'type'=>'ba')));
			        }
			    }
				
			}else{
			    
			    $this->layer_msg('');
			}
		}else{
		    $this->layer_msg('');
		}
	}
	function baloginsave_action(){

		$userinfoM	=  $this->MODEL('userinfo');

		if ($_POST['provider'] == 'weixin'){
	        
	        if(!empty($_COOKIE['wxloginid'])){
	            
	            $weixinM  =  $this->MODEL('weixin');
	            $wxqrcode  =  $weixinM->getWxQrcode(array('wxloginid' => $_COOKIE['wxloginid'], 'status' => 1));
	            
	            if($wxqrcode['wxid'] || $wxqrcode['unionid']){
	                
	                

	                $lData['openid']	=	$wxqrcode['wxid'];
	                $lData['unionid']	=	$wxqrcode['unionid'];
					$lData['username']	=	$_POST['username'];
					$lData['uid']		=	$this->uid;
					$lData['usertype']	=	$this->usertype;
					$lData['password']	=	$_POST['password'];
					$lData['authcode']	=	$_POST['authcode'];
					
	                $return	    =  $userinfoM->bindacount($lData,'weixin');
	            }
	        }else{
	            $return['msg']		=	'微信登录信息已失效，请重新登录！';
	        }
	    }else if($_POST['provider'] == 'qq'){

	    	session_start();
	        
	        if($_SESSION['qq']['openid']){
	            
	            $lData  =  array(
	                'openid'   	=>  $_SESSION['qq']['openid'],
	                'unionid'  	=>  $_SESSION['qq']['unionid'],
	                'authcode' 	=>  $_POST['authcode'],
	                'username' 	=>  $_POST['username'],
	                'password' 	=>  $_POST['password'],
	                'uid'		=>	$this->uid,
	                'usertype'	=>	$this->usertype
	            );
	            
	            $return	    =  $userinfoM->bindacount($lData,'qq');
	        }else{
	            $return['msg']		=	'qq登录信息已失效，请重新登录！';
	        }
	    }else if ($_POST['provider'] == 'sinaweibo'){
	        
	        session_start();
	        
	        if($_SESSION['sina']['openid']){
	            
	            $lData  =  array(
	                'openid'       =>  $_SESSION['sina']['openid'],
	                'authcode' 	=>  $_POST['authcode'],
	                'username' 	=>  $_POST['username'],
	                'password' 	=>  $_POST['password'],
	                'uid'		=>	$this->uid,
	                'usertype'	=>	$this->usertype
	            );

	            $return	    =  $userinfoM->bindacount($lData,'sinaweibo');

	        }else{
	            $return['msg']		=	'新浪微博登录信息已失效，请重新登录！';
	        }
	    }

	    if($return['errcode']==2){
			$this->layer_msg($return['msg'],9,0,Url('register',array('c'=>'ident')),2,1);
		}else if($return['url']){
			$this->layer_msg($return['msg'],9,0,$return['url'],2,1);
		}else{
			$this->layer_msg($return['msg']);	
		}
	}
	function balogin_action(){
		
		if ($_POST['provider'] == 'weixin'){
			if($_COOKIE['wxloginid']){
				$WxM				=			$this->MODEL('weixin');
				$userinfoM			=			$this->MODEL('userinfo');
				$result 			= 			$WxM->creatacount($_COOKIE['wxloginid'], $this->uid);
				
				if($result['status']==1){

					if (!empty($result['member'])){

						$user  =  $result['member'];

						if ($user['usertype'] == 0){
					            
				            $this->layer_msg('创建成功',9,0, Url('register',array('c'=>'ident')));
				            
				        }else{

				        	$this->cookie->add_cookie($user['uid'],$user['username'],$user['salt'],$user['email'],$user['password'],$user['usertype'],$this->config['sy_logintime'],$user['did']);
				            $logtime		=		date("Ymd",$user['login_date']);
				            $nowtime		=		date("Ymd",time());
				            if($logtime!=$nowtime){
				                $this->MODEL('integral')->invtalCheck($user['uid'],$user['usertype'],"integral_login","会员登录",22);
				            }
				            $this->layer_msg('',9,0,Url("member"));
				        }

				    }else{
				    	$this->layer_msg('生成账号失败，请重新扫描微信二维码',9,0, Url('wxconnect'));
				    }
				}else{
					$this->layer_msg('请扫描微信二维码',9,0, Url('wxconnect'));
				}

			}else{
			    $this->layer_msg('请扫描微信二维码',9,0, Url('wxconnect'));
			}
		}else if($_POST['provider'] == 'qq'){
			session_start();
	        
	        if($_SESSION['qq']['openid']){
	            
	            // 未设置实名注册，QQ未绑定账号的，直接注册账号
	            $wdata  =  array(
	                'openid'   =>  $_SESSION['qq']['openid'],
	                'unionid'  =>  $_SESSION['qq']['unionid'],
	                'source'   =>  8
	            );
	            
	            $userinfoM  =  $this->MODEL('userinfo');
	            $result     =  $userinfoM->fastReg($wdata, '', 'qq');
	            
	            if ($result['errcode'] == 9){
	                
	                $this->layer_msg('创建成功',9,0, Url('register',array('c'=>'ident')));
	                
	            }else{
	                $this->layer_msg('账号注册失败',8,0);
	            }
	        }else{
	            $this->layer_msg('qq登录信息已失效，请重新登录！',8,0);
	        }
		}else if($_POST['provider'] == 'sinaweibo'){
			session_start();
	        
	        if($_SESSION['sina']['openid']){
	            
	            // 未设置实名注册，微博未绑定账号的，直接注册账号
	            $wdata  =  array(
	                'openid'   =>  $_SESSION['sina']['openid'],
	                'source'   =>  10
	            );
	            
	            $userinfoM  =  $this->MODEL('userinfo');
	            $result     =  $userinfoM->fastReg($wdata, '', 'sinaweibo');
	            
	            if ($result['errcode'] == 9){
	                
	                $this->layer_msg('创建成功',9,0, Url('register',array('c'=>'ident')));
	                
	            }else{
	                
	                $this->layer_msg('账号注册失败',8,0);
	            }
	        }else{
	            $this->layer_msg('新浪微博登录信息已失效，请重新登录！',8,0);
	        }
		}
	}
	/**
	 * 微信扫码后，后台设置实名验证，需绑定手机号后再自动注册账号
	 */
	function fastregsave_action(){
	    
	    if ($_POST['provider'] == 'weixin'){
	        
	        if(!empty($_COOKIE['wxloginid'])){
	            
	            $weixinM  =  $this->MODEL('weixin');
	            $wxqrcode  =  $weixinM->getWxQrcode(array('wxloginid' => $_COOKIE['wxloginid'], 'status' => 1));
	            
	            if($wxqrcode['wxid'] || $wxqrcode['unionid']){
	                
	                $data  =  array(
	                    'openid'       =>  $wxqrcode['wxid'],
	                    'unionid'      =>  $wxqrcode['unionid'],
	                    'source'       =>  9,
	                    'moblie'       =>  $_POST['moblie'],
	                    'moblie_code'  =>  $_POST['moblie_code'],
	                    'code'         =>  $_POST['authcode'],
	                    'port'         =>  1
	                );
	                $userinfoM	=  $this->MODEL('userinfo');
	                $return	    =  $userinfoM->fastReg($data, '', 'weixin');
	            }
	        }else{
	            $return['msg']		=	'微信登录信息已失效，请重新登录！';
	        }
	    }
	    if ($_POST['provider'] == 'qq'){
	        
	        session_start();
	        
	        if($_SESSION['qq']['openid']){
	            
	            $data  =  array(
	                'openid'       =>  $_SESSION['qq']['openid'],
	                'unionid'      =>  $_SESSION['qq']['unionid'],
	                'source'       =>  8,
	                'moblie'       =>  $_POST['moblie'],
	                'moblie_code'  =>  $_POST['moblie_code'],
	                'code'         =>  $_POST['authcode'],
	                'port'         =>  1
	            );
	            $userinfoM	=  $this->MODEL('userinfo');
	            $return	    =  $userinfoM->fastReg($data, '', 'qq');
	        }else{
	            $return['msg']		=	'QQ登录信息已失效，请重新登录！';      
	        }
	    }
	    if ($_POST['provider'] == 'sinaweibo'){
	        
	        session_start();
	        
	        if($_SESSION['sina']['openid']){
	            
	            $data  =  array(
	                'openid'       =>  $_SESSION['sina']['openid'],
	                'source'       =>  10,
	                'moblie'       =>  $_POST['moblie'],
	                'moblie_code'  =>  $_POST['moblie_code'],
	                'code'         =>  $_POST['authcode'],
	                'port'         =>  1
	            );
	            $userinfoM	=  $this->MODEL('userinfo');
	            $return	    =  $userinfoM->fastReg($data, '', 'sinaweibo');
	        }else{
	            $return['msg']		=	'新浪微博登录信息已失效，请重新登录！';
	        }
	    }
        if($return['errcode']==9){
            $arr['status']	=	9;
            $arr['msg']		=	$return['msg'];
            $arr['url']		=	Url('register',array('c' => 'ident'));
        }else{
            $arr['msg']		=	$return['msg'];
            $arr['status']	=	8;
        }
        echo json_encode($arr);die;
	}
}
