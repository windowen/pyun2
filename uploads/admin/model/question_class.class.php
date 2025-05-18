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
class question_class_controller extends adminCommon{
	
	function index_action(){
		
		$AskM	=	$this -> MODEL('ask');
		
		if($_GET['pid']){
			
			$where['pid']	=	$_GET['pid'];
			
			$urlarr['pid']	=	$_GET['pid'];
		
		}else{
			
			$where['pid']	=	"0";
		
		}
		
		if(trim($_GET['keyword'])){
			
			$where['name']		=	array('like',trim($_GET['keyword']));
			
			$urlarr['keyword']	=	$_GET['keyword'];
		
		}
		
		$urlarr['page']		=	"{{page}}";
		
		$pageurl			=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM				=	$this  -> MODEL('page');
		
		$pages				=	$pageM -> pageList('q_class',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			if($_GET['order']){
				
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				
				$urlarr['order']		=	$_GET['order'];
				
				$urlarr['t']			=	$_GET['t'];
		   
 		    }else{   
				
				$where['orderby']		=	'id';        
			
			}    
			
			$where['limit']				=	$pages['limit'];         
			
			$q_class	=	$AskM -> getQclassList($where);           
		
		}
		
		$this -> yunset("q_class" , $q_class); 
		
		$this -> yunset("get_type", $_GET);
		
		$this -> yuntpl(array('admin/admin_q_class_list'));
	
	}
	
	function add_action(){
		
		$AskM	=	$this->Model('ask');
		
		if($_GET['pid']){
			
			$this -> yunset("pid",$_GET['pid']);
		
		}
		
		if($_GET['id']){
			
			$q_class	=	$AskM->getQclassInfo($_GET['id']);
			
			$this -> yunset("q_class",$q_class);
			
			$this -> yunset("pid",$q_class['pid']);
		
		}
		
		$all_q_class	=	$AskM->getQclassList(array('pid'=>0));
		
		$this -> yunset("class_list",$all_q_class);
		
		$this -> yuntpl(array('admin/admin_q_class_add'));
	
	}
	
	function save_action(){
		
		$AskM	=	$this->Model('ask');
		
		if($_GET['pid']){
			
			$url	=	"index.php?m=question_class&pid=".$_GET['pid'];
		
		}else{
			
			$url	=	"index.php?m=question_class";
		
		}
		
		$_POST['intro']		=	str_replace("&amp;","&",$_POST['intro']);
		
		
		if($_FILES['file']['tmp_name']){

			$upArr    =  array(
				'file'  =>  $_FILES['file'],
				'dir'   =>  'question_class'
			);

			$uploadM  =  $this->MODEL('upload');

			$picr      =  $uploadM->newUpload($upArr);
			
			if (!empty($picr['msg'])){

				$this->ACT_layer_msg($picr['msg'],8);
				

			}elseif (!empty($picr['picurl'])){

				$pictures 	=  	$picr['picurl'];
			}

		}

		if(isset($pictures)){

			$_POST['pic']	=	$pictures;
			
		}

		if($_POST['id']){
			
			$nbid	=	$AskM -> upQclassInfo(array('id'=>intval($_POST['id'])),$_POST);
			
			$this -> cache_action();
			
			$msg	=	"更新";
		
		}else{
			
			$_POST['add_time']	=	time();
			
			$nbid	=	$AskM -> addQclassInfo($_POST);
			
			$this -> cache_action();
			
			$msg="添加";
		
		}
		
		isset($nbid)?$this->ACT_layer_msg("问答类别(ID:".$_POST['id'].")".$msg."成功！",9,$url,2,1):$this->ACT_layer_msg($msg."失败！",8,$url);
	
	}
	
	function del_action(){
		
		$AskM	=	$this->Model('ask');
		
		$this -> check_token();
		
		$delID	=	$_GET['id'] ? intval($_GET['id']) : $_GET['del'];
		
		$addArr	=	$AskM -> delQclass($delID);
		
		$this -> del_question($del);
		
		$this -> cache_action();
		
		$this -> layer_msg( $addArr['msg'],$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	
	}
	
	function del_question($cid){
		
		$AskM	=	$this -> Model('ask');
		
		$qid	=	$AskM -> getList(array('id'=>$cid));
		
		foreach($qid as $q_v){
			
			$qids[]	=	$q_v['id'];
		
		}
		
		$qids	=	pylode(',',$qids);
		
		$AskM -> delquestiongroups($qids);
	
	}
	
	function cache_action(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->ask_cache("ask.cache.php");
	}
}
?>