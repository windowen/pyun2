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
class product_controller extends company{
	function index_action(){  
		$companyM		=	$this->MODEL('company');
		
		$where['uid']	=	$this->uid;
		
		if(trim($_GET['keyword'])){
			
			$where['title']		=	array('like',trim($_GET['keyword']));
			
			$urlarr['keyword']	=	trim($_GET['keyword']);
		}
		
		$data['field']	=	'`title`,`id`,`status`,`ctime`,`statusbody`';
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	"product";
		$pageurl		=	Url('member',$urlarr);
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_product',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
		    $where['limit']			=	$pages['limit'];
			
		    $List					=	$companyM->getCompanyProductList($where,$data);
			$this->yunset("rows",$List);
		}
		
		$this->public_action();
		$this->company_satic();
		$this->com_tpl("product");
	}
	function add_action(){
		$this->public_action();
		$this->com_tpl("addproduct");
	}
	
	function save_action(){
		
		$companyM		=	$this->MODEL('company');
		
		$sql['title']	=	$_POST['title'];
		$sql['body']	=	$_POST['body'];
		$sql['file']	=	$_FILES['file'];
		
		
		if(!$_POST['id']){
			$sql['uid']		=	$this->uid;
			$sql['did']		=	$this->userdid;
			$sql['ctime']	=	time();
			$where			=	array();
		}else{
			$where['id']	=	(int)$_POST['id'];
			$where['uid']		=	$this->uid;
			$sql['status']	=	'0';
			$sql['uid']	=	$this->uid;
		}
		$return		=	$companyM->setCompanyProduct($where,$sql,$this->usertype);
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$return['url']);
		
	}
	
	function edit_action(){ 
		$companyM	=	$this->MODEL('company');
		$this->public_action();
		$editrow	=	$companyM->getComProductInfo(array('uid'=>$this->uid,'id'=>(int)$_GET['id']));
		
		$editrow['body']	=	str_replace(array("ti<x>tle","“","”"),array("title"," "," "),$editrow['body']);
		
		$this->yunset("editrow",$editrow);
		$this->com_tpl("addproduct");
	}
	
	function del_action(){
		$companyM	=	$this->MODEL('company');
		
		$delid		=	$_GET['delid']?$_GET['delid']:$_GET['id'];
		
		$return		=	$companyM->delCompanyProduct($delid,array('utype' => 'user','uid'=>$this->uid));
   
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		
	}
	
	function show_action(){
		$companyM	=	$this->MODEL('company');
		
		if($_POST['id']){
			$row	=	$companyM->getComProductInfo(array('uid'=>$this->uid,'id'=>(int)$_POST['id']),array('field'=>'`statusbody`'));
			
			$data['statusbody']		=	$row['statusbody'];
			
			echo json_encode($data);die;
		}
	}
}
?>