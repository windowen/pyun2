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
class login_controller extends common{
	function index_action(){
		$this->get_moblie();
		//微信wap登录
		if(preg_match("/^[a-zA-Z0-9_-]+$/",$_GET['wxid'])){
			$wxid = $_GET['wxid'];
			$this->cookie->setcookie("wxid",$_GET['wxid'],time() + 86400);
		}elseif($_COOKIE['wxid']){
			$wxid = $_COOKIE['wxid'];
		}
		if(preg_match("/^[a-zA-Z0-9_-]+$/",$_GET['wxloginid'])){
			$this->cookie->setcookie("wxloginid",$_GET['wxloginid'],time() + 86400);
		}
		if($wxid){
			if($wxid == $_COOKIE['wxid']){
				$this->yunset("wxid",$wxid);
				$this->yunset("wxnickname",$_COOKIE['wxnickname']);
				$this->yunset("wxpic",$_COOKIE['wxpic']);
			}else{
				$wxM = $this->MODEL('weixin');
				$wxinfo = $wxM->getWxUser($wxid);
				if($wxinfo['nickname']){
					$this->yunset("wxid",$wxid);
					$this->cookie->setcookie("wxnickname",$wxinfo['nickname'],time() + 86400);
					$this->yunset("wxnickname",$wxinfo['nickname']);
					$this->cookie->setcookie("wxpic",$wxinfo['headimgurl'],time() + 86400);
					$this->yunset("wxpic",$wxinfo['headimgurl']);
					$this->cookie->setcookie("unionid",$wxinfo['unionid'],time() + 86400);
				}
			}
		}
		if($this->uid || $this->username){
			if((int)$_GET['bind']=='1'){
				$this->cookie->unset_cookie();
				$data['msg']='重新绑定您的求职账户！';
			}elseif($_GET['wxid']){
				$this->cookie->unset_cookie();
			}else{
				$this->wapheader('member/index.php');
			}
		}	
		$checkurl=$_COOKIE['checkurl'];
		unset($checkurl);
       	$this->yunset("checkurl",$checkurl);
		$this->yunset('headertitle',"会员登录");
		$this->seo('login');	
		if(strpos($_SERVER['HTTP_REFERER'],'applyjobuid')){
			if($this->config['sy_seo_rewrite']){
				$endurl=str_replace(array(Url('wap'),'.html'),array('',''),$_SERVER['HTTP_REFERER']);
				$furl=explode('-',$endurl);
				$eurl=explode('_',$furl[2]);
			}else{
				$endurl=str_replace(Url('wap',array('c'=>'job','a'=>'applyjobuid')).'&','',$_SERVER['HTTP_REFERER']);
				$eurl=explode('=',$endurl);
			}
			$this -> yunset('backurl', Url('wap',array('c'=>'job','a'=>'comapply','id'=>$eurl['1'])));
		}elseif(strpos($_SERVER['HTTP_REFERER'],'wxconnect')){
		    $this -> yunset('backurl', Url('wap'));
		}else{
			$this -> yunset('backurl', $_SERVER['HTTP_REFERER']);
		}
		$this->yuntpl(array('wap/login'));
	}
	function mlogin_action(){
	    //普通账户登录提交
	    if($_POST['submit']){
	        $lData['username']		=			$_POST['username'];
	        $lData['uid']			=			$this->uid;
	        $lData['usertype']		=			$this->usertype;
	        $lData['act_login']		=			$_POST['act_login'];
	        $lData['moblie']     	=    	 	$_POST['moblie'];
	        $lData['backurl']		=     	 	$_POST['backurl'];
	        $lData['password']		=			$_POST['act_login'] == 1 ? $_POST['dynamiccode'] : $_POST['password'];
	        $lData['job']			=			$_POST['job'];
	        $lData['checkurl']		=			$_POST['checkurl'];
	        $lData['usource']		=			'wap';
			$lData['authcode']		=			$_POST['authcode'];
	        
	        $UserinfoM				=			$this->MODEL('userinfo');
	        
	        $return					=			$UserinfoM->userLogin($lData);
	        
	        if($return['errcode']==2){
	            $this->layer_msg('',9,0,Url('wap',array('c'=>'register','a'=>'ident')),2);
	        }else if($return['errcode']==9){
	            $this->layer_msg('',9,0,$return['url'],2);
	        }else{
	            $this->layer_msg($return['msg'],9,0,'',2);
	        }
	    }
	}
	//登录短信验证码发送
	function sendmsg_action(){
		$noticeM 		= 		$this->MODEL('notice');
		$result			=		$noticeM->jycheck($_POST['authcode'],'前台登录');
		if(!empty($result)){
			$this->layer_msg($result['msg'], 9, 0, '', 2, $result['error']);
		}
		
		$moblie			=		$_POST['moblie'];
		$UserinfoM		=		$this->MODEL('userinfo');
		$userinfo 		= 		$UserinfoM->getInfo(array("moblie" => $moblie),array('field'=>"`usertype`,`moblie_status`,`uid`"));
		if($userinfo['moblie_status']!='1'){
			$this->layer_msg('手机号码未绑定', 9, 0, '', 2, 108);
		}
		$user 			= 		array(
			'uid'     	=> 		$userinfo['uid'],
			'usertype' 	=> 		$userinfo['usertype']
		);
		
		$result 		=		$noticeM->sendCode($moblie, 'login', 2, $user, 6, 90, 'msg');
		echo json_encode($result);exit();
	}
	function loginlock_action(){
		$this->seo("login"); 
		$this->yuntpl(array('wap/loginlock'));
	}
	function utype_action(){
		if($this->uid){
			header("Location:".$this->config['sy_weburl']."/member");
		}
		$this->seo("login");
		$this->yuntpl(array('wap/utype'));
	}

