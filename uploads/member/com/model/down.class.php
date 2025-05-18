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
class down_controller extends company{
	function index_action(){
		
	    $where              =   array();
	    
	    $uid				=   $this->uid;
		
	    $where['comid']		=   $uid;
        
		$where['usertype']	=   $this->usertype;
        
		if(trim($_GET['keyword'])){
			$resumeM				=  $this -> MODEL('resume');
	            
			$resume					=  $resumeM -> getSimpleList(array('uname'=>array('like',trim($_GET['keyword']))),array('field'=>'uid'));
			
			if ($resume){
			    $uids  =  array();
				foreach($resume as $v){
					if($v['uid'] && !in_array($v['uid'],$uids)){
						$uids[]		=  $v['uid'];
					}
				}
				$where['uid']		=  array('in',pylode(',', $uids));
			}
			$urlarr['keyword']		=	trim($_GET['keyword']);
		}
		
		$this -> public_action();
		
	    $urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('member',$urlarr);

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('down_resume',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];
	        
	        $downM  =  $this -> MODEL('downresume');
	        
	        $List   =  $downM  ->  getList($where,array('uid'=>$this->uid));
	    }
		$CacheM     =   $this -> MODEL('cache');
        $cache      =   $CacheM -> GetCache(array('com'));
        $this->yunset($cache);
		//邀请面试选择职位
		$this -> yqmsInfo();
		$this -> company_satic();
		$this -> yunset("rows",$List['list']); 
		$this -> yunset("total",$pages['total']); 
		$this -> com_tpl('down');
	}
	function del_action(){
		if ($_GET['id']){
	        $id   =  intval($_GET['id']);
	    }elseif ($_POST['delid']){
	        $id   =  $_POST['delid'];
	    }
	    $downM    =  $this->MODEL('downresume');
	    $return   =  $downM -> delInfo($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));
	    $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	function report_action(){
		$data		=	array(
			'reason'	=>	$_POST['reason'],
			'c_uid'		=>	(int)$_POST['r_uid'],    
			'inputtime'	=>	time(),
			'p_uid'		=>	$this -> uid,
			'did'		=>	$this -> userid,
			'usertype'	=>	$this -> usertype,
			'eid'		=>	(int)$_POST['eid'],
			'r_name'	=>	$_POST['r_name'],
			'username'	=>	$this -> username
		);
		$reportM    =  $this->MODEL('report');
	    $return   	=  $reportM -> ReportResume($data);
		$this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
	}
	function xls_action(){
		if($_POST['delid']){
			$downM  =  $this -> MODEL('downresume');
	        $list   =  $downM  ->  Xls($_POST['delid'],array('uid'=>$this->uid,'usertype'=>$this->usertype));
			if($list){
				$this -> yunset("list",$list);
				header("Content-Type: application/vnd.ms-excel");
				header("Content-Disposition: attachment; filename=已下载的简历.xls");
				$this -> com_tpl('resume_xls');
			}
		}
	}
	function remark_action(){
		$downM		=	$this->MODEL('downresume');
	    $data		=	array(
			'remark'	=>	$_POST['remark'],
			'remarkid'	=>	$_POST['remarkid'],
			'rname'		=>	$_POST['rname'],
			'status'	=>	$_POST['status'],
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype
		);
	    $return		=	$downM -> Remark($data);
		
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
	}
}
?>