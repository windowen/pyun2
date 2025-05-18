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
class binding_controller extends user{
	
	function index_action(){
		
		$UserinfoM	=	$this -> MODEL('userinfo');
		
		$member		=	$UserinfoM -> getInfo(array('uid'=> $this->uid));
		
		$this -> yunset('member', $member);
		
		$this -> public_action();
		
		$this -> user_tpl('binding');
	
	}
	
	/**
	 * @desc 手机认证
	 */
	function save_action(){
	    
	    $CookieM   =   $this -> MODEL('cookie');
	    
	    $CookieM   ->  SetCookie('delay', '', time() - 60);
	    
	    $CompanyM  =   $this -> MODEL('company');
	    
	    $data      =   array(
			
			'uid'		=>	$this->uid,
		    
		    'usertype'	=>	$this->usertype,
			
			'moblie'	=>	$_POST['moblie']
		
		);
		
		$nid      =	  $CompanyM -> upCertInfo(array('uid'=>$this->uid,'check2'=>$_POST['code']),array('status'=>'1'), $data); 
		
		echo $nid; die;
	
	}
	
	/**
	 * @desc 个人身份认证
	 */
	function savecert_action(){
		
		$UserinfoM	=	$this -> MODEL('userinfo');
		
		$upResumeData	=	array(
			
			'idcard'	=>	$_POST['idcard'],
			'name'		=>	$_POST['name'],
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
			'file'		=>  $_FILES['file'],
		);
		
		$return		=	$UserinfoM -> upidcardInfo(array('uid'=>$this->uid),$upResumeData);
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	
	}
	
	function del_action(){
		
		$comM     =	$this->MODEL('company');
		
		$return	  =	$comM->delBd($this->uid,array('type'=>$_GET['type'],'usertype'=>$this->usertype));
		
		$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
	}
	
}
?>