	function setutype_action(){
		if($_COOKIE['username'] && $_COOKIE['password'] && (CheckRegUser($_COOKIE['username']) OR CheckRegEmail($_COOKIE['username']))){
			$Member			=		$this->MODEL("userinfo");
			$RatingM		=		$this->MODLE('rating');
			$user 			= 		$Member->getInfo(array("username"=>$_COOKIE['username']),array("field"=>"`uid`,`username`,`password`,`salt`,`usertype`,`did`"));
		
			$userid 		= 		$user['uid'];
			if(!$user['usertype']){
				if(passCheck($_COOKIE['password'],$user['salt'],$user['password']) && $user['password']!=''){
					$usertype 			=  (int)$_GET['usertype'];
					if($usertype=='1'){
						$table1 		=  "member_statis";
						$table2			=  "resume";
						$data1			=  array("uid"=>$userid);
						$data2['uid']	=  $userid;
					}elseif($usertype=='2'){
						$table1 		=  "company_statis";
						$table2 		=  "company";
						$ratingM        =  $this->MODEL('rating');
						$data1          =  $ratingM->FetchRatingInfo();
						$data2['uid']	=  $userid;
						$data1['did']	=  $user['did'];
					}
					$Member->upInfo(array("uid"=>$userid),array("usertype"=>$usertype));
					$Member->InsertReg($table1,$data1);
					$Member->InsertReg($table2,$data2);
					
					if(($usertype == '1' && $this->config['user_state']!='1') || ($usertype == '2' && $this->config['com_status']!='1') || ($usertype == '3' && $this->config['lt_status']!='1')){
						$data['msg']='请等待账户审核！';
						$data['url']=Url('wap',array('c'=>'login'));
						$this->yunset("layer",$data);
						$this->yuntpl(array('wap/utype'));
					}else{
						$this->cookie->add_cookie($userid,$user['username'],$user['salt'],$user['email'],$user['password'],$usertype,$this->config['sy_logintime'],$user['did']);
						header("Location:".$this->config['sy_wapdomain'].'/member');
					}
					
				}else{
					$this->cookie->unset_cookie();
					echo "激活失败";
				}
			}else{
				$this->cookie->unset_cookie();
				echo "激活失败";
			}
		}else{
			header("Location:".Url('wap'));
		}
	}
	function baloginsave_action(){

		$userinfoM			=  $this->MODEL('userinfo');
	    if($_POST['provider']=='weixin'){
	    	session_start();
	    	if($_SESSION['wx']['openid']){
            
	            $lData['openid']	=	$_SESSION['wx']['openid'];
	            $lData['unionid']	=	$_SESSION['wx']['unionid'];
				$lData['username']	=	$_POST['username'];
				$lData['uid']		=	$this->uid;
				$lData['usource']	=	'wap';
				$lData['usertype']	=	$this->usertype;
				$lData['password']	=	$_POST['password'];
				$lData['authcode']	=	$_POST['authcode'];
				
	            $result	    		=	$userinfoM->bindacount($lData,'weixin');
	            
	        }else{
	            $result['msg']		=	'微信登录信息已失效，请重新登录！';
	        }
	    }else if($_POST['provider']=='qq'){
	    	session_start();
	    	if($_SESSION['qq']['openid']){
                
			    $lData['openid']	=	$_SESSION['qq']['openid'];
	            $lData['unionid']	=	$_SESSION['qq']['unionid'];
				$lData['username']	=	$_POST['username'];
				$lData['uid']		=	$this->uid;
				$lData['usource']	=	'wap';
				$lData['usertype']	=	$this->usertype;
				$lData['password']	=	$_POST['password'];
				$lData['authcode']	=	$_POST['authcode'];
			    
			    $result	    		=  $userinfoM->bindacount($lData,'qq');
			    
			}else{
			    $result['msg'] 		= 'QQ登录信息已失效，请重新登录！';
			}

	    }else if($_POST['provider']=='sinaweibo'){
	    	session_start();
	    	if($_SESSION['sina']['openid']){
                
			    $lData['openid']	=	$_SESSION['sina']['openid'];
	            $lData['username']	=	$_POST['username'];
				$lData['uid']		=	$this->uid;
				$lData['usource']	=	'wap';
				$lData['usertype']	=	$this->usertype;
				$lData['password']	=	$_POST['password'];
				$lData['authcode']	=	$_POST['authcode'];
			    
			    $result	    		=  $userinfoM->bindacount($lData,'sinaweibo');
			    
			}else{
			    $result['msg'] 		= '微博登录信息已失效，请重新登录！';
			}
	    }
        
	    

        if($result['errcode']==2){
            $this->layer_msg('',9,0,Url('wap',array('c'=>'register','a'=>'ident')),2);
        }else if($result['errcode']==9){
            $this->layer_msg('',9,0,$result['url'],2);
        }else{
            $this->layer_msg($result['msg'],9,0,'',2);
        }
	}
	function balogin_action(){

		if($_POST['provider']=='weixin'){
			session_start();
			if($_SESSION['wx']['openid']){
				    
	            //微信未绑定账号直接注册账号的
		        $wdata  =  array(
		            'openid'   =>  $_SESSION['wx']['openid'],
		            'unionid'  =>  $_SESSION['wx']['unionid'],
		            'source'   =>  4
		        );
		        
		        $userinfoM  =  $this->MODEL('userinfo');
		        $result     =  $userinfoM->fastReg($wdata, '' ,'weixin');
		        
		        if ($result['errcode'] == 9){
		            $res['error'] 	= 1;
					$res['msg'] 	= '注册成功，请选择身份类型';
					$res['url'] 	= Url('wap',array('c'=>'register','a'=>'ident'));
		        }else{
		            $res['error'] 	= 2;
					$res['msg'] 	= '账号注册失败！';
					$res['url'] 	= Url('wap');
		        }
			}else{
				$res['error'] 	= 2;
				$res['msg'] 	= '微信登录信息已失效，请重新登录！';
				$res['url'] 	= Url('wap');
			}
		}else if($_POST['provider']=='qq'){
			session_start();
			if($_SESSION['qq']['openid']){
                
			    $wdata  =  array(
		            'openid'   =>  $_SESSION['qq']['openid'],
		            'unionid'  =>  $_SESSION['qq']['unionid'],
		            'source'   =>  8
		        );
		        
		        $userinfoM  =  $this->MODEL('userinfo');
		        $result     =  $userinfoM->fastReg($wdata, '' ,'qq');
		        if ($result['errcode'] == 9){
		            $res['error'] 	= 1;
					$res['msg'] 	= '注册成功，请选择身份类型';
					$res['url'] 	= Url('wap',array('c'=>'register','a'=>'ident'));
		        }else{
		            $res['error'] 	= 2;
					$res['msg'] 	= '账号注册失败！';
					$res['url'] 	= Url('wap');
		        }
			}else{
			    $res['error'] 	= 2;
				$res['msg'] 	= 'QQ登录信息已失效，请重新登录！';
				$res['url'] 	= Url('wap');
			}

		}else if($_POST['provider']=='sinaweibo'){
			session_start();
			if($_SESSION['sina']['openid']){
                
			    $wdata  =  array(
		            'openid'   =>  $_SESSION['sina']['openid'],
		            'source'   =>  10
		        );
		        
		        $userinfoM  =  $this->MODEL('userinfo');
		        $result     =  $userinfoM->fastReg($wdata, '' ,'sinaweibo');
		        
		        if ($result['errcode'] == 9){
		            $res['error'] 	= 1;
					$res['msg'] 	= '注册成功，请选择身份类型';
					$res['url'] 	= Url('wap',array('c'=>'register','a'=>'ident'));
		        }else{
		            $res['error'] 	= 2;
					$res['msg'] 	= '账号注册失败！';
					$res['url'] 	= Url('wap');
		        }
			}else{
			    $res['error'] 	= 2;
				$res['msg'] 	= '微博登录信息已失效，请重新登录！';
				$res['url'] 	= Url('wap');
			}
		}
		
		echo json_encode($res);
	}
}
?>