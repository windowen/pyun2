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
class admin_industry_controller extends adminCommon{
	function index_action(){
		$categoryM				=	$this 		->	MODEL('category');
		$whereData['orderby']	=	'sort,desc';
		$list					=	$categoryM	->	getIndustryClassList($whereData);

		$this->yunset("list",$list);
		$this->yuntpl(array('admin/admin_industry'));
	}
	//添加
	function add_action(){
		$_POST			=	$this	->	post_trim($_POST);
		$data['name']	=	@explode('-',$_POST['name']);
		$categoryM		=	$this		->	MODEL('category');
		$return			=	$categoryM	->	addIndustryClass($data);
	    
	    
		echo $return;die;
	}
	
	//删除
	function del_action()
	{
		$whereData				=	array();
		$data					=	array();
		$categoryM				=	$this	->	MODEL('category');
		if($_GET['delid']){//单个删除
			$this				->	check_token();
			$whereData['id']	=	$_GET['delid'];
			$data['type']		=	'one';	
		}
		if($_POST['del']){//批量删除
			$whereData['id']	=	array('in',pylode(',',$_POST['del']));
			$data['type']		=	'all';	
		}
		$return	=	$categoryM	->	delIndustryClass($whereData,$data);
		$this   ->  layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1); 
	}
	
	function ajax_action(){
		$categoryM			=	$this	->	MODEL('category');
		$whereData['id']	=	$_POST['id'];
		$addData['sort']	=	$_POST['sort'];
		$addData['name']	=	$_POST['name'];
		
		$categoryM	->	upIndustryClass($addData,$whereData);
		echo '1';
		die;
	}
}
?>