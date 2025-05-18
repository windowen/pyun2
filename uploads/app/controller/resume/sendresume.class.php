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
class sendresume_controller extends resume_controller{
	/**
	 * 发送简历
	 * 2019-06-15
	 */
	function index_action(){
		$id					=	intval($_GET['id']);
		if(!empty($id)){
			$resumeM		=	$this -> MODEL('resume');
			$user			=	$resumeM -> getInfoByEid(array(
				'eid' 		=>	$id,
				'uid' 		=>	$this -> uid,
				'usertype' 	=>	$this -> usertype
			));

			$JobM			=	$this -> MODEL('job');
			
			$time			=	strtotime("-14 day");
			$allnum			=	$JobM -> getYqmsNum(array(
				'uid' 		=>	$user['uid'],
				'eid' 		=>	$id,
				'datetime' 	=>	array('>', $time)
			));
			$replynum		=	$JobM -> getYqmsNum(array(
				'uid' 		=>	$user['uid'],
				'eid' 		=>	$id,
				'datetime' 	=>	array('>', $time),
				'is_browse' =>	array('>', 1)
			));

			$pre			=	round(($replynum/$allnum)*100);
			
			$this -> yunset('pre', $pre);
			$this -> yunset('Info', $user);
			
			$resumeCheck	=	$this->config['resume_open_check'] == '1' ? '1' : '2';
			$this->yunset('resumeCkeck',  $resumeCheck);
		}

		$this->yuntpl(array('resume/sendresume'));
    }
}
?>