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
class integral_controller extends user{
	//获取积分规则
	function index_action(){
		$integralM	=	$this->MODEL('integral');
		$resumeM	=	$this->MODEL('resume');
		
		$statusList	=	$integralM	->	integralMission(array('type'=>'member','uid'=>$this->uid,'usertype'=>$this->usertype));
		
		$expectnum	=	$resumeM->getExpectNum(array('uid'=>$this->uid));
		
		$this->public_action();
		$this->yunset("expectnum",$expectnum);
		$this->yunset("statusList",$statusList);
		$this->user_tpl('integral');
	}
	
}
?>