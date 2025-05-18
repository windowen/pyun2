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
class advice_controller extends common{
	function index_action(){
		$this->seo('advice');
		$this->yunset('headertitle','意见反馈');
		$this->yuntpl(array('wap/advice'));
	}

	function saveadd_action(){
		$data		=	array(
			'username'	=>	$_POST['username'],
			'infotype'	=>	$_POST['infotype'],
			'content'	=>	$_POST['content'],
			'mobile'	=>	$_POST['moblie'],
			'authcode'	=>	$_POST['authcode'],
			'utype'		=>	'wap'
		);
		$adviceM	=	$this->MODEL('advice');
		$return		=	$adviceM->addInfo($data);
		
		echo json_encode($return);die;
	}
}
?>