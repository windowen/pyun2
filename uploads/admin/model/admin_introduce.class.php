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
class admin_introduce_controller extends adminCommon{
	function index_action(){
		$categoryM				=	$this -> MODEL('category');
		$whereData['orderby'] 	=	'sort,asc';
		$List				=	$categoryM -> getIntroduceClassList($whereData);
		$this	->	yunset("list",$List);
		$this	->	yuntpl(array('admin/admin_introduce'));
	}
	
	function classadd_action(){
	    
		$categoryM				=	$this -> MODEL('category');
		
		if($_GET['id']){
			$info		=	$categoryM -> getIntroduceClass(array('id'=>$_GET['id']));
			
			$this	->	yunset("info",$info);
		}
		$this	->	yuntpl(array('admin/admin_introduce_classadd'));
	}
	
	function save_action(){
		$_POST	=	$this -> post_trim($_POST);
		if($_POST['submit']){
			$data	=	array(
				'name'		=>	$_POST['position'],
				'sort'		=>	$_POST['sort'],
				'content'	=>	strip_tags($_POST['content'])
			);
			
			$categoryM		=	$this -> MODEL('category');
			
			if($_POST['id']){
				
				$return		=	$categoryM -> addIntroduceClass($data,array('id'=>$_POST['id']));	
				
			}else{
				
				$return		=	$categoryM -> addIntroduceClass($data);	
			}
			
			$this	->	ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_introduce",2,$return['type']);

		}
	}
	
	
	//删除
	function del_action(){
		$categoryM				=	$this	->	MODEL('category');
		if((int)$_GET['delid']){
			$this	->	check_token();
			$whereData['id']	=	$_GET['delid'];
			$data['type']		=	'one';	
		}else if($_POST['del']){ //批量删除
			$whereData['id']	=	array('in',pylode(',',$_POST['del']));
			$data['type']		=	'all';	
		}

		$return	=	$categoryM	->	delIntroduceClass($whereData,$data);
		
		$this   ->  layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	}
	
	function ajax_action(){
		$categoryM			=	$this	->	MODEL('category');
		$whereData['id']	=	$_POST['id'];
		if($_POST['name']){
      $addData['name']	=	$_POST['name'];
    }else{
      $addData['sort']	=	$_POST['sort'];
    }		
		
		$categoryM	->	addIntroduceClass($addData,$whereData);
		echo '1';
		die;
	}
	
}

?>