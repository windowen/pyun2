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
class show_controller extends user{
	function index_action(){
		$resumeM			=	$this->MODEL('resume');
		if($_GET['eid']){
			
		
			$where['uid']	=	$this->uid;
			$where['eid']	=	$_GET['eid'];
			

			$urlarr['c']	=	$_GET['c'];
			$urlarr['eid']	=	$_GET['eid'];
			$urlarr['page']	=	'{{page}}';
			$pageurl		=	Url('member',$urlarr);
			
			//提取分页
			$pageM			=	$this  -> MODEL('page');
			$pages			=	$pageM -> pageList('resume_show',$where,$pageurl,$_GET['page'],'8');
			

			if($pages['total'] > 0){
				
				$where['orderby']	=	'sort,desc';
				
				$where['limit']		=	$pages['limit'];
				
				$List				=	$resumeM->getResumeShowList($where,array('field'=>'`title`,`id`,`picurl`'));
				
				$this->yunset("rows",$List);
			}
			
			$this->public_action();
			$this->user_tpl('show');
		}else{
			header("location:"."index.php?c=resume");
		}
	}
	function del_action(){
		$resumeM	=	$this->MODEL('resume');
		if($_GET['id']){
			$return	=	$resumeM->delShow(intval($_GET['id']),array('uid'=>$this->uid,'usertype'=>$this->usertype));
			
			$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
		}
	}
	function showpic_action(){
		$resumeM	=	$this->MODEL('resume');
		if($_GET['id']){
			
			
			$picurl	=	$resumeM->getResumeShowInfo(array('id'=>intval($_GET['id']),'uid'=>$this->uid),array('field'=>'`eid`,`picurl`,`title`,`sort`'));
			
			$this->yunset("picurl",$picurl);
			
			$this->yunset("uid",$this->uid);
			$this->yunset("id",$_GET['id']);
			$this->yunset("id",$_GET['id']);
			
			$this->public_action();
		    $this->yunset("js_def",2);
			$this->user_tpl('editshow');
		}
	}
	function addshow_action(){
		$this->public_action();
		$this->user_tpl('addshow');
	}
	function upshow_action()
	{
		$resumeM	=	$this->MODEL('resume');
		$rinfo		=	$resumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'r_status'));
		$post		=	array(
			'title'		=>	$_POST['title'],
			'sort'		=>	$_POST['showsort'],
			'status'	=>  $rinfo['r_status']==0?1:$this->config['rshow_photo_status'],
		);
		$data	=	array(
			'post'		=>	$post,
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
			'id'		=>	intval($_POST['id']),
			'file'		=>	$_FILES['file']
		);
		$return		=	$resumeM->addResumeShow($data);
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?c=show&eid=".$_POST['eid']);
    }
	function save_action(){
	    
	    if (!empty($_FILES)){
			$resumeM	=	$this->MODEL('resume');
			$rinfo		=	$resumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'r_status'));
	        $post		=	array(
				'title'	  =>  $this->stringfilter($_FILES['file']['name']),
				'status'  =>  $rinfo['r_status']==0?1:$this->config['rshow_photo_status'],
				'eid'	  =>  intval($_POST['usershowid']),
			);
			$data	=	array(
				'post'		=>	$post,
				'uid'		=>	$this->uid,
				'usertype'	=>	$this->usertype,
				'file'		=>	$_FILES['file']
			);
	        $id 	  =  $resumeM->addResumeShow($data);
			
	        $arr	  =	 array(
	            'jsonrpc'=>'2.0',
	            'id'=>$id
	        );
	        echo json_encode($arr);die;
	    }
	}
}
?>