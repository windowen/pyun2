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
class admin_reason_controller extends adminCommon{
	function index_action(){
		$categoryM			=	$this -> MODEL('category');
		$reason				=	$categoryM -> getReasonClassList();
		$this->yunset('reason',$reason);
		$this->yuntpl(array('admin/admin_reason_list'));
	}
	

	function save_action(){
		$this	->	check_token();
		$whereData	=	array();
		if($_GET['id']){
			$whereData['id']	=	$_GET['id'];
			$addData['name']	=	trim($_GET['name']);
		}else{
			$addData['name']	=	trim($_GET['name']);
		}
		$categoryM	=	$this -> MODEL('category');
		$return		=	$categoryM	->	addReasonClass($addData,$whereData);
		$this	->	ACT_layer_msg($return['msg'],$return['cod'],$_SERVER['HTTP_REFERER'],2,1);

	}
	function ajax_action(){
		if($_POST['id']){
			$categoryM	=	$this -> MODEL('category');
			$categoryM	->	addReasonClass(array('name'=>trim($_POST['name'])),array('id'=>$_POST['id']));
			echo '1';
			die;
		}
	}
	function del_action(){
		$this	->	check_token();
		$whereData	=	array();
		if($_GET['del']){
			$delid				=	pylode(',',$_GET['del']);
    		$whereData['id']	=	array('in',$delid);
			$data['type']		=	'all';
		}
	    if(isset($_GET['id'])){
	    	$whereData['id']	=	$_GET['id'];
			$data['type']		=	'one';
		}
		$categoryM	=	$this -> MODEL('category');
		$return		=	$categoryM	->	delReasonClass($whereData,$data);
		$this	->	layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
    
}
?>