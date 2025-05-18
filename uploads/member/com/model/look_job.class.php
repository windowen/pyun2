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
class look_job_controller extends company
{
	function index_action()
	{
		$this->company_satic();
		$this->public_action();
		$JobM					=	$this -> MODEL("job");
		
		$where['com_id']		=  $this -> uid;
		$where['com_status']	=  0;
	    $urlarr['c']			=	$_GET['c'];
		$urlarr['page']			=	'{{page}}';
	    $pageurl				=	Url('member',$urlarr);

	    $pageM					=	$this   ->  MODEL('page');
	    $pages					=	$pageM  ->  pageList('look_job',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']	=	'datetime';
	        $where['limit']		=	$pages['limit'];
	        
	        $List				=  $JobM  ->  getLookJobList($where,array('uid'=>$this->uid,'usertype'=>$this->usertype));
	    }
		
		//邀请面试选择职位
		$this->yqmsInfo();
		$this->yunset("rows",$List);
		$this->com_tpl('look_job');
	}
	function del_action(){
		if($_POST['delid']||$_GET['id']){
			if ($_GET['id']){
				$id   =  intval($_GET['id']);
			}elseif ($_POST['delid']){
				$id   =  $_POST['delid'];
			}
			$jobM    =  $this->MODEL('job');
			$return   =  $jobM -> delLookJob($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));
			$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		}
	}
}
?>