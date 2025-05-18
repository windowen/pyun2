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
class show_controller extends company{
	function index_action(){
		$companyM		=	$this->MODEL('company');
		
		$where['uid']	=	$this->uid;
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	"show";
		$pageurl		=	Url('member',$urlarr);
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_show',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
		    $where['limit']			=	$pages['limit'];
			$where['orderby']		=	'sort,desc';
			
		    $List					=	$companyM->getCompanyShowList($where,array('field'=>'`title`,`id`,`picurl`'));
			$this->yunset("rows",$List);
		}
		$sessionid	=	session_id();
		$this->yunset("sessionid",$sessionid);
		$this->public_action();
		$this->company_satic();
		$this->com_tpl('show');
	}
	function del_action(){
		$companyM	=	$this->MODEL('company');
		$logM		=	$this->MODEL('log');
		
		if($_GET['id']){
			
			$oid	=	$companyM->delCompanyShow($_GET['id'],array('uid'=>$this->uid));
			
			if($oid){
				
				$logM->member_log("删除企业环境展示",16,3);//会员日志
				
				$this->layer_msg('删除成功！',9,0,"index.php?c=show");
				
			}else{
				
				$this->layer_msg('删除失败！',8,0,"index.php?c=show");
				
			}
		}
	}
	
	function showpic_action(){
		$companyM	=	$this->MODEL('company');
		
		if($_GET['id']){
			
			$this->public_action();
			
			$picurl	=	$companyM->getCompanyShowInfo((int)$_GET['id'],array('uid'=>$this->uid,'field'=>'`picurl`,`title`,`sort`','type'=>'showpic'));
			$this->yunset("picurl",$picurl);
			$this->yunset("uid",$this->uid);
			$this->yunset("id",$_GET['id']);
			$this->com_tpl('editshow');
		}
	}
	
	function addshow_action(){
		$this->public_action();
		$this->yunset("uid",$this->uid);
		$this->com_tpl('addshow');
	}
	
	function upshow_action(){
		
		$companyM			=	$this->MODEL('company');

		$data['file']		=	$_FILES['file'];
		$data['uid']		=	$this->uid;
		$data['title']		=	$_POST['title'];
		$data['id']			=	(int)$_POST['id'];
		$data['sort']		=	$_POST['showsort'];
		$data['r_status']	=	$this -> comInfo['info']['r_status'];
		$return				=	$companyM->setShow($data);
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
	    
	}
	
	function save_action(){
		$companyM 			= 	$this->MODEL('company');
		
		$data['file']		=	$_FILES['file'];
			
		$data['uid']		=	$this->uid;
		
		$data['r_status']	=	$this -> comInfo['info']['r_status'];
		
		$return				=	$companyM->setShow($data);
		
		$arr				=	array('jsonrpc'=>'2.0', 'id'=>$return['id'] );
		
		echo json_encode($arr);die;
		
	}
}
?>