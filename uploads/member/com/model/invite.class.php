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
class invite_controller extends company{
	function index_action(){
		$uid            =   $this -> uid;
		
		$where['fid']	=	$uid;
		
		if(trim($_GET['keyword'])){
			$resumeM				=  $this -> MODEL('resume');
	            
			$resume					=  $resumeM -> getSimpleList(array('uname'=>array('like',trim($_GET['keyword']))),array('field'=>'uid'));
			
 			if ($resume){
				foreach($resume as $v){
					if($v['uid'] && !in_array($v['uid'],$uid)){
						$uids[]		=  $v['uid'];
					}
				}
				$where['uid']		=  array('in',pylode(',', $uids));
			}
			$urlarr['keyword']		=	trim($_GET['keyword']);
		}
		
		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('member',$urlarr);

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('userid_msg',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];
	        
	        $JobM    =   $this -> MODEL('job');
	        $list    =   $JobM -> getYqmsList($where,array('uid'=>$this->uid,'usertype'=>$this->usertype));
	    }
		$this -> yunset('rows',$list); 
		$this -> yunset('com_look', @explode(',', $this->config['com_look']));

		$this->public_action();
		$this->company_satic();
		$this->com_tpl('invite');
	}
	//获取邀请记录信息
	function ajax_action(){
		if($_POST['id']){
			$JobM				=   $this -> MODEL('job');
	        $row				=   $JobM -> getYqmsInfo(array('id'=>intval($_POST['id']),'fid'=>$this->uid),array('yqh'=>1));
			echo json_encode($row);die;
		}
	}

	function del_action(){
		if($_POST['delid'] || $_GET['id']){
			if ($_GET['id']){
				$id   =  intval($_GET['id']);
			}elseif ($_POST['delid']){
				$id   =  $_POST['delid'];
			}
			$JobM		=  $this->MODEL('job');
			$return		=  $JobM -> delYqms($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));
			$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		}
	}
}
?>