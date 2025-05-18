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
class setname_controller extends user{
	function index_action(){
		
		$UserinfoM  =   $this -> MODEL('userinfo');
	
		$data       =   array(
		  
			'username'      =>  trim($_POST['username']),
			'password'      =>  trim($_POST['password']),
			'uid'           =>  intval($this->uid),
			'usertype'      =>  intval($this->usertype),
			'restname'      =>  '1'
			
		);
		if (!empty($data['username'])) {
			
			$err	=   $UserinfoM  -> saveUserName($data);
			
			if($err['errcode'] != '1'){
				
				echo $err['msg'];die();
			}else{
				
				echo 1;die();
			}
		}
		
		$this->public_action();
		$this->user_tpl('setname');
	}
}
?>