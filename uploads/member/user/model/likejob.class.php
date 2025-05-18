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
class likejob_controller extends user{
	//相似职位
	function index_action(){
		$this->public_action();
    $resumeM	=	$this->MODEL('resume');
		$data	=	array(
			'id'=>(int)$_GET['id'],
			'uid'=>$this->uid,
		);
	
		$list		=	$resumeM->likeJob($data);

		$this->yunset("job",$list);
		$this->user_tpl('likejob');
	}
}
?>