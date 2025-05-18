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
class transfer_controller extends user{
	//基本信息
	function index_action(){
		
		$this->public_action();
		$this->user_tpl('transfer');
	}
	
	function save_action(){
		
		
		if($_POST['submit']){
			
			$transferM	=	$this -> MODEL('transfer');

			$return		=	$transferM -> setTransfer($this->uid,$_POST);
			
			if($return['errcode'] == '1'){
				$this->cookie->unset_cookie();
				$this->ACT_layer_msg('账户分离成功，请使用新账户登录！',9,Url('login'));

			}else{
				$this->ACT_layer_msg($return['msg'],8);
			}

		}
		
	}
	
}
?>