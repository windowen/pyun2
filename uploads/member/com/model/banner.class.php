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
class banner_controller extends company{
	function index_action(){
		$where['uid']	=	$this->uid;
		
		$banner		=	$this -> MODEL('company') -> getBannerInfo('',array('where'=>$where));
		$syspic  	=   checkpic($this->config['sy_banner']);
		$this->yunset("banner",$banner); 
		$this->yunset("syspic",$syspic); 
		$this->public_action();
		$this->com_tpl("banner");
		
	}
	
	function save_action(){
		
		$companyM		=	$this -> MODEL('company');
		
		$data			=	array(

			'file'		=>	$_FILES['file'],

			'uid'		=>	$this->uid,

			'usertype'	=>	$this->usertype

		);
		
		if($_POST['save']){

			$data['type']='add';

		}

		if($_POST['update']){

			$data['type']='update';

		}

		$return			 =	$companyM	->	setBanner($data);

		$this			->	ACT_layer_msg($return['msg'],$return['errcode'],$return['url']);
		
	 
		
	}
}
?>