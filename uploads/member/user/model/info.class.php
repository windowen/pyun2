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
class info_controller extends user{
	//基本信息
	function index_action(){
		
		$nametype	=	array('1'=>'完全公开','2'=>'显示编号','3'=>'性别称呼');
		
		$this -> yunset("nametype",$nametype);
		
		$this -> public_action();
		
		$this -> yunset($this->MODEL('cache')->GetCache(array('user','city')));
		
		$this -> user_tpl('info');
	}
	
	function save_action(){
    $resumeM  =  $this -> MODEL('resume'); 
		$mData		=	array(
			
			'moblie'		=>	$_POST['telphone'],
			'email'			=>	$_POST['email']
		);
		
		$rData	=	array(
			'name'			=>	$_POST['name'],
			'nametype'		=>	$_POST['nametype'],
			'sex'			=>	$_POST['sex'],
			'birthday'		=>	$_POST['birthday'],
			'edu'			=>	$_POST['edu'],
			'exp'			=>	$_POST['exp'],
			'telphone'		=>	$_POST['telphone'],
			'living'		=>	$_POST['living'],
			'email'			=>	$_POST['email'],
			'address'		=>	$_POST['address'],
			'height'		=>	$_POST['height'],
			'weight'		=>	$_POST['weight'],
			'nationality'	=>	$_POST['nationality'],
			'marriage'		=>	$_POST['marriage'],
			'domicile'		=>	$_POST['domicile'],
			'qq'			=>	$_POST['qq'],
			'homepage'		=>	$_POST['homepage'],
			'lastupdate'	=>	time()
		);
		
		
		$return	  =  $resumeM -> upResumeInfo(array('uid'=>$this->uid),array('mData'=>$mData,'rData'=>$rData,'utype'=>'user'));
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$return['url'],2,1);
		
	}
	function phototype_action(){
		
		$ResumeM	=	$this -> MODEL('resume');
		
		$ResumeM -> upResumeInfo(array('uid'=>$this->uid),array('rData'=>array('phototype'=>intval($_POST['phototype']))));
		
		$ResumeM -> upInfo(array('uid'=>$this->uid),array('eData'=>array('phototype'=>intval($_POST['phototype']))));
		
		echo $_POST['phototype'];die();
	
	}
}
?>