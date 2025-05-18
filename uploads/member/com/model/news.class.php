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
class news_controller extends company
{
	function index_action(){
		$companyM	=	$this->MODEL('company');
		$this->public_action();
		$this->company_satic();
		
		$where['uid']	=	$this->uid;
		
		if(trim($_GET['keyword'])){
			$urlarr['keyword']	=	trim($_GET['keyword']);
			$where['title']		=	array('like',trim($_GET['keyword']));
		}
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$pageurl		=	Url('member',$urlarr);
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_news',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
		    $where['limit']			=	$pages['limit'];
			
		    $List					=	$companyM->getCompanyNewsList($where,array('field'=>'`title`,`id`,`status`,`ctime`,`statusbody`'));
			$this->yunset("rows",$List);
		}
		$this->com_tpl("news");
	}
	
	function add_action(){
		$this->public_action();
		$this->com_tpl("addnews");
	}
	
	function edit_action(){
		$companyM			=	$this->MODEL('company');
		
		$this->public_action();
		$editrow			=	$companyM->getCompanyNewsInfo(array('uid'=>$this->uid,'id'=>(int)$_GET['id']));
		
		$editrow['body']	=	str_replace(array("ti<x>tle","“","”"),array("title"," "," "),$editrow['body']);
		
		$this->yunset("editrow",$editrow);
		$this->com_tpl("addnews");
	}
	
	function save_action(){
		$companyM			=	$this->MODEL('company');
		
		if($_POST['action']=="save"){
			
			$sql['title']	=	$_POST['title'];
			$sql['body']	=	$_POST['body'];
			
			if(trim($sql['title'])=="" || $sql['body']==""){
 				$this->ACT_layer_msg("新闻标题内容不能为空！",2,$_SERVER['HTTP_REFERER']);
			}
			if(!$_POST['id']){
				$sql['uid']		=	$this->uid;
				$sql['did']		=	$this->userdid;
				$sql['ctime']	=	time();
				$sql['usertype']=	$this->usertype;
				$where			=	array();
			}else{
				$where['id']	=	(int)$_POST['id'];
				$where['uid']	=	$this->uid;
				$sql['status']	=	'0';
				$sql['uid']		=	$this->uid;
				$sql['usertype']=	$this->usertype;
				$sql['ctime']	=	time();
				$sql['did']		=	$this->userdid;
			}
			$return		=	$companyM->setCompanyNews($where,$sql);
			
			$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?c=news");
		}
	}
	
	function del_action(){
		
		$companyM	=	$this->MODEL('company');
		
		$delid		=	$_POST['delid']?$_POST['delid']:$_GET['id'];
		
		$return		=	$companyM->delCompanyNews($delid , array('uid' => $this->uid,'usertype' => $this->usertype, 'utype' => 'user'));
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		
	}
	
	function show_action(){
		
		$companyM	=	$this->MODEL('company');
		
		$row		=	$companyM->getCompanyNewsInfo(array('uid'=>$this->uid,'id'=>$_POST['id']),array('field'=>'`statusbody`'));
		
		echo json_encode($row);die;
	}
}
?>