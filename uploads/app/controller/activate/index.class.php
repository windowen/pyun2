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

		$this->seo("activate");
		
		$this->yun_tpl(array('index'));
	
	}
	function sendstr_action(){

		$NoticeM					    =				$this->MODEL("notice");

		if($this->config['user_status']=="1"){

			$username=$this->stringfilter($_POST['username']);

			if(CheckRegUser($username)==false && CheckRegEmail($username)==false){

				die;

			}

			$where['username']		  		=				$username;

			$info					        =				$UserinfoM->getInfo($where,array('field'=>'`uid`,`email`,`usertype`'));

			if(!empty($info)){
       	
				$randstr			        =				rand(10000000,99999999);
				
				$base				        =				base64_encode($info['uid']."|".$randstr."|".$this->config['coding']);
				
				$url				        =				"<a href='".$this->config['sy_weburl']."/index.php?m=qqconnect&c=mcert&id=".$base."'>点击认证</a>";
				
				$data				        =				array(
					
					'uid'			        =>				$info['uid'],

					'type'			      	=>				'cert',

					'email'			      	=>				$info['email'],

					'url'			        =>				$url,

					'date'			      	=>				date("Y-m-d")

				);
		
				if($this->config['sy_email_set']=="1"){
          			
					$NoticeM->sendEmailType($data);
					  
					echo 1;die;

				}else{

					echo 3;die;

				}

			}else{

				echo 2;die;

			}

		}else{

			echo 0;die;

		}

	}

	function editpw_action(){

		$UserinfoM					  =				$this->MODEL("userinfo");

		$CompanyM					  =				$this->MODEL("company");


		if($_POST['username'] && $_POST['code'] && $_POST['pass']){

			if(!is_numeric($_POST['code']) || CheckRegUser($_POST['username'])==false){

				$this->ACT_msg($this->url("index","forgetpw","1"), $msg = "无效的信息！", $st = 2, $tm = 3);

				exit();

			}

			$password 				= 					$_POST['pass'];

			$certwhere['type']		=					5;

			$certwhere['check2']	=					$_POST['username'];

			$certwhere['check']		=					$_POST['code'];

			$certwhere['orderby']	=					array('id,desc');


			$cert					=					$CompanyM->getCertInfo($certwhere,array('field'=>'`uid`,`check2`,`ctime`'));

			if(!$cert['uid']){
				
				$this->ACT_msg($this->url("index","forgetpw","1"), $msg = "验证码填写错误！", $st = 2, $tm = 3);
				
				exit();
			
			}elseif((time()-$cert['ctime'])>1200){
				
				$this->ACT_msg($this->url("index","forgetpw","1"), $msg = "验证码已失效，请重新获取！", $st = 2, $tm = 3);
				
				exit();
			
			}

			$userhwere['uid']		=				$cert['uid'];

			$info					=				$UserinfoM->getInfo($userhwere,array('field'=>'`email`'));
			
			if(is_array($info)){
				
				$info['username'] 	= 				$cert['check2'];
				
				if($this->config[sy_uc_type]=="uc_center" && $info['name_repeat']!="1"){
					
					$this->obj->uc_open();
					
					uc_user_edit($info[username], "", $password, $info['email'],"0");
				
				}else{
					
					$data					=		array(

						'password'			=>		$password

					);

					$where['uid']			=		$cert['uid'];
				
					$UserinfoM->upInfo($where,$data);	
					
				}
				
				$this->ACT_msg($this->url("index","login","1"), $msg = "密码修改成功！", $st = 1, $tm = 3);
			
			}else{
				
				$this->ACT_msg($this->url("index","forgetpw","1"), $msg = "对不起！没有该用户！", $st = 2, $tm = 3);
			
			}
		
		}else{
			
			$this->ACT_msg($this->url("index","forgetpw","1"), $msg = "请完整填写信息！", $st = 2, $tm = 3);
			
			exit();
		
		}
		
	
	}

}
?>
