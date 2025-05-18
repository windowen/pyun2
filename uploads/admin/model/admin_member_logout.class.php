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
class admin_member_logout_controller extends adminCommon{	 
	function index_action()
	{
	    if ($_GET['status']){
	        $where['status']  =  intval($_GET['status']);
	    }
		if(trim($_GET['keyword'])){
			if($_GET['type']==1){
				
				$where['username']		=	array('like',trim($_GET['keyword']));
				
			}elseif($_GET['type']==2){
				
				$where['moblie']		=	array('like',trim($_GET['keyword']));
				
			}elseif($_GET['type']==3){
				
				$where['uid']			=	array('like',trim($_GET['keyword']));
				
			}
			$urlarr['keyword']			=	$_GET['keyword'];
			
			$urlarr['type']				=	$_GET['type'];
		}
		$where['orderby']  =  array('id,desc');
		
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM			=	$this  -> MODEL('page');
		
		$pages			=	$pageM -> pageList('member_logout',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			$where['limit']	=  	$pages['limit'];
			
			$logoutM  =  $this->MODEL('logout');
			$List     =  $logoutM -> getList($where);
			
			$this -> yunset('rows',$List);
		}
		$statusArr		=  array('1'=>'未处理','2'=>'已处理');
		$search_list[]  =  array('param'=>'status','name'=>'处理状态','value'=>$statusArr);
		$this->yunset('search_list',$search_list);
		
		$this->yuntpl(array('admin/admin_member_logout'));
	}
	/**
	 * 注销账号申请审核
	 */
	function status_action()
	{
	    $id  =  intval($_POST['id']);
	    
	    $logoutM  =  $this->MODEL('logout');
	    $return   =  $logoutM->status(array('id'=>$id));
	    
	    $this->layer_msg($return['msg'],$return['errcode']);
	}
	/**
	 * 注销账号申请删除
	 */
	function del_action(){
	    
	    $whereData	=	array();
	    if(isset($_GET['id'])){
	        $this->check_token();
	        $delid  =  intval($_GET['id']);
	    }
	    if($_POST['del']){
	        
	        $delid  =  $_POST['del'];
	    }
	    $logoutM  =  $this->MODEL('logout');
	    $return	  =	 $logoutM->del($delid);
	    
	    $this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
}
?>