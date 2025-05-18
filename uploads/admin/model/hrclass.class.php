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
class hrclass_controller extends adminCommon{
	function index_action(){
		
		$HrM  =  $this -> MODEL('hr');
		
		$rows   =  $HrM     ->   getClassList($where);
            
		$this->yunset("rows",$rows);
		
		$this->yuntpl(array('admin/admin_hrclass'));
	
	}
	function add_action(){
		$HrM	=	$this->MODEL('hr');
		
		if($_GET['id']){
			
			$id		=	intval($_GET['id']);
			
			$row	=	$HrM   ->  getClassInfo(array("id"=>(int)$_GET['id']));
			
			$this -> yunset("row",  $row);	
			
		}
		if($_POST['submit']){
			
			
			
			
			
				if($_FILES['file']['tmp_name']){

				    $UploadM = $this->MODEL('upload');

	                $upArr    =  array(
	                  'file'  =>  $_FILES['file'],
	                  'dir'   =>  'hrclass'
	                );

	                $uploadM  =  $this->MODEL('upload');

	                $pic      =  $uploadM->newUpload($upArr);
	              
	                if (!empty($pic['msg'])){

	                  $this->ACT_layer_msg($pic['msg'],8);

	                }elseif (!empty($pic['picurl'])){

	                  $_POST['pic']   =   $pic['picurl'];

	                }
	            }
				
				
				if($_POST['id']){
					
					$nid	=	$HrM->upClassInfo(array('id'=>intval($_POST['id'])),$_POST);
					
					$msg	=	"更新";
				
				}else{
					
					$nid	=	$HrM -> addClassInfo($_POST);
					
					$msg	=	"添加";
				
				}
				
				$nid?$this->ACT_layer_msg( "HR类别(ID:".$_POST['id'].")".$msg."成功！",9,"index.php?m=hrclass",2,1):$this->ACT_layer_msg( "HR类别(ID:".$_POST['id'].")".$msg."失败！",8,"index.php?m=hrclass",2,1);
		
		}
		
		$this->yuntpl(array('admin/admin_hrclass_add'));
	
	}
	
	function del_action(){
		
		if($_GET['del']){
			
			$this -> check_token();
			
			$delID   =  intval($_GET['del']);
		
		}elseif($_POST['del']){
			 
			$delID   =  $_POST['del'];
		
		}
		
		$HrM	=	$this -> Model('hr');
		
		$return	=	$HrM -> delHrClass($delID);
		
		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		
	
	}
	
}

?>