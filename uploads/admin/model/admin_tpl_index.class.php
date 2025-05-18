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
class admin_tpl_index_controller extends adminCommon{
	function index_action(){
		$where['orderby']	=	'id,desc';
		
		$list	=	$this -> MODEL('tpl') -> getTplindexList($where);
		
		$this->yunset("list",$list);
		
		$this->yuntpl(array('admin/admin_tpl_index'));
	}
	function add_action(){
		if($_GET['id']){
			
			$list	=	$this -> MODEL('tpl') -> getTplindex(array('id'=>$_GET['id']));
			
			$timearr[]		=	date("Y-m-d",$list['stime']);
			
			$timearr[]		=	date("Y-m-d",$list['etime']);
			
			$time			=	implode(" ~ ",$timearr);
			
			$list['time']	=	$time;
			
			$this->yunset("row",$list);
		}
		$this->yuntpl(array('admin/admin_tpl_indexadd'));
	}
	function save_action(){
		$tplM	=	$this -> MODEL('tpl');
		
	    if($_FILES['file']['tmp_name']!=''){
			// pc端上传
			$upArr    =  array(
				'file'  =>  $_FILES['file'],
				'dir'   =>  'tplindex'
			);

			$uploadM  =  $this->MODEL('upload');
			$pic      =  $uploadM->newUpload($upArr);

			if (!empty($pic['msg'])){

			$return['msg']      =  $pic['msg'];
			$return['errcode']  =  8;
			return $return;

			}elseif (!empty($pic['picurl'])){

				$picurl         =   $pic['picurl'];
			}
        }
        if(isset($picurl)){
        	$_POST['pic']		=	$picurl;
        }
		$time			=	explode("~",$_POST['time']);
		
		$_POST['stime']	=	strtotime(trim($time[0]));
		
		$_POST['etime']	=	strtotime(trim($time[1]));
		
		unset($_POST['msgconfig']);
		
		unset($_POST['time']);
		
		if($_POST['id']){
			
			$return		=	$tplM -> upTplindex($_POST,array("id"=>$_POST['id']));
		}else{
			
			$return		=	$tplM -> addTplindex($_POST);
		}
		$this->cache();
		$return['id']?$this->ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_tpl_index",2,1):$this->ACT_layer_msg($return['msg'],$return['errcode']);
	}
	function del_action(){
		$this->check_token();
		
		$tplM	=	$this -> MODEL('tpl');
		
		$del=$_GET['id'];
		
		if($del){
			
			$return	=	$tplM -> delTplindex($del);
			
			$this->cache();
			
			$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		}else{
			$this->cache();
			
			$this->layer_msg('请先选择！',8,0,$_SERVER['HTTP_REFERER']);
		}
		
	}
	/*生成主题的缓存文件*/
	function cache(){
		include_once(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->indextpl_cache("indextpl.cache.php");
	}
}