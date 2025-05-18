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
class zph_space_controller extends adminCommon{
	
	function index_action(){
		
		$ZphM	=	$this -> MODEL('zph');
		
		$position	=	$ZphM -> getZphSpaceList(array('keyid'=>'0','orderby'=>'sort,asc'));
		
		$this	->	yunset("position",$position);
		
		$this	->	yuntpl(array('admin/zph_space'));
	
	}
	
	function classadd_action(){
		
		$ZphM	=	$this -> MODEL('zph');
		
		if($_GET['id']){
			
			$info	=	$ZphM -> getZphSpaceInfo(array('id'=>intval($_GET['id'])),array('pic'=>1));
			
			$this -> yunset("info",$info);
		
		}else{
			
			$position	=	$ZphM->getZphSpaceList(array('keyid'=>'0','orderby'=>'sort,asc'));
			
			$this -> yunset("position",$position);
		
		}
		
		$this -> yuntpl(array('admin/zph_space_classadd'));
	
	}
	
	function save_action(){
		
		$ZphM	=	$this->MODEL('zph');
		
		if($_POST['keyid']!=''){
			
			$data['keyid']	=	$_POST['keyid'];
			
			$data['price']	=	$_POST['price'];
		
		}elseif($_POST['nid']!=''){
			
			$data['keyid']	=	$_POST['nid'];
		
		}
		
		$data['name']		=	$_POST['id'] ? $_POST['position'] : explode('，', $_POST['position']);
		
		$data['sort']		=	$_POST['sort'];
		
		$data['content']	=	str_replace("&amp;","&",$_POST['content']);
		
		if($_FILES['file']['tmp_name']!=''){
			// pc端上传
			$upArr    =  array(
				'file'  =>  $_FILES['file'],
				'dir'   =>  'zhaopinhui'
			);

			$uploadM  =  $this->MODEL('upload');
			$pic      =  $uploadM->newUpload($upArr);

			if (!empty($pic['msg'])){

				$this -> ACT_layer_msg($pic['msg'],8);

			}elseif (!empty($pic['picurl'])){

				$data['pic']         =   $pic['picurl'];
			}
		}
		
		
		if($_POST['id']){
			
			$nid	=	$ZphM -> upZphSpaceInfo(array('id'=>$_POST['id']),$data);
			
			$msg	=	"更新";
		
		}else{
			
			$nid	=	$ZphM->addZphSpaceInfo($data);
			
			$msg	=	"添加";
		
		}
		
		$nid?$this->ACT_layer_msg($msg."成功！",9,"index.php?m=zph_space",2,1):$this->ACT_layer_msg($msg."失败！",8,"index.php?m=zph_space");
	
	}
	
	function ajaxspace_action(){
		
		$ZphM	=	$this->MODEL('zph');
		
		$id		=	intval($_GET['id']);
		
		if($id!=""){
			
			$jobs	=	$ZphM->getZphSpaceList(array('keyid'=>$id));
			
			if(is_array($jobs)){
				
				$html .= "";
                
				$html 	=	 "<option value=''>请选择</option>";
				
				foreach($jobs as $key=>$v){
					
					$html .= '<option value='.$v['id'].'>'.$v['name'].'</option>';
				
				}				
				
				echo $html;die;
			
			}
		
		}
	
	}
	
	function up_action(){
		// 查询子类别
		
		$ZphM=$this->MODEL('zph');
		
		if((int)$_GET['id']){
			
			$id		=	(int)$_GET['id'];
			
			$onejob	=	$ZphM->getZphSpaceInfo(array('id'=>$_GET['id']));
			
			$twojob	=	$ZphM->getZphSpaceList(array('keyid'=>$_GET['id'],'orderby'=>'sort,asc'));
			
			if(is_array($twojob)){
				
				foreach($twojob as $key => $v){
					
					$val[]		=	$v['id'];
					
					$root_arr 	= 	@implode(",",$val);
				
				}
			
			}
		
			$jobs	=	$ZphM -> getZphSpaceList(array('keyid'=>$_GET['id'],'keyid'=>array('in',$root_arr,'or'),'orderby'=>'sort,asc'));
			
			$a=0;
			
			if(is_array($jobs)){
				
				foreach($jobs as $key => $v){
					
					if($v['keyid'] == $id){
						
						$twojob[$a]['id']	=	$v['id'];
						
						$twojob[$a]['sort']	=	$v['sort'];
						
						$twojob[$a]['name']	=	$v['name'];
						
						$a++;
					
					}else{
						
						$threejob[$v['keyid']][]	=	$v;
					
					}
				
				}
			
			}
			
			$this -> yunset("id",$id);
			
			$this -> yunset("onejob",$onejob);
			
			$this -> yunset("twojob",$twojob);
			
			$this -> yunset("threejob",$threejob);
		
		}
		
		$position	=	$ZphM->getZphSpaceList(array('keyid'=>'0'));
		
		$this -> yunset("position",$position);
		
		$this -> yuntpl(array('admin/zph_space'));
	
	}

	function del_action(){
		
		$ZphM	=	$this->MODEL('zph');
		
		if($_GET['delid']){
			
			$this -> check_token();
			
			$delID	=	intval($_GET['delid']);
		
		}else if($_POST['del']){
			
			$delID	=	$_POST['del'];
		
		}
		
		$return		=	$ZphM -> delZphSpace($delID);
		
		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	
	}
	
	function ajax_action(){
		
		$ZphM	=	$this->MODEL('zph');
		
		if($_POST['sort']){//修改招聘会场地排序
			
			$sValue['sort']		=	$_POST['sort'];
			
			$sWhere['id']		=	$_POST['id'];
			
			$ZphM -> upZphSpaceInfos($sWhere,$sValue);
			
			$this -> MODEL('log') -> addAdminLog("修改招聘会场地(ID:".$_POST['id'].")的排序");
		
		}
		
		if($_POST['name']){//修改招聘会场地名称
			
			$nValue['name']		=	$_POST['name'];
			
			$nWhere['id']		=	$_POST['id'];
			
			$ZphM -> upZphSpaceInfos($nWhere,$nValue);
			
			$this -> MODEL('log') -> addAdminLog("修改招聘会场地(ID:".$_POST['id'].")名称");
		
		}
		
		if($_POST['price']!=""){//修改招聘会场地名称
			
			$pValue['price']	=	$_POST['price'];
			
			$pWhere['id']		=	$_POST['id'];
			
			$ZphM -> upZphSpaceInfos($pWhere,$pValue);
			
			$this -> MODEL('log')->addAdminLog("修改招聘会场地(ID:".$_POST['id'].")名称");
		
		}
		
		echo '1';die;
	
	}
	
}

?>