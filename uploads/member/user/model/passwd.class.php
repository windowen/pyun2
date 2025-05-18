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
class passwd_controller extends user{
	
	function index_action(){
		
		$UserinfoM	=	$this -> MODEL('userinfo');
		
		$member		=	$UserinfoM -> getInfo(array('uid'=> $this->uid),array('setname'=>'1'));
		
		$this -> yunset("member", $member);
		
		//修改密码
		if($_POST['submit']){
			 
			 $data   =   array(
               
			   'uid'          	 =>  intval($this->uid),
               
			   'usertype'     	 =>  $this->usertype,
                
				'oldpassword'  	 =>  trim($_POST['oldpassword']),
   
                'password'     	 =>  trim($_POST['password']),
                
				'repassword'   	 =>  trim($_POST['repassword'])
                
            );
			
			$info	=	$UserinfoM -> getInfo(array('uid'=> $this->uid));
			
			if (is_array($info)) {
				
				if($this->config['sy_uc_type']=="uc_center" &&$info['name_repeat']!="1"){
					
					$this->obj->uc_open();
					
					$ucresult	=	uc_user_edit($info['username'], $_POST['oldpassword'], $_POST['newpassword'], "","1");
					
					if($ucresult == -1){
						
						$this->ACT_layer_msg("原始密码错误！", 8,"index.php?c=passwd");
					
					}
				
				}else{
					
					$err    =   $UserinfoM -> savePassword($data);
				
				}
				
				
				
				if($err['errcode'] == '8'){ 
                    
					$this -> ACT_layer_msg($err['msg'], $err['errcode'], "index.php?c=passwd");
                
				}else{
                   $this -> cookie -> unset_cookie();
				   $this -> ACT_layer_msg($err['msg'], $err['errcode'], $this->config['sy_weburl'] . "/index.php?m=login");
                
				}
			
			}
		
		}
		
		//修改用户名
		if($_POST['submit2']){
			
			$data	=	array(
				
				'username'	=>  trim($_POST['username']),
				
				'password'	=>  trim($_POST['password']),
				
				'uid'		=>  intval($this->uid),
				
				'usertype'	=>  intval($this->usertype),
				
				'restname'	=>  '1'
            
			);
			if (!empty($data['username'])) {
				
				$err	=	$UserinfoM  -> saveUserName($data);
				
				if($err['errcode'] == '1'){
					
					$this->ACT_layer_msg("修改成功，请重新登录！", 9 ,$this->config['sy_weburl']."/index.php?m=login");
					
				
				}else{
					
					$this->ACT_layer_msg("修改失败！", 8 ,$_SERVER['HTTP_REFERER']);
				
				}
			
			}
		
		}
		
		$this->public_action();
		
		$this->user_tpl('passwd');
	
	}

}
?>