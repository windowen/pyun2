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
		
		

		if($this->config['reg_user_stop']!=1){//关闭会员注册
			
			$this->yun_tpl(array('stopreg'));
			
		}else{
			if($this->uid){
				$this->ACT_msg($this->config['sy_weburl'], "您已经登录了！");
			}
			$type 	= 	$_GET['type'];
			if($type){
				switch($type){
					case 1:
						if($this->config['reg_user'] != 1){
							$this->ACT_msg("index.php","用户名注册已关闭！");
						}
						break;
					case 2:
						if($this->config['reg_moblie'] != 1){
							$this->ACT_msg("index.php","手机号注册已关闭！");
						}
						break;
					case 3:
						if($this->config['reg_email'] != 1){
							$this->ACT_msg("index.php","email注册已关闭！");
						}
					default:
						break;
				}
			}else{
				if($this->config['reg_moblie']){
				
					$type = '2';
				}else if($this->config['reg_email']){
					
					$type = '3';
				}else{
					
					$type = '1';
				}
			
			}

			$this->yunset('type', $type);
			
			if((int)$_GET['uid']){//邀请注册生成
			
				$this->cookie->setcookie('regcode',(int)$_GET['uid'],time()+3600);
			}

			$this->seo("register");
			
			
			$this->yun_tpl(array('index'));
			
		}
	}
	function ok_action(){
		if($this->uid){
			header("location:".$this->config['sy_weburl'].'/member');
		}
		if((int)$_GET['type']==1){
			
			$seo=$this->config['sy_webname']."- 注册成功";
			
		}elseif((int)$_GET['type']==2){
			
			$seo=$this->config['sy_webname']."- 帐号被锁定";
			
		}elseif((int)$_GET['type']==3){
			
			$seo=$this->config['sy_webname']."- 审核未通过";
			
		}elseif((int)$_GET['type']==4){
			
			$seo=$this->config['sy_webname']."- 邮件认证成功";
			
		}elseif((int)$_GET['type']==5){
			
			$seo=$this->config['sy_webname']."- 未审核";
			
		}elseif((int)$_GET['type']==6){
			
			$seo=$this->config['sy_webname']."- 订阅";
			
		}else{
			header("location:".$this->config['sy_weburl']);
		}
		$this->seo('register');
		$this->yun_tpl(array('ok'));
	}
	function ajaxreg_action(){
		//验证用户名、真实姓名、邮箱
		$post	=	array();
		if(!empty($_POST['username'])){
			$post['username']	=	$_POST['username'];
		}
		if(!empty($_POST['email'])){
			$post['email']	=	$_POST['email'];
		}
		if(!empty($_POST['realname'])){
			$post['realname']	=	$_POST['realname'];
		}
		if(!empty($_POST['password'])){
			$post['password']	=	$_POST['password'];
		}
 		$data		=	array('post'	=>	$post);
		$registerM	=	$this->MODEL("register");
		$return 	= 	$registerM->ajaxReg($data);
		
		//echo $return['errcode'];
		echo json_encode($return);
	}
	
	function regmoblie_action(){
		$data	=	array(
			'moblie'	=>	$_POST['moblie'],
		);
		$registerM	=	$this->MODEL("register");
		$return 	= 	$registerM->regMoblie($data);
		if($return['errcode']==9){
			echo json_encode($return['data']);
		}else{
			echo $return['errcode'];
		}
	}
	
	function regemail_action(){
		$data	=	array(
			'email'	=>	$_POST['email'],
		);
		$registerM	=	$this->MODEL("register");
		$return 	= 	$registerM->regMoblie($data);
		if($return['errcode']==9){
			echo json_encode($return['data']);
		}else{
			echo $return['errcode'];
		}
	}
	
	
	function writtenOff_action(){
		
		$data	=	array(
			'code'		=>	$_POST['code'],
			'zyuid'		=>	$_POST['zyuid'],
			'pw'		=>	$_POST['pw'],
			'mobile'	=>	$_POST['mobile'],
			'email'		=>	$_POST['email'],
			
		);
		$registerM	=	$this->MODEL("register");
		$return 	= 	$registerM->writtenOff($data);
		
		echo $return['errcode'];
	}

	function regsave_action(){
		$Member	=	$this->MODEL('userinfo');
			
		$data['uid']			=	$this->uid;
		$data['username']		=	$_POST['username'];
		$data['codeid']			=	$_POST['codeid'];
		$data['moblie']			=	$_POST['moblie'];
		$data['moblie_code']	=	$_POST['moblie_code'];
		$data['code']			=	$_POST['authcode'];
		$data['name']			=	$_POST['name'];
		$data['email']			=	$_POST['email'];
		$data['password']		=	$_POST['password'];
		$data['source']			=	1;
		$data['did']			=	$this->config['did'];
		$data['port']			=	1;
		
		
		$return	=	$Member->userRegSave($data);
		if($return['errcode']!=8){
			$arr['status']	=	$return['errcode'];
			$arr['msg']		=	$return['msg'];
			$arr['url']		=	Url('register',array('c' => 'ident'));
		}else{
			$arr['msg']		=	$return['msg'];
			$arr['status']	=	8;
		}
		echo json_encode($arr);die;
	}


	function ident_action(){
		
		if(!$this->uid){
		
			header('Location: '.Url('register'));
			exit();

		}elseif($this->usertype){
			
			header('Location: '.Url('member'));
			exit();
		}
		
		if($_GET['usertype']){
			
			$Member	=	$this->MODEL('userinfo');

			$info	=	$Member->upUserType(array('uid'=>$this->uid, 'usertype'=>$_GET['usertype']));
			
			if($info['errcode'] == 1){
				
				header('Location: '.$info['url']);
				exit();

			}else{

				$this->ACT_msg(Url('register',array('c' => 'ident' )), $info['msg']);
			
			}
		
		}
		$this->seo("register");
		$this->yun_tpl(array('ident'));
	}



}
?>