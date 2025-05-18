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
class look_resume_controller extends company{
	function index_action(){
		$this->company_satic();
		$this->public_action();
		$where['com_id']				=  $this -> uid;
		$where['usertype']              =  $this -> usertype;
		$where['com_status']			=  0;
		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('member',$urlarr);

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('look_resume',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']		=	'datetime';
	        $where['limit']			=	$pages['limit'];
	        
	        $lookresumeM  =  $this -> MODEL('lookresume');
	        
	        $List   =  $lookresumeM  ->  getList($where,array('uid'=>$this->uid));
	    }
		
		//邀请面试选择职位
		$this->yqmsInfo();
		$this->yunset('rows',$List['list']);
		$this->com_tpl('look_resume');
	}
	function del_action(){
		if($_POST['delid']||$_GET['id']){
			if ($_GET['id']){
				$id   =  intval($_GET['id']);
			}elseif ($_POST['delid']){
				$id   =  $_POST['delid'];
			}
			$lookresumeM    =  $this->MODEL('lookresume');
			$return   =  $lookresumeM -> delInfo(array('id'=>$id,'uid'=>$this->uid,'usertype'=>2));
			
			$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		}
	}
}
?